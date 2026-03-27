<?php

require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/includes/log_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read (active list for forms) is public. Write operations need admin.
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'read';

$writeActions = ['create', 'update', 'delete', 'toggle'];
if (in_array($action, $writeActions)) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Admin authentication required']);
        exit();
    }
}

try {
    $database = new Database();
    $db       = $database->connect();
    $symptom  = new Symptom($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

switch ($action) {

    // ── Public: active symptoms grouped by category (for forms) ──
    case 'read':
        $rows = $symptom->readAllActive();

        // Group by category for easy rendering
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['category']][] = $r;
        }

        echo json_encode([
            'success'  => true,
            'data'     => $rows,
            'grouped'  => $grouped,
        ]);
        break;

    // ── Admin: all symptoms including inactive ────────────────
    case 'read_all':
        if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Admin required']);
            exit();
        }
        echo json_encode([
            'success'    => true,
            'data'       => $symptom->readAll(),
            'categories' => $symptom->getCategories(),
        ]);
        break;

    // ── Create ────────────────────────────────────────────────
    case 'create':
        if (empty($data['symptom_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Symptom name is required']);
            break;
        }

        $symptom->symptom_name = trim($data['symptom_name']);
        $symptom->category     = trim($data['category'] ?? 'General');
        $symptom->is_active    = 1;

        if ($symptom->create()) {
            writeLog($db, 'ADD_SYMPTOM', 'Symptoms', "Added symptom '{$symptom->symptom_name}' (category: {$symptom->category})");
            echo json_encode(['success' => true, 'message' => 'Symptom added successfully', 'symptom_id' => $symptom->symptom_id]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create symptom (may already exist)']);
        }
        break;

    // ── Update ────────────────────────────────────────────────
    case 'update':
        if (empty($data['symptom_id']) || empty($data['symptom_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'symptom_id and symptom_name are required']);
            break;
        }

        $symptom->symptom_id   = $data['symptom_id'];
        $symptom->symptom_name = trim($data['symptom_name']);
        $symptom->category     = trim($data['category'] ?? 'General');
        $symptom->is_active    = isset($data['is_active']) ? (int) $data['is_active'] : 1;

        if ($symptom->update()) {
            writeLog($db, 'UPDATE_SYMPTOM', 'Symptoms', "Updated symptom ID {$symptom->symptom_id} to '{$symptom->symptom_name}'");
            echo json_encode(['success' => true, 'message' => 'Symptom updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update symptom']);
        }
        break;

    // ── Toggle active/inactive ────────────────────────────────
    case 'toggle':
        if (empty($data['symptom_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'symptom_id is required']);
            break;
        }

        $symptom->symptom_id = $data['symptom_id'];
        $row = $symptom->readOne();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Symptom not found']);
            break;
        }

        $symptom->symptom_name = $row['symptom_name'];
        $symptom->category     = $row['category'];
        $symptom->is_active    = $row['is_active'] ? 0 : 1;

        if ($symptom->update()) {
            $state = $symptom->is_active ? 'enabled' : 'disabled';
            writeLog($db, 'UPDATE_SYMPTOM', 'Symptoms', "Symptom '{$row['symptom_name']}' {$state}");
            echo json_encode(['success' => true, 'message' => "Symptom {$state}", 'is_active' => $symptom->is_active]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to toggle symptom']);
        }
        break;

    // ── Delete ────────────────────────────────────────────────
    case 'delete':
        $id = $data['symptom_id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'symptom_id is required']);
            break;
        }

        $symptom->symptom_id = $id;
        $row = $symptom->readOne();

        if ($symptom->delete()) {
            writeLog($db, 'DELETE_SYMPTOM', 'Symptoms', "Deleted symptom ID {$id}" . ($row ? " ('{$row['symptom_name']}')" : ''));
            echo json_encode(['success' => true, 'message' => 'Symptom deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete symptom']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
