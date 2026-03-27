<?php

class Symptom {
    private $conn;
    private $table = 'symptoms';

    public $symptom_id;
    public $symptom_name;
    public $category;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    /** All active symptoms, grouped by category */
    public function readAllActive() {
        $stmt = $this->conn->query(
            "SELECT symptom_id, symptom_name, category
             FROM {$this->table}
             WHERE is_active = 1
             ORDER BY category ASC, symptom_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** All symptoms including inactive (for admin management) */
    public function readAll() {
        $stmt = $this->conn->query(
            "SELECT * FROM {$this->table} ORDER BY category ASC, symptom_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Single symptom */
    public function readOne() {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE symptom_id = :id LIMIT 1"
        );
        $stmt->bindParam(':id', $this->symptom_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function create() {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (symptom_name, category, is_active)
             VALUES (:name, :cat, :active)"
        );
        $stmt->bindParam(':name',   $this->symptom_name);
        $stmt->bindParam(':cat',    $this->category);
        $stmt->bindParam(':active', $this->is_active);

        if ($stmt->execute()) {
            $this->symptom_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET symptom_name = :name, category = :cat, is_active = :active
             WHERE symptom_id = :id"
        );
        $stmt->bindParam(':name',   $this->symptom_name);
        $stmt->bindParam(':cat',    $this->category);
        $stmt->bindParam(':active', $this->is_active);
        $stmt->bindParam(':id',     $this->symptom_id);
        return $stmt->execute();
    }

    public function delete() {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE symptom_id = :id"
        );
        $stmt->bindParam(':id', $this->symptom_id);
        return $stmt->execute();
    }

    /** All distinct categories (for filter dropdown) */
    public function getCategories() {
        $stmt = $this->conn->query(
            "SELECT DISTINCT category FROM {$this->table} ORDER BY category ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

?>
