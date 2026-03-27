<?php

class MedicineController {
    private $db;
    private $medicine;

    public function __construct($db) {
        $this->db = $db;
        $this->medicine = new Medicine($db);
    }

    /**
     * Get all medicines
     */
    public function index() {
        try {
            $stmt = $this->medicine->readAll();
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $medicines
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch medicines: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single medicine
     */
    public function show($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Medicine ID is required'
            ]);
            return;
        }

        try {
            $this->medicine->medicine_id = $id;
            $result = $this->medicine->readOne();
            
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
                    'error' => 'Medicine not found'
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
     * Create new medicine
     */
    public function store($data) {
        if (empty($data['medicine_name'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Medicine name is required'
            ]);
            return;
        }

        try {
            $this->medicine->medicine_name     = $data['medicine_name'];
            $this->medicine->milligrams        = $data['milligrams']        ?? null;
            $this->medicine->description       = $data['description']       ?? '';
            $this->medicine->manufactured_date = $data['manufactured_date'] ?? null;
            $this->medicine->expiry_date       = $data['expiry_date']       ?? null;
            $this->medicine->current_stock     = $data['current_stock']     ?? 0;

            if ($this->medicine->create()) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Medicine created successfully',
                    'medicine_id' => $this->medicine->medicine_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create medicine'
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
     * Update medicine
     */
    public function update($data) {
        if (empty($data['medicine_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Medicine ID is required'
            ]);
            return;
        }

        try {
            $this->medicine->medicine_id       = $data['medicine_id'];
            $this->medicine->medicine_name     = $data['medicine_name'];
            $this->medicine->milligrams        = $data['milligrams']        ?? null;
            $this->medicine->description       = $data['description']       ?? '';
            $this->medicine->manufactured_date = $data['manufactured_date'] ?? null;
            $this->medicine->expiry_date       = $data['expiry_date']       ?? null;

            if ($this->medicine->update()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Medicine updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update medicine'
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
     * Delete medicine
     */
    public function destroy($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Medicine ID is required'
            ]);
            return;
        }

        try {
            $this->medicine->medicine_id = $id;
            
            if ($this->medicine->delete()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Medicine deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to delete medicine'
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
     * Update medicine stock
     */
    public function updateStock($data) {
        if (empty($data['medicine_id']) || empty($data['quantity'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Medicine ID and quantity are required'
            ]);
            return;
        }

        try {
            $this->medicine->medicine_id = $data['medicine_id'];
            $action = $data['action'] ?? 'add'; // 'add' or 'subtract'
            $quantity = $data['quantity'];
            
            if ($action === 'add') {
                $result = $this->medicine->increaseStock($quantity);
            } else {
                $result = $this->medicine->decreaseStock($quantity);
            }
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Stock updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update stock (insufficient stock or error)'
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
