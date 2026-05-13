<?php

require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/includes/log_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Auth guard — all actions require admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Admin authentication required']);
    exit();
}

try {
    $database = new Database();
    $db       = $database->connect();
    $lab      = new LabResult($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── Allowed MIME types & extensions ──────────────────────────
$allowed = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/gif'       => 'gif',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf',
];

$uploadDir = __DIR__ . '/../public/uploads/lab_results/';

switch ($action) {

    // ── Read all (with optional filters) ─────────────────────
    case 'read':
        $patient_id = $_GET['patient_id'] ?? null;
        $lab_type   = $_GET['lab_type']   ?? null;
        $rows = $lab->readAll($patient_id, $lab_type);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── Search patients by name ───────────────────────────────
    case 'search_patients':
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            echo json_encode(['success' => true, 'data' => []]);
            break;
        }
        $rows = $lab->searchPatients($q);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── Upload ────────────────────────────────────────────────
    case 'upload':
        if ($method !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); break; }

        $patient_id = $_POST['patient_id'] ?? '';
        $lab_type   = trim($_POST['lab_type']   ?? '');
        $notes      = trim($_POST['notes']       ?? '');

        if (empty($patient_id) || empty($lab_type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Patient and lab type are required']);
            break;
        }

        if (!isset($_FILES['lab_file']) || $_FILES['lab_file']['error'] !== UPLOAD_ERR_OK) {
            $errMap = [1=>'File too large (server limit)',2=>'File too large (form limit)',3=>'Partially uploaded',4=>'No file selected',6=>'No temp folder',7=>'Write failed'];
            $errCode = $_FILES['lab_file']['error'] ?? 4;
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $errMap[$errCode] ?? 'Upload error']);
            break;
        }

        $file      = $_FILES['lab_file'];
        $origName  = basename($file['name']);
        $tmpPath   = $file['tmp_name'];
        $fileSize  = $file['size'];

        // Validate size (max 10 MB)
        if ($fileSize > 10 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File size exceeds 10 MB limit']);
            break;
        }

        // Validate MIME type using finfo (server-side, not just extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath);

        if (!array_key_exists($mimeType, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File type not allowed. Use JPG, PNG, PDF, GIF, or WEBP.']);
            break;
        }

        // Create upload dir if missing
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $ext        = $allowed[$mimeType];
        $storedName = uniqid('lab_', true) . '.' . $ext;
        $destPath   = $uploadDir . $storedName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
            break;
        }

        // Save to DB
        $lab->patient_id  = (int) $patient_id;
        $lab->lab_type    = htmlspecialchars($lab_type);
        $lab->file_name   = htmlspecialchars($origName);
        $lab->file_path   = $storedName;
        $lab->file_size   = $fileSize;
        $lab->file_mime   = $mimeType;
        $lab->notes       = htmlspecialchars($notes);
        $lab->uploaded_by = $_SESSION['username'] ?? 'admin';

        if ($lab->create()) {
            writeLog($db, 'UPLOAD_LAB', 'Lab Results', "Uploaded '{$lab_type}' for patient ID {$patient_id} — file: {$origName}");
            echo json_encode(['success' => true, 'message' => 'Lab result uploaded successfully', 'lab_id' => $lab->lab_id]);
        } else {
            // Remove orphaned file
            @unlink($destPath);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save record to database']);
        }
        break;

    // ── Delete ────────────────────────────────────────────────
    case 'delete':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id   = $data['lab_id'] ?? $_GET['id'] ?? null;

        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'lab_id required']); break; }

        $lab->lab_id = (int) $id;
        $row = $lab->readOne();

        if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Record not found']); break; }

        if ($lab->delete()) {
            writeLog($db, 'DELETE_LAB', 'Lab Results', "Deleted lab result ID {$id} ('{$row['lab_type']}' for patient: {$row['patient_name']})");
            echo json_encode(['success' => true, 'message' => 'Lab result deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete record']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
