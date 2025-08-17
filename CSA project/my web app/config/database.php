<?php
// Secure Database Configuration
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Use environment variables for security
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'nazzys_thrift_shop';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                )
            );
        } catch(PDOException $exception) {
            error_log("Database connection failed: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}
?>
