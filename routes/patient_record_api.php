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

// Initialize database connection
try {
    $database = new Database();
    $db = $database->connect();
    $controller = new PatientRecordController($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Get request body data for POST/PUT
$data = [];
if ($method === 'POST' || $method === 'PUT') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    
    if (!$data) {
        $data = $_POST;
    }
}

// Route requests
switch ($action) {
    case 'index':
    case 'list':
        // GET all records
        if ($method === 'GET') {
            echo $controller->index();
        }
        break;

    case 'show':
    case 'get':
        // GET single record by ID
        if ($method === 'GET' && $id) {
            echo $controller->show($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Record ID is required']);
        }
        break;

    case 'create':
    case 'store':
        // POST create new record
        if ($method === 'POST') {
            echo $controller->store($data);
        }
        break;

    case 'update':
        // PUT/POST update record
        if (($method === 'POST' || $method === 'PUT') && $id) {
            echo $controller->update($id, $data);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Record ID is required']);
        }
        break;

    case 'delete':
    case 'destroy':
        // DELETE record
        if ($method === 'POST' || $method === 'DELETE') {
            if ($id) {
                echo $controller->destroy($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Record ID is required']);
            }
        }
        break;

    case 'by_patient':
        // GET records by patient ID
        if ($method === 'GET' && isset($_GET['patient_id'])) {
            echo $controller->getByPatient($_GET['patient_id']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Patient ID is required']);
        }
        break;

    case 'by_medicine':
        // GET records by medicine ID
        if ($method === 'GET' && isset($_GET['medicine_id'])) {
            echo $controller->getByMedicine($_GET['medicine_id']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Medicine ID is required']);
        }
        break;

    case 'by_date_range':
        // GET records by date range
        if ($method === 'GET' && isset($_GET['start_date']) && isset($_GET['end_date'])) {
            echo $controller->getByDateRange($_GET['start_date'], $_GET['end_date']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Start date and end date are required']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action or endpoint not found']);
        break;
}

?>