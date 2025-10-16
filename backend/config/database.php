<?php
class Database
{
    private $host = "localhost";
    private $database_name = "rads_tooling";
    private $username = "root";
    private $password = "";
    private $conn = null;

    public function getConnection()
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            return $this->conn;
        } catch (PDOException $e) {
            // Log the error (in production, log to file instead of displaying)
            error_log("Database connection error: " . $e->getMessage());

            // Don't expose sensitive database info in production
            if (defined('DEBUG')) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } else {
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
    }

    public function testConnection()
    {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}