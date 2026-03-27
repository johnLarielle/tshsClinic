<?php

require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/includes/log_helper.php';

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication for write operations
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Require auth for all operations except read (medicines are public reference data)
$protectedActions = ['create', 'update', 'delete', 'update_stock'];
if (in_array($action, $protectedActions)) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Admin authentication required'
        ]);
        exit();
    }
}

// Initialize database connection
try {
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get request data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

// Initialize controller (using MedicineController)
$medicineController = new MedicineController($db);

// Helper: run a controller call, echo the result, and log if successful
function runAndLog($callable, $db, $action, $module, $descriptionFn) {
    ob_start();
    $result = $callable();
    $output = ob_get_clean();

    echo $output;

    $decoded = json_decode($output, true);
    if (!empty($decoded['success'])) {
        writeLog($db, $action, $module, $descriptionFn($decoded));
    }
}

// Route requests to controller methods
switch ($action) {
    case 'read':
        if ($method === 'GET') {
            echo $medicineController->index();
        }
        break;

    case 'read_one':
        if ($method === 'GET') {
            $id = $_GET['id'] ?? null;
            echo $medicineController->show($id);
        }
        break;

    case 'create':
        if ($method === 'POST') {
            $medName = $data['medicine_name'] ?? 'Unknown';
            runAndLog(
                fn() => $medicineController->store($data),
                $db,
                'ADD_MEDICINE',
                'Medicine',
                fn($r) => "Added medicine '{$medName}'"
            );
        }
        break;

    case 'update':
        if ($method === 'POST' || $method === 'PUT') {
            $medId   = $data['medicine_id']   ?? '?';
            $medName = $data['medicine_name'] ?? 'Unknown';
            runAndLog(
                fn() => $medicineController->update($data),
                $db,
                'UPDATE_MEDICINE',
                'Medicine',
                fn($r) => "Updated medicine ID {$medId} — name: '{$medName}'"
            );
        }
        break;

    case 'delete':
        if ($method === 'POST' || $method === 'DELETE') {
            $id = $data['medicine_id'] ?? $_GET['id'] ?? null;
            runAndLog(
                fn() => $medicineController->destroy($id),
                $db,
                'DELETE_MEDICINE',
                'Medicine',
                fn($r) => "Deleted medicine ID {$id}"
            );
        }
        break;

    case 'update_stock':
        if ($method === 'POST') {
            $medId  = $data['medicine_id'] ?? '?';
            $type   = $data['type']        ?? 'adjust';
            $qty    = $data['quantity']    ?? 0;
            runAndLog(
                fn() => $medicineController->updateStock($data),
                $db,
                'UPDATE_STOCK',
                'Medicine',
                fn($r) => "Stock update on medicine ID {$medId}: {$type} {$qty} unit(s)"
            );
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

?>
