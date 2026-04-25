<?php

class Database {
    private $host = "localhost";
    private $db_name = "educational_platform";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    public function testConnection() {
        $conn = $this->connect();
        return $conn ? true : false;
    }
}

// Global function to get database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $database = new Database();
        $pdo = $database->connect();
    }
    return $pdo;
}