<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;

    public function __construct() {
        $env = $this->loadEnv();

        $this->host     = $env['DB_HOST']    ?? 'localhost';
        $this->db_name  = $env['DB_NAME']    ?? 'record_management';
        $this->username = $env['DB_USER']    ?? 'root';
        $this->password = $env['DB_PASS']    ?? '';
        $this->charset  = $env['DB_CHARSET'] ?? 'utf8mb4';
    }

    /**
     * Parse .env file into an associative array.
     * Does NOT use putenv/getenv — safe on shared hosts where putenv is disabled.
     */
    private function loadEnv(): array {
        $envFile = __DIR__ . '/../../.env';
        $vars    = [];

        if (!file_exists($envFile)) {
            return $vars;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and blank lines
            if ($line === '' || $line[0] === '#') continue;
            // Split on the first '=' only
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;
            $vars[trim($parts[0])] = trim($parts[1]);
        }

        return $vars;
    }

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Connection Failed: " . $e->getMessage());
        }

        return $this->conn;
    }
}

?>