<?php
/**
 * Public Lab Upload API
 * No admin auth required — used by patients on the public form.
 */

require_once __DIR__ . '/../app/Config/Config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

try {
    $database = new Database();
    $db       = $database->connect();
    $lab      = new LabResult($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? '';

// ── Allowed MIME types ────────────────────────────────────────
$allowed = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/gif'       => 'gif',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf',
];

$uploadDir = __DIR__ . '/../public/uploads/lab_results/';

switch ($action) {

    // ── Public upload (find-or-create patient) ────────────────
    case 'upload':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            break;
        }

        $fullname    = trim($_POST['fullname']     ?? '');
        $patient_type= trim($_POST['patient_type'] ?? '');
        $contact_no  = trim($_POST['contact_no']   ?? '');
        $lab_type    = trim($_POST['lab_type']      ?? '');
        $notes       = trim($_POST['notes']         ?? '');

        if (empty($fullname) || empty($patient_type) || empty($contact_no) || empty($lab_type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Name, patient type, contact number, and lab type are required']);
            break;
        }

        // Find existing patient or create new one
        $patient    = new Patient($db);
        $patient_id = $patient->findByNameAndContact($fullname, $contact_no);

        if (!$patient_id) {
            $patient->fullname     = $fullname;
            $patient->patient_type = $patient_type;
            $patient->contact_no   = $contact_no;
            if (!$patient->create()) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Could not create patient record']);
                break;
            }
            $patient_id = $patient->patient_id;
        }

        if (!isset($_FILES['lab_file']) || $_FILES['lab_file']['error'] !== UPLOAD_ERR_OK) {
            $errMap = [1=>'File too large',2=>'File too large',3=>'Partially uploaded',4=>'No file selected',6=>'No temp folder',7=>'Write failed'];
            $errCode = $_FILES['lab_file']['error'] ?? 4;
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $errMap[$errCode] ?? 'Upload error']);
            break;
        }

        $file     = $_FILES['lab_file'];
        $origName = basename($file['name']);
        $tmpPath  = $file['tmp_name'];
        $fileSize = $file['size'];

        if ($fileSize > 10 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File size exceeds 10 MB limit']);
            break;
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath);

        if (!array_key_exists($mimeType, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File type not allowed. Use JPG, PNG, PDF, GIF, or WEBP.']);
            break;
        }

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext        = $allowed[$mimeType];
        $storedName = uniqid('lab_', true) . '.' . $ext;
        $destPath   = $uploadDir . $storedName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save file. Please try again.']);
            break;
        }

        $lab->patient_id  = (int)$patient_id;
        $lab->lab_type    = htmlspecialchars($lab_type);
        $lab->file_name   = htmlspecialchars($origName);
        $lab->file_path   = $storedName;
        $lab->file_size   = $fileSize;
        $lab->file_mime   = $mimeType;
        $lab->notes       = htmlspecialchars($notes);
        $lab->uploaded_by = 'patient';

        if ($lab->create()) {
            echo json_encode(['success' => true, 'message' => 'Lab result uploaded successfully!']);
        } else {
            @unlink($destPath);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save record. Please try again.']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
