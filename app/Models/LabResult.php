<?php

class LabResult {
    private $conn;
    private $table = 'lab_results';

    public $lab_id;
    public $patient_id;
    public $lab_type;
    public $file_name;
    public $file_path;
    public $file_size;
    public $file_mime;
    public $notes;
    public $uploaded_by;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO {$this->table}
                  (patient_id, lab_type, file_name, file_path, file_size, file_mime, notes, uploaded_by)
                  VALUES (:patient_id, :lab_type, :file_name, :file_path, :file_size, :file_mime, :notes, :uploaded_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id',  $this->patient_id);
        $stmt->bindParam(':lab_type',    $this->lab_type);
        $stmt->bindParam(':file_name',   $this->file_name);
        $stmt->bindParam(':file_path',   $this->file_path);
        $stmt->bindParam(':file_size',   $this->file_size);
        $stmt->bindParam(':file_mime',   $this->file_mime);
        $stmt->bindParam(':notes',       $this->notes);
        $stmt->bindParam(':uploaded_by', $this->uploaded_by);

        if ($stmt->execute()) {
            $this->lab_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAll($patient_id = null, $lab_type = null) {
        $where = ["(lr.dateDeleted IS NULL OR lr.dateDeleted = '0000-00-00 00:00:00')"];
        $params = [];

        if ($patient_id) {
            $where[]  = 'lr.patient_id = :patient_id';
            $params[':patient_id'] = $patient_id;
        }
        if ($lab_type) {
            $where[]  = 'lr.lab_type = :lab_type';
            $params[':lab_type'] = $lab_type;
        }

        $sql = "SELECT lr.*,
                       p.fullname   AS patient_name,
                       p.patient_type
                FROM {$this->table} lr
                LEFT JOIN patient p ON lr.patient_id = p.patient_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY lr.dateCreated DESC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne() {
        $sql = "SELECT lr.*, p.fullname AS patient_name, p.patient_type
                FROM {$this->table} lr
                LEFT JOIN patient p ON lr.patient_id = p.patient_id
                WHERE lr.lab_id = :lab_id
                AND (lr.dateDeleted IS NULL OR lr.dateDeleted = '0000-00-00 00:00:00')
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lab_id', $this->lab_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete() {
        $sql  = "UPDATE {$this->table} SET dateDeleted = CURRENT_TIMESTAMP WHERE lab_id = :lab_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lab_id', $this->lab_id);
        return $stmt->execute();
    }

    public function searchPatients($query) {
        $sql  = "SELECT patient_id, fullname, patient_type
                 FROM patient
                 WHERE (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                 AND fullname LIKE :q
                 ORDER BY fullname ASC
                 LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':q', '%' . $query . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
