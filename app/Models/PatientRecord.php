<?php

class PatientRecord {
    private $conn;
    private $table = 'patient_record';

    public $record_id;
    public $patient_id;
    public $medicine_id;
    public $date_given;
    public $quantity;
    public $reason;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create patient record
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (patient_id, medicine_id, quantity, reason, date_given) 
                  VALUES (:patient_id, :medicine_id, :quantity, :reason, :date_given)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->date_given = htmlspecialchars(strip_tags($this->date_given));

        // Bind parameters
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':date_given', $this->date_given);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all patient records with JOIN to get patient name and medicine name
    public function readAll() {
        $query = "SELECT 
                    pr.record_id,
                    pr.patient_id,
                    pr.medicine_id,
                    pr.quantity,
                    pr.reason,
                    pr.date_given,
                    pr.dateCreated,
                    p.fullname as patient_name,
                    p.patient_type,
                    p.contact_no,
                    m.medicine_name,
                    m.description as medicine_description
                  FROM " . $this->table . " pr
                  LEFT JOIN patient p ON pr.patient_id = p.patient_id
                  LEFT JOIN medicine m ON pr.medicine_id = m.medicine_id
                  WHERE pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00'
                  ORDER BY pr.date_given DESC, pr.record_id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single patient record
    public function readOne() {
        $query = "SELECT 
                    pr.record_id,
                    pr.patient_id,
                    pr.medicine_id,
                    pr.quantity,
                    pr.reason,
                    pr.date_given,
                    pr.dateCreated,
                    p.fullname as patient_name,
                    p.patient_type,
                    p.contact_no,
                    m.medicine_name,
                    m.description as medicine_description
                  FROM " . $this->table . " pr
                  LEFT JOIN patient p ON pr.patient_id = p.patient_id
                  LEFT JOIN medicine m ON pr.medicine_id = m.medicine_id
                  WHERE pr.record_id = :record_id 
                  AND (pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':record_id', $this->record_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->patient_id = $row['patient_id'];
            $this->medicine_id = $row['medicine_id'];
            $this->quantity = $row['quantity'];
            $this->reason = $row['reason'];
            $this->date_given = $row['date_given'];
            $this->dateCreated = $row['dateCreated'];
            return $row; // Return full row with joined data
        }
        return false;
    }

    // Update patient record
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET patient_id = :patient_id,
                      medicine_id = :medicine_id,
                      quantity = :quantity,
                      reason = :reason,
                      date_given = :date_given
                  WHERE record_id = :record_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->date_given = htmlspecialchars(strip_tags($this->date_given));
        $this->record_id = htmlspecialchars(strip_tags($this->record_id));

        // Bind parameters
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':date_given', $this->date_given);
        $stmt->bindParam(':record_id', $this->record_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete patient record (set dateDeleted to current timestamp)
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET dateDeleted = CURRENT_TIMESTAMP 
                  WHERE record_id = :record_id";
        
        $stmt = $this->conn->prepare($query);
        $this->record_id = htmlspecialchars(strip_tags($this->record_id));
        $stmt->bindParam(':record_id', $this->record_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Hard delete (permanent deletion) - use with caution
    public function hardDelete() {
        $query = "DELETE FROM " . $this->table . " WHERE record_id = :record_id";
        $stmt = $this->conn->prepare($query);
        
        $this->record_id = htmlspecialchars(strip_tags($this->record_id));
        $stmt->bindParam(':record_id', $this->record_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get records by patient ID
    public function getByPatientId($patient_id) {
        $query = "SELECT 
                    pr.*,
                    m.medicine_name,
                    m.description as medicine_description
                  FROM " . $this->table . " pr
                  LEFT JOIN medicine m ON pr.medicine_id = m.medicine_id
                  WHERE pr.patient_id = :patient_id
                  AND (pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')
                  ORDER BY pr.date_given DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        return $stmt;
    }

    // Get records by medicine ID
    public function getByMedicineId($medicine_id) {
        $query = "SELECT 
                    pr.*,
                    p.fullname as patient_name,
                    p.patient_type
                  FROM " . $this->table . " pr
                  LEFT JOIN patient p ON pr.patient_id = p.patient_id
                  WHERE pr.medicine_id = :medicine_id
                  AND (pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')
                  ORDER BY pr.date_given DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medicine_id', $medicine_id);
        $stmt->execute();
        return $stmt;
    }

    // Get records by date range
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT 
                    pr.*,
                    p.fullname as patient_name,
                    p.patient_type,
                    m.medicine_name
                  FROM " . $this->table . " pr
                  LEFT JOIN patient p ON pr.patient_id = p.patient_id
                  LEFT JOIN medicine m ON pr.medicine_id = m.medicine_id
                  WHERE DATE(pr.date_given) BETWEEN :start_date AND :end_date
                  AND (pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')
                  ORDER BY pr.date_given DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }
}

?>