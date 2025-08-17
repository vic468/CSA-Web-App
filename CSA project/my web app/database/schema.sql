-- NAZZY'S THRIFT SHOP - Complete Database Schema
-- Comprehensive database structure for thrift shop web application

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS nazzys_thrift_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nazzys_thrift_shop;

-- =============================================
-- CORE TABLES
-- =============================================

-- Users table (Staff management)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'cashier') DEFAULT 'staff',
    phone VARCHAR(20) NULL,
    hire_date DATE NULL,
    salary DECIMAL(10,2) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    parent_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(50) NULL,
    zip_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'USA',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(50) NULL,
    zip_code VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    loyalty_points INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (first_name, last_name),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_loyalty (loyalty_points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INVENTORY MANAGEMENT
-- =============================================

-- Inventory items table
CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category_id INT NOT NULL,
    supplier_id INT NULL,
    brand VARCHAR(100) NULL,
    size VARCHAR(50) NULL,
    color VARCHAR(50) NULL,
    condition_rating ENUM('excellent', 'very_good', 'good', 'fair', 'poor') DEFAULT 'good',
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    quantity_in_stock INT NOT NULL DEFAULT 0,
    minimum_stock_level INT DEFAULT 1,
    location VARCHAR(100) NULL,
    barcode VARCHAR(100) NULL,
    weight DECIMAL(8,2) NULL,
    dimensions VARCHAR(100) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    date_acquired DATE NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku),
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_price (selling_price),
    INDEX idx_stock (quantity_in_stock),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_barcode (barcode),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory transactions table (Stock movements)
CREATE TABLE inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id INT NOT NULL,
    transaction_type ENUM('purchase', 'sale', 'adjustment', 'return', 'damage', 'transfer') NOT NULL,
    quantity_change INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    unit_cost DECIMAL(10,2) NULL,
    reference_id INT NULL,
    reference_type VARCHAR(50) NULL,
    notes TEXT NULL,
    created_by INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_item (inventory_item_id),
    INDEX idx_type (transaction_type),
    INDEX idx_date (transaction_date),
    INDEX idx_reference (reference_type, reference_id),
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SALES MANAGEMENT
-- =============================================

-- Sales table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NULL,
    cashier_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'check', 'store_credit', 'layaway') NOT NULL,
    payment_status ENUM('pending', 'completed', 'refunded', 'cancelled') DEFAULT 'pending',
    payment_reference VARCHAR(100) NULL,
    notes TEXT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sale_number (sale_number),
    INDEX idx_customer (customer_id),
    INDEX idx_cashier (cashier_id),
    INDEX idx_date (sale_date),
    INDEX idx_status (payment_status),
    INDEX idx_method (payment_method),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sale items table
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_price DECIMAL(10,2) NOT NULL,
    INDEX idx_sale (sale_id),
    INDEX idx_item (inventory_item_id),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SECURITY & AUDIT TABLES
-- =============================================

