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
    $patient = new Patient($db);
    $patientRecord = new PatientRecord($db);
    $medicine = new Medicine($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Route requests
switch ($action) {
    case 'read':
        if ($method === 'GET') {
            readPatientRecords($patientRecord);
        }
        break;
        
    case 'read_one':
        if ($method === 'GET') {
            readOnePatientRecord($patientRecord);
        }
        break;
        
    case 'create':
        if ($method === 'POST') {
            createPatientRecord($patient, $patientRecord, $medicine, $db);
        }
        break;
        
    case 'update':
        if ($method === 'POST' || $method === 'PUT') {
            updatePatientRecord($patient, $patientRecord, $medicine, $db);
        }
        break;
        
    case 'delete':
        if ($method === 'POST' || $method === 'DELETE') {
            deletePatientRecord($patientRecord, $medicine, $db);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// Read all patient records with patient info
function readPatientRecords($patientRecord) {
    try {
        $stmt = $patientRecord->readAll();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $records
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch patient records: ' . $e->getMessage()]);
    }
}

// Read single patient record
function readOnePatientRecord($patientRecord) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Record ID is required']);
        return;
    }
    
    try {
        $patientRecord->record_id = $_GET['id'];
        $result = $patientRecord->readOne();
        
        if ($result) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Patient record not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch record: ' . $e->getMessage()]);
    }
}

// Create patient record
function createPatientRecord($patient, $patientRecord, $medicine, $db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['name']) || empty($data['patient_type']) || empty($data['contact_no']) || 
        empty($data['medicine']) || empty($data['quantity']) || empty($data['reason']) || 
        empty($data['date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        return;
    }
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Check if patient exists
        $patient_id = $patient->findByNameAndContact($data['name'], $data['contact_no']);
        
        // If patient doesn't exist, create new patient
        if (!$patient_id) {
            $patient->fullname = $data['name'];
            $patient->patient_type = $data['patient_type'];
            $patient->contact_no = $data['contact_no'];
            
            if (!$patient->create()) {
                $db->rollBack();
                throw new Exception('Failed to create patient');
            }
            $patient_id = $patient->patient_id;
        }
        
        // Find or create medicine
        $medicine_id = $medicine->findOrCreateByName($data['medicine']);
        
        if (!$medicine_id) {
            $db->rollBack();
            throw new Exception('Failed to find or create medicine');
        }
        
        // Decrease medicine stock
        $medicine->medicine_id = $medicine_id;
        if (!$medicine->decreaseStock($data['quantity'])) {
            $db->rollBack();
            throw new Exception('Insufficient medicine stock or failed to update stock');
        }
        
        // Create patient record
        $patientRecord->patient_id = $patient_id;
        $patientRecord->medicine_id = $medicine_id;
        $patientRecord->quantity = $data['quantity'];
        $patientRecord->reason = $data['reason'];
        $patientRecord->date_given = $data['date'];
        
        if (!$patientRecord->create()) {
            $db->rollBack();
            throw new Exception('Failed to create patient record');
        }
        
        // Commit transaction
        $db->commit();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Patient record created successfully'
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Update patient record
function updatePatientRecord($patient, $patientRecord, $medicine, $db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Record ID is required']);
        return;
    }
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Get old record data for stock adjustment
        $patientRecord->record_id = $data['id'];
        $oldRecord = $patientRecord->readOne();
        
        if (!$oldRecord) {
            $db->rollBack();
            throw new Exception('Record not found');
        }
        
        // Update patient info if provided
        if (!empty($data['patient_id']) && !empty($data['name'])) {
            $patient->patient_id = $data['patient_id'];
            $patient->fullname = $data['name'];
            $patient->patient_type = $data['patient_type'];
            $patient->contact_no = $data['contact_no'];
            
            if (!$patient->update()) {
                $db->rollBack();
                throw new Exception('Failed to update patient');
            }
        }
        
        // Restore old medicine stock (add back old quantity)
        $medicine->medicine_id = $oldRecord['medicine_id'];
        if (!$medicine->increaseStock($oldRecord['quantity'])) {
            $db->rollBack();
            throw new Exception('Failed to restore old medicine stock');
        }
        
        // Find or create new medicine
        $medicine_id = $medicine->findOrCreateByName($data['medicine']);
        
        if (!$medicine_id) {
            $db->rollBack();
            throw new Exception('Failed to find or create medicine');
        }
        
        // Deduct new medicine stock
        $medicine->medicine_id = $medicine_id;
        if (!$medicine->decreaseStock($data['quantity'])) {
            $db->rollBack();
            throw new Exception('Insufficient medicine stock');
        }
        
        // Update patient record
        $patientRecord->record_id = $data['id'];
        $patientRecord->patient_id = $data['patient_id'];
        $patientRecord->medicine_id = $medicine_id;
        $patientRecord->quantity = $data['quantity'];
        $patientRecord->reason = $data['reason'];
        $patientRecord->date_given = $data['date'];
        
        if (!$patientRecord->update()) {
            $db->rollBack();
            throw new Exception('Failed to update patient record');
        }
        
        // Commit transaction
        $db->commit();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Patient record updated successfully'
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Delete patient record
function deletePatientRecord($patientRecord, $medicine, $db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['id']) && empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Record ID is required']);
        return;
    }
    
    $record_id = !empty($data['id']) ? $data['id'] : $_GET['id'];
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Get record data before deleting
        $patientRecord->record_id = $record_id;
        $record = $patientRecord->readOne();
        
        if (!$record) {
            $db->rollBack();
            throw new Exception('Record not found');
        }
        
        // Restore medicine stock (add back the quantity)
        $medicine->medicine_id = $record['medicine_id'];
        if (!$medicine->increaseStock($record['quantity'])) {
            $db->rollBack();
            throw new Exception('Failed to restore medicine stock');
        }
        
        // Delete the record (soft delete)
        if (!$patientRecord->delete()) {
            $db->rollBack();
            throw new Exception('Failed to delete patient record');
        }
        
        // Commit transaction
        $db->commit();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Patient record deleted successfully and stock restored'
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

?>