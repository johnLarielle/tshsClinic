<?php

class PatientRecordController {
    private $db;
    private $patientRecord;
    private $patient;
    private $medicine;

    public function __construct($db) {
        $this->db = $db;
        $this->patientRecord = new PatientRecord($db);
        $this->patient = new Patient($db);
        $this->medicine = new Medicine($db);
    }

    /**
     * Get all patient records with joined data
     */
    public function index() {
        try {
            $stmt = $this->patientRecord->readAll();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $records
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch patient records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single patient record by ID
     */
    public function show($id) {
        if (empty($id)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Record ID is required'
            ], 400);
        }

        try {
            $this->patientRecord->record_id = $id;
            $result = $this->patientRecord->readOne();
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => $result
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Patient record not found'
                ], 404);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Error fetching record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new patient record
     */
    public function store($data) {
        // Validate required fields
        $requiredFields = ['name', 'patient_type', 'contact_no', 'medicine', 'quantity', 'reason', 'date'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Field '$field' is required"
                ], 400);
            }
        }

        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // 1. Find or create patient
            $patient_id = $this->patient->findByNameAndContact($data['name'], $data['contact_no']);
            
            if (!$patient_id) {
                $this->patient->fullname = $data['name'];
                $this->patient->patient_type = $data['patient_type'];
                $this->patient->contact_no = $data['contact_no'];
                
                if (!$this->patient->create()) {
                    throw new Exception('Failed to create patient');
                }
                $patient_id = $this->patient->patient_id;
            }
            
            // 2. Find or create medicine
            $medicine_id = $this->medicine->findOrCreateByName($data['medicine']);
            
            if (!$medicine_id) {
                throw new Exception('Failed to find or create medicine');
            }
            
            // 3. Create patient record
            $this->patientRecord->patient_id = $patient_id;
            $this->patientRecord->medicine_id = $medicine_id;
            $this->patientRecord->quantity = $data['quantity'];
            $this->patientRecord->reason = $data['reason'];
            $this->patientRecord->date_given = $data['date'];
            
            if (!$this->patientRecord->create()) {
                throw new Exception('Failed to create patient record');
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Patient record created successfully'
            ], 201);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing patient record
     */
    public function update($data) {
        if (empty($data['id'])) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Record ID is required'
            ], 400);
        }

        try {
            $this->db->beginTransaction();
            
            // Update patient info if provided
            if (!empty($data['patient_id']) && !empty($data['name'])) {
                $this->patient->patient_id = $data['patient_id'];
                $this->patient->fullname = $data['name'];
                $this->patient->patient_type = $data['patient_type'];
                $this->patient->contact_no = $data['contact_no'];
                
                if (!$this->patient->update()) {
                    throw new Exception('Failed to update patient');
                }
            }
            
            // Find or create medicine
            $medicine_id = $this->medicine->findOrCreateByName($data['medicine']);
            
            if (!$medicine_id) {
                throw new Exception('Failed to find or create medicine');
            }
            
            // Update patient record
            $this->patientRecord->record_id = $data['id'];
            $this->patientRecord->patient_id = $data['patient_id'];
            $this->patientRecord->medicine_id = $medicine_id;
            $this->patientRecord->quantity = $data['quantity'];
            $this->patientRecord->reason = $data['reason'];
            $this->patientRecord->date_given = $data['date'];
            
            if (!$this->patientRecord->update()) {
                throw new Exception('Failed to update patient record');
            }
            
            $this->db->commit();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Patient record updated successfully'
            ], 200);
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete patient record
     */
    public function destroy($id) {
        if (empty($id)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Record ID is required'
            ], 400);
        }

        try {
            $this->patientRecord->record_id = $id;
            
            if ($this->patientRecord->delete()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Patient record deleted successfully'
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to delete patient record'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get records by patient ID
     */
    public function getByPatient($patient_id) {
        try {
            $stmt = $this->patientRecord->getByPatientId($patient_id);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $records
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get records by date range
     */
    public function getByDateRange($start_date, $end_date) {
        try {
            $stmt = $this->patientRecord->getByDateRange($start_date, $end_date);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $records
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Send JSON response with proper headers
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

?>
