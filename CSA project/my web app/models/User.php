<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $role;
    public $is_active;
    public $created_at;
    public $updated_at;
    public $last_login;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        if ($this->conn === null) {
            throw new Exception("Database connection failed. Please check if MySQL is running and database 'nazzys_thrift_shop' exists.");
        }
    }
    
    // Create new user
    public function create($username, $email, $password, $role = 'staff') {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Check if user already exists
        if ($this->userExists($username, $email)) {
            return false;
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare query
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, role, is_active, created_at) 
                  VALUES (:username, :email, :password_hash, :role, 1, NOW())";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password_hash", $password_hash);
            $stmt->bindParam(":role", $role);
            
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    // Authenticate user
    public function authenticate($username, $password) {
        // Get user by username
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username AND is_active = 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $row['password_hash'])) {
                    // Update last login
                    $this->updateLastLogin($row['id']);
                    
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    
                    return ['success' => true, 'user' => $row];
                }
            }
        } catch (PDOException $e) {
            error_log("Authentication failed: " . $e->getMessage());
        }
        
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    // Check if user is authenticated
    public function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    // Get user by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND is_active = 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Get user by ID failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    // Check if user exists
    private function userExists($username, $email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username OR email = :email";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("User exists check failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Update last login time
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :user_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update last login failed: " . $e->getMessage());
        }
    }
    
    // Logout user
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return true;
    }
    
    // Get all users
    public function getAll() {
        $query = "SELECT id, username, email, role, is_active, created_at, last_login 
                  FROM " . $this->table_name . " ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all users failed: " . $e->getMessage());
            return [];
        }
    }
    
    // Update user
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, role = :role, 
                      is_active = :is_active, updated_at = NOW()
                  WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":is_active", $data['is_active']);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update user failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete user (soft delete)
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete user failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
