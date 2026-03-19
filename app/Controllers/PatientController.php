<?php

class PatientController {
    private $db;
    private $patient;

    public function __construct($db) {
        $this->db = $db;
        $this->patient = new Patient($db);
    }

    /**
     * Get all patients
     */
    public function index() {
        try {
            $stmt = $this->patient->readAll();
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $patients
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch patients: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single patient
     */
    public function show($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Patient ID is required'
            ]);
            return;
        }

        try {
            $this->patient->patient_id = $id;
            $result = $this->patient->readOne();
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Patient not found'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create new patient
     */
    public function store($data) {
        // Validate
        if (empty($data['fullname']) || empty($data['patient_type']) || empty($data['contact_no'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Name, patient type, and contact number are required'
            ]);
            return;
        }

        try {
            $this->patient->fullname = $data['fullname'];
            $this->patient->patient_type = $data['patient_type'];
            $this->patient->contact_no = $data['contact_no'];
            
            if ($this->patient->create()) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Patient created successfully',
                    'patient_id' => $this->patient->patient_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create patient'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update patient
     */
    public function update($data) {
        if (empty($data['patient_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Patient ID is required'
            ]);
            return;
        }

        try {
            $this->patient->patient_id = $data['patient_id'];
            $this->patient->fullname = $data['fullname'];
            $this->patient->patient_type = $data['patient_type'];
            $this->patient->contact_no = $data['contact_no'];
            
            if ($this->patient->update()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Patient updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update patient'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete patient (soft delete)
     */
    public function destroy($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Patient ID is required'
            ]);
            return;
        }

        try {
            $this->patient->patient_id = $id;
            
            if ($this->patient->delete()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Patient deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to delete patient'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

?>
