<?php

class Medicine {
    private $conn;
    private $table = 'medicine';

    public $medicine_id;
    public $medicine_name;
    public $description;
    public $current_stock;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create medicine
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (medicine_name, description, current_stock) 
                  VALUES (:medicine_name, :description, :current_stock)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->current_stock = htmlspecialchars(strip_tags($this->current_stock));

        // Bind parameters
        $stmt->bindParam(':medicine_name', $this->medicine_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':current_stock', $this->current_stock);

        if ($stmt->execute()) {
            $this->medicine_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all medicines
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00'
                  ORDER BY medicine_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single medicine
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE medicine_id = :medicine_id 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->medicine_name = $row['medicine_name'];
            $this->description = $row['description'];
            $this->current_stock = $row['current_stock'];
            $this->dateCreated = $row['dateCreated'];
            return $row;
        }
        return false;
    }

    // Update medicine
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET medicine_name = :medicine_name, 
                      description = :description, 
                      current_stock = :current_stock
                  WHERE medicine_id = :medicine_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->current_stock = htmlspecialchars(strip_tags($this->current_stock));
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));

        // Bind parameters
        $stmt->bindParam(':medicine_name', $this->medicine_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':current_stock', $this->current_stock);
        $stmt->bindParam(':medicine_id', $this->medicine_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete medicine
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET dateDeleted = CURRENT_TIMESTAMP 
                  WHERE medicine_id = :medicine_id";
        $stmt = $this->conn->prepare($query);

        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));
        $stmt->bindParam(':medicine_id', $this->medicine_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Find medicine by name (create if doesn't exist)
    public function findOrCreateByName($medicine_name) {
        $query = "SELECT medicine_id FROM " . $this->table . " 
                  WHERE medicine_name = :medicine_name 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medicine_name', $medicine_name);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $row['medicine_id'];
        }
        
        // Medicine doesn't exist, create it
        $this->medicine_name = $medicine_name;
        $this->description = '';
        $this->current_stock = 0;
        
        if ($this->create()) {
            return $this->medicine_id;
        }
        
        return false;
    }

    // Decrease stock
    public function decreaseStock($quantity) {
        $query = "UPDATE " . $this->table . " 
                  SET current_stock = current_stock - :quantity 
                  WHERE medicine_id = :medicine_id 
                  AND current_stock >= :quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    // Increase stock
    public function increaseStock($quantity) {
        $query = "UPDATE " . $this->table . " 
                  SET current_stock = current_stock + :quantity 
                  WHERE medicine_id = :medicine_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        
        return $stmt->execute();
    }
}

?>
