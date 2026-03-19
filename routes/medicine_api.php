<?php

require_once __DIR__ . '/../app/Config/Config.php';

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

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

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
            echo $medicineController->store($data);
        }
        break;
        
    case 'update':
        if ($method === 'POST' || $method === 'PUT') {
            echo $medicineController->update($data);
        }
        break;
        
    case 'delete':
        if ($method === 'POST' || $method === 'DELETE') {
            $id = $data['medicine_id'] ?? $_GET['id'] ?? null;
            echo $medicineController->destroy($id);
        }
        break;
        
    case 'update_stock':
        if ($method === 'POST') {
            echo $medicineController->updateStock($data);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

?>
