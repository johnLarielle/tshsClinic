<?php

class Medicine {
    private $conn;
    private $table = 'medicine';

    public $medicine_id;
    public $medicine_name;
    public $milligrams;
    public $description;
    public $manufactured_date;
    public $expiry_date;
    public $current_stock;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create medicine
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (medicine_name, milligrams, description, manufactured_date, expiry_date, current_stock)
                  VALUES (:medicine_name, :milligrams, :description, :manufactured_date, :expiry_date, :current_stock)";

        $stmt = $this->conn->prepare($query);

        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->milligrams    = $this->milligrams    ? htmlspecialchars(strip_tags($this->milligrams))    : null;
        $this->description   = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->current_stock = htmlspecialchars(strip_tags($this->current_stock ?? 0));

        $mfgDate = !empty($this->manufactured_date) ? $this->manufactured_date : null;
        $expDate = !empty($this->expiry_date)        ? $this->expiry_date        : null;

        $stmt->bindParam(':medicine_name',     $this->medicine_name);
        $stmt->bindParam(':milligrams',        $this->milligrams);
        $stmt->bindParam(':description',       $this->description);
        $stmt->bindParam(':manufactured_date', $mfgDate);
        $stmt->bindParam(':expiry_date',       $expDate);
        $stmt->bindParam(':current_stock',     $this->current_stock);

        if ($stmt->execute()) {
            $this->medicine_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all medicines (active only — soft delete)
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
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
            $this->medicine_name      = $row['medicine_name'];
            $this->milligrams         = $row['milligrams'];
            $this->description        = $row['description'];
            $this->manufactured_date  = $row['manufactured_date'];
            $this->expiry_date        = $row['expiry_date'];
            $this->current_stock      = $row['current_stock'];
            $this->dateCreated        = $row['dateCreated'];
            return $row;
        }
        return false;
    }

    // Update medicine
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET medicine_name     = :medicine_name,
                      milligrams        = :milligrams,
                      description       = :description,
                      manufactured_date = :manufactured_date,
                      expiry_date       = :expiry_date
                  WHERE medicine_id = :medicine_id";

        $stmt = $this->conn->prepare($query);

        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->milligrams    = $this->milligrams    ? htmlspecialchars(strip_tags($this->milligrams))    : null;
        $this->description   = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->medicine_id   = htmlspecialchars(strip_tags($this->medicine_id));

        $mfgDate = !empty($this->manufactured_date) ? $this->manufactured_date : null;
        $expDate = !empty($this->expiry_date)        ? $this->expiry_date        : null;

        $stmt->bindParam(':medicine_name',     $this->medicine_name);
        $stmt->bindParam(':milligrams',        $this->milligrams);
        $stmt->bindParam(':description',       $this->description);
        $stmt->bindParam(':manufactured_date', $mfgDate);
        $stmt->bindParam(':expiry_date',       $expDate);
        $stmt->bindParam(':medicine_id',       $this->medicine_id);

        return $stmt->execute();
    }

    // Soft delete — sets dateDeleted timestamp
    public function delete() {
        $query = "UPDATE " . $this->table . "
                  SET dateDeleted = CURRENT_TIMESTAMP
                  WHERE medicine_id = :medicine_id";
        $stmt = $this->conn->prepare($query);
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        return $stmt->execute();
    }

    // Find medicine by name (create if not found)
    public function findOrCreateByName($medicine_name) {
        $query = "SELECT medicine_id FROM " . $this->table . "
                  WHERE medicine_name = :medicine_name
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medicine_name', $medicine_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row['medicine_id'];

        $this->medicine_name = $medicine_name;
        $this->description   = '';
        $this->current_stock = 0;
        $this->milligrams         = null;
        $this->manufactured_date  = null;
        $this->expiry_date        = null;
        return $this->create() ? $this->medicine_id : false;
    }

    // Decrease stock
    public function decreaseStock($quantity) {
        $query = "UPDATE " . $this->table . "
                  SET current_stock = current_stock - :quantity
                  WHERE medicine_id = :medicine_id
                  AND current_stock >= :quantity";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity',    $quantity);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    // Increase stock
    public function increaseStock($quantity) {
        $query = "UPDATE " . $this->table . "
                  SET current_stock = current_stock + :quantity
                  WHERE medicine_id = :medicine_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity',    $quantity);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        return $stmt->execute();
    }
}
?>
