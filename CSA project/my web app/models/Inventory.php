<?php
require_once 'config/database.php';

class Inventory {
    private $conn;
    private $table_name = "inventory";
    
    public $id;
    public $item_name;
    public $description;
    public $category;
    public $size;
    public $color;
    public $brand;
    public $condition;
    public $purchase_price;
    public $selling_price;
    public $quantity;
    public $location;
    public $date_added;
    public $date_sold;
    public $status; // available, sold, reserved
    public $added_by;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        if ($this->conn === null) {
            throw new Exception("Database connection failed. Please check if MySQL is running and database 'nazzys_thrift_shop' exists.");
        }
    }
    
    // Add new inventory item
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (item_name, description, category, size, color, brand, condition, 
                   purchase_price, selling_price, quantity, location, status, added_by, date_added) 
                  VALUES (:item_name, :description, :category, :size, :color, :brand, :condition,
                          :purchase_price, :selling_price, :quantity, :location, :status, :added_by, NOW())";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(":item_name", $data['item_name']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":size", $data['size']);
            $stmt->bindParam(":color", $data['color']);
            $stmt->bindParam(":brand", $data['brand']);
            $stmt->bindParam(":condition", $data['condition']);
            $stmt->bindParam(":purchase_price", $data['purchase_price']);
            $stmt->bindParam(":selling_price", $data['selling_price']);
            $stmt->bindParam(":quantity", $data['quantity']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":status", $data['status']);
            $stmt->bindParam(":added_by", $data['added_by']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("Inventory creation failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    // Get all inventory items
    public function getAll($status = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($status) {
            $query .= " WHERE status = :status";
        }
        
        $query .= " ORDER BY date_added DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            if ($status) {
                $stmt->bindParam(":status", $status);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get inventory failed: " . $e->getMessage());
            return [];
        }
    }
    
    // Get inventory item by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Get inventory by ID failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    // Update inventory item
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET item_name = :item_name, description = :description, category = :category,
                      size = :size, color = :color, brand = :brand, condition = :condition,
                      purchase_price = :purchase_price, selling_price = :selling_price,
                      quantity = :quantity, location = :location, status = :status
                  WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":item_name", $data['item_name']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":size", $data['size']);
            $stmt->bindParam(":color", $data['color']);
            $stmt->bindParam(":brand", $data['brand']);
            $stmt->bindParam(":condition", $data['condition']);
            $stmt->bindParam(":purchase_price", $data['purchase_price']);
            $stmt->bindParam(":selling_price", $data['selling_price']);
            $stmt->bindParam(":quantity", $data['quantity']);
            $stmt->bindParam(":location", $data['location']);
            $stmt->bindParam(":status", $data['status']);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update inventory failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Mark item as sold
    public function markAsSold($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'sold', date_sold = NOW() 
                  WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Mark as sold failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete inventory item
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete inventory failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Search inventory
    public function search($searchTerm, $category = null) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (item_name LIKE :search OR description LIKE :search OR brand LIKE :search)";
        
        if ($category) {
            $query .= " AND category = :category";
        }
        
        $query .= " ORDER BY date_added DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $searchTerm . "%";
            $stmt->bindParam(":search", $searchParam);
            
            if ($category) {
                $stmt->bindParam(":category", $category);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search inventory failed: " . $e->getMessage());
            return [];
        }
    }
    
    // Get inventory statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_items,
                    COUNT(CASE WHEN status = 'available' THEN 1 END) as available_items,
                    COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_items,
                    COUNT(CASE WHEN status = 'reserved' THEN 1 END) as reserved_items,
                    SUM(CASE WHEN status = 'available' THEN selling_price * quantity ELSE 0 END) as total_value
                  FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get inventory stats failed: " . $e->getMessage());
            return [];
        }
    }
}
?>