-- Security events table
CREATE TABLE security_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    event_data JSON NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (event_type),
    INDEX idx_user (user_id),
    INDEX idx_date (created_at),
    INDEX idx_severity (severity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    INDEX idx_active (is_active),
    INDEX idx_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SYSTEM CONFIGURATION
-- =============================================

-- System configuration table
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    description TEXT NULL,
    data_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL,
    INDEX idx_config_key (config_key),
    INDEX idx_public (is_public),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INITIAL DATA
-- =============================================

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Clothing', 'All types of clothing items'),
('Shoes', 'Footwear for all ages'),
('Accessories', 'Bags, jewelry, belts, etc.'),
('Books', 'Books and magazines'),
('Electronics', 'Electronic devices and gadgets'),
('Home & Garden', 'Home decor and garden items'),
('Toys & Games', 'Children toys and games'),
('Sports & Outdoors', 'Sports equipment and outdoor gear'),
('Vintage', 'Vintage and antique items'),
('Furniture', 'Furniture and home furnishing');

-- Insert default suppliers
INSERT INTO suppliers (name, contact_person, email, phone, address, city, state, zip_code) VALUES
('Local Donations', 'Donation Center', 'donations@nazzysthrift.com', '(555) 123-4567', '123 Main St', 'Anytown', 'ST', '12345'),
('Estate Sales Co.', 'John Smith', 'john@estatesales.com', '(555) 234-5678', '456 Oak Ave', 'Somewhere', 'ST', '23456'),
('Wholesale Vintage', 'Mary Johnson', 'mary@wholesalevintage.com', '(555) 345-6789', '789 Pine Rd', 'Elsewhere', 'ST', '34567');

-- Insert system configuration
INSERT INTO system_config (config_key, config_value, description, data_type, is_public) VALUES
('store_name', 'NAZZY\'s THRIFT SHOP', 'Store name for display', 'string', TRUE),
('store_address', '123 Vintage Lane, Retro City, RC 12345', 'Store address', 'string', TRUE),
('store_phone', '(555) 123-4567', 'Store phone number', 'string', TRUE),
('store_email', 'info@nazzysthriftshop.com', 'Store email address', 'string', TRUE),
('tax_rate', '8.5', 'Default sales tax rate percentage', 'number', TRUE),
('currency_symbol', '$', 'Currency symbol for display', 'string', TRUE),
('max_login_attempts', '5', 'Maximum failed login attempts before lockout', 'number', FALSE),
('lockout_duration', '900', 'Account lockout duration in seconds', 'number', FALSE),
('session_timeout', '1800', 'Session timeout in seconds', 'number', FALSE),
('low_stock_threshold', '5', 'Alert when stock falls below this number', 'number', FALSE);

-- Create admin user (password: Admin@123)
-- In production, this should be changed immediately
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active) VALUES
('admin', 'admin@nazzysthriftshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', TRUE);

-- =============================================
-- VIEWS FOR REPORTING
-- =============================================

-- Sales summary view
CREATE VIEW sales_summary AS
SELECT 
    DATE(sale_date) as sale_date,
    COUNT(*) as total_sales,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_sale_amount,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as cash_sales,
    SUM(CASE WHEN payment_method IN ('credit_card', 'debit_card') THEN total_amount ELSE 0 END) as card_sales
FROM sales 
WHERE payment_status = 'completed'
GROUP BY DATE(sale_date)
ORDER BY sale_date DESC;

-- Top selling items view
CREATE VIEW top_selling_items AS
SELECT 
    ii.id,
    ii.sku,
    ii.name,
    c.name as category,
    SUM(si.quantity) as total_sold,
    SUM(si.final_price) as total_revenue,
    AVG(si.unit_price) as avg_price,
    ii.quantity_in_stock as current_stock
FROM sale_items si
JOIN inventory_items ii ON si.inventory_item_id = ii.id
JOIN categories c ON ii.category_id = c.id
JOIN sales s ON si.sale_id = s.id
WHERE s.payment_status = 'completed'
GROUP BY ii.id, ii.sku, ii.name, c.name, ii.quantity_in_stock
ORDER BY total_sold DESC;

-- Low stock alert view
CREATE VIEW low_stock_items AS
SELECT 
    ii.id,
    ii.sku,
    ii.name,
    c.name as category,
    ii.quantity_in_stock,
    ii.minimum_stock_level,
    ii.selling_price,
    s.name as supplier_name
FROM inventory_items ii
JOIN categories c ON ii.category_id = c.id
LEFT JOIN suppliers s ON ii.supplier_id = s.id
WHERE ii.quantity_in_stock <= ii.minimum_stock_level
AND ii.is_active = TRUE
ORDER BY ii.quantity_in_stock ASC;

