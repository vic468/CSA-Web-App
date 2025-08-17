-- Database Setup for NAZZY's Thrift Shop
-- Run this script in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS nazzys_thrift_shop;
USE nazzys_thrift_shop;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    brand VARCHAR(100),
    condition ENUM('excellent', 'good', 'fair', 'poor') NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 1,
    location VARCHAR(100),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_sold TIMESTAMP NULL,
    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    added_by INT,
    FOREIGN KEY (added_by) REFERENCES users(id)
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    loyalty_points INT DEFAULT 0,
    total_purchases DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sales table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile') NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    served_by INT,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (served_by) REFERENCES users(id)
);

-- Sale items table
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id)
);

-- Staff schedule table
CREATE TABLE IF NOT EXISTS staff_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT DEFAULT 30, -- minutes
    status ENUM('scheduled', 'completed', 'absent', 'late') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role) VALUES 
('admin', 'admin@nazzysthrift.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample inventory categories for testing
INSERT INTO inventory (item_name, description, category, condition, purchase_price, selling_price, quantity, status, added_by) VALUES
('Vintage Denim Jacket', 'Classic blue denim jacket in excellent condition', 'Outerwear', 'excellent', 15.00, 35.00, 1, 'available', 1),
('Designer Handbag', 'Authentic leather handbag with minor wear', 'Accessories', 'good', 25.00, 65.00, 1, 'available', 1),
('Cotton T-Shirt', 'Soft cotton t-shirt, various colors', 'Clothing', 'good', 3.00, 12.00, 5, 'available', 1)
ON DUPLICATE KEY UPDATE item_name=item_name;

-- Insert sample customer
INSERT INTO customers (first_name, last_name, email, phone, loyalty_points) VALUES
('John', 'Doe', 'john.doe@email.com', '+1234567890', 50)
ON DUPLICATE KEY UPDATE first_name=first_name;
