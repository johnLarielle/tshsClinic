<?php

class ActivityLog {
    private $conn;
    private $table = 'activity_log';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Write a log entry.
     *
     * @param string $action       e.g. 'ADD_RECORD', 'DELETE_MEDICINE'
     * @param string $module       e.g. 'Patient Records', 'Medicine', 'Auth'
     * @param string $description  Human-readable summary
     * @param string $username     Session username  (default: 'system')
     * @param string $fullname     Session full name (default: 'System')
     */
    public function log($action, $module, $description, $username = 'system', $fullname = 'System') {
        $query = "INSERT INTO " . $this->table . "
                    (admin_username, admin_fullname, action, module, description, ip_address)
                  VALUES
                    (:username, :fullname, :action, :module, :description, :ip)";

        $stmt = $this->conn->prepare($query);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt->bindParam(':username',    $username);
        $stmt->bindParam(':fullname',    $fullname);
        $stmt->bindParam(':action',      $action);
        $stmt->bindParam(':module',      $module);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip',          $ip);

        $stmt->execute();
    }

    /**
     * Get logs with optional filters.
     *
     * @param array $filters  Keys: module, action, date_from, date_to, search
     * @param int   $limit
     * @param int   $offset
     */
    public function getAll($filters = [], $limit = 50, $offset = 0) {
        $where  = [];
        $params = [];

        if (!empty($filters['module'])) {
            $where[]            = 'module = :module';
            $params[':module']  = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $where[]            = 'action = :action';
            $params[':action']  = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $where[]               = 'DATE(created_at) >= :date_from';
            $params[':date_from']  = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[]             = 'DATE(created_at) <= :date_to';
            $params[':date_to']  = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[]             = '(admin_username LIKE :search OR admin_fullname LIKE :search OR description LIKE :search)';
            $params[':search']   = '%' . $filters['search'] . '%';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT * FROM " . $this->table . "
                  $whereSQL
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limit',  (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count logs with same filters (for pagination).
     */
    public function count($filters = []) {
        $where  = [];
        $params = [];

        if (!empty($filters['module'])) {
            $where[]            = 'module = :module';
            $params[':module']  = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $where[]            = 'action = :action';
            $params[':action']  = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $where[]               = 'DATE(created_at) >= :date_from';
            $params[':date_from']  = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[]             = 'DATE(created_at) <= :date_to';
            $params[':date_to']  = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[]           = '(admin_username LIKE :search OR admin_fullname LIKE :search OR description LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) FROM " . $this->table . " $whereSQL";
        $stmt  = $this->conn->prepare($query);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get distinct list of modules (for filter dropdown).
     */
    public function getModules() {
        $stmt = $this->conn->query("SELECT DISTINCT module FROM " . $this->table . " ORDER BY module ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get distinct list of actions (for filter dropdown).
     */
    public function getActions() {
        $stmt = $this->conn->query("SELECT DISTINCT action FROM " . $this->table . " ORDER BY action ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

?>
