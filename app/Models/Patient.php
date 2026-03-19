<?php

class Patient {
    private $conn;
    private $table = 'patient';

    public $patient_id;
    public $fullname;
    public $patient_type;
    public $contact_no;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create patient
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (fullname, patient_type, contact_no) 
                  VALUES (:fullname, :patient_type, :contact_no)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->patient_type = htmlspecialchars(strip_tags($this->patient_type));
        $this->contact_no = htmlspecialchars(strip_tags($this->contact_no));

        // Bind parameters
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':patient_type', $this->patient_type);
        $stmt->bindParam(':contact_no', $this->contact_no);

        if ($stmt->execute()) {
            $this->patient_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all patients
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00'
                  ORDER BY dateCreated DESC, patient_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single patient
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE patient_id = :patient_id 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->fullname = $row['fullname'];
            $this->patient_type = $row['patient_type'];
            $this->contact_no = $row['contact_no'];
            $this->dateCreated = $row['dateCreated'];
            return $row;
        }
        return false;
    }

    // Update patient
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET fullname = :fullname, 
                      patient_type = :patient_type, 
                      contact_no = :contact_no
                  WHERE patient_id = :patient_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->patient_type = htmlspecialchars(strip_tags($this->patient_type));
        $this->contact_no = htmlspecialchars(strip_tags($this->contact_no));
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));

        // Bind parameters
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':patient_type', $this->patient_type);
        $stmt->bindParam(':contact_no', $this->contact_no);
        $stmt->bindParam(':patient_id', $this->patient_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete patient
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET dateDeleted = CURRENT_TIMESTAMP 
                  WHERE patient_id = :patient_id";
        $stmt = $this->conn->prepare($query);

        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $stmt->bindParam(':patient_id', $this->patient_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if patient exists by name and contact
    public function findByNameAndContact($fullname, $contact_no) {
        $query = "SELECT patient_id FROM " . $this->table . " 
                  WHERE fullname = :fullname 
                  AND contact_no = :contact_no 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['patient_id'] : false;
    }
}

?>