-- Customer statistics view
CREATE VIEW customer_stats AS
SELECT 
    c.id,
    CONCAT(c.first_name, ' ', c.last_name) as full_name,
    c.email,
    c.phone,
    c.loyalty_points,
    c.total_spent,
    COUNT(s.id) as total_orders,
    MAX(s.sale_date) as last_purchase_date,
    AVG(s.total_amount) as avg_order_value
FROM customers c
LEFT JOIN sales s ON c.id = s.customer_id AND s.payment_status = 'completed'
GROUP BY c.id, c.first_name, c.last_name, c.email, c.phone, c.loyalty_points, c.total_spent
ORDER BY c.total_spent DESC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Procedure to update inventory after sale
CREATE PROCEDURE UpdateInventoryAfterSale(
    IN p_inventory_item_id INT,
    IN p_quantity_sold INT,
    IN p_sale_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE current_stock INT;
    DECLARE new_stock INT;
    
    -- Get current stock
    SELECT quantity_in_stock INTO current_stock 
    FROM inventory_items 
    WHERE id = p_inventory_item_id;
    
    -- Calculate new stock
    SET new_stock = current_stock - p_quantity_sold;
    
    -- Update inventory
    UPDATE inventory_items 
    SET quantity_in_stock = new_stock,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_inventory_item_id;
    
    -- Record transaction
    INSERT INTO inventory_transactions (
        inventory_item_id, transaction_type, quantity_change, 
        quantity_before, quantity_after, reference_id, 
        reference_type, created_by
    ) VALUES (
        p_inventory_item_id, 'sale', -p_quantity_sold,
        current_stock, new_stock, p_sale_id,
        'sale', p_user_id
    );
END //

-- Procedure to add inventory
CREATE PROCEDURE AddInventoryStock(
    IN p_inventory_item_id INT,
    IN p_quantity_added INT,
    IN p_unit_cost DECIMAL(10,2),
    IN p_user_id INT,
    IN p_notes TEXT
)
BEGIN
    DECLARE current_stock INT;
    DECLARE new_stock INT;
    
    -- Get current stock
    SELECT quantity_in_stock INTO current_stock 
    FROM inventory_items 
    WHERE id = p_inventory_item_id;
    
    -- Calculate new stock
    SET new_stock = current_stock + p_quantity_added;
    
    -- Update inventory
    UPDATE inventory_items 
    SET quantity_in_stock = new_stock,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_inventory_item_id;
    
    -- Record transaction
    INSERT INTO inventory_transactions (
        inventory_item_id, transaction_type, quantity_change, 
        quantity_before, quantity_after, unit_cost, notes, created_by
    ) VALUES (
        p_inventory_item_id, 'purchase', p_quantity_added,
        current_stock, new_stock, p_unit_cost, p_notes, p_user_id
    );
END //

DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger to update customer total spent after sale
DELIMITER //
CREATE TRIGGER update_customer_total_spent 
AFTER UPDATE ON sales
FOR EACH ROW
BEGIN
    IF NEW.payment_status = 'completed' AND OLD.payment_status != 'completed' THEN
        UPDATE customers 
        SET total_spent = total_spent + NEW.total_amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.customer_id;
    END IF;
END //
DELIMITER ;

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Additional performance indexes
CREATE INDEX idx_sales_date_status ON sales(sale_date, payment_status);
CREATE INDEX idx_inventory_price_active ON inventory_items(selling_price, is_active);
CREATE INDEX idx_inventory_stock_level ON inventory_items(quantity_in_stock, minimum_stock_level);
CREATE INDEX idx_customers_spent ON customers(total_spent DESC);
CREATE INDEX idx_security_events_date_type ON security_events(created_at, event_type);

-- =============================================
-- PERMISSIONS (Uncomment and adjust as needed)
-- =============================================

-- CREATE USER 'thrift_app'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON nazzys_thrift_shop.* TO 'thrift_app'@'localhost';
-- GRANT EXECUTE ON nazzys_thrift_shop.* TO 'thrift_app'@'localhost';
-- FLUSH PRIVILEGES;
