<?php
require_once '../models/User.php';
require_once '../models/Security.php';

// Initialize models
$user = new User();
$security = new Security();

// Check if user is authenticated
if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Verify CSRF token
if (!$security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

// Get the action
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $result = addCustomer();
            break;
        case 'edit':
            $result = editCustomer();
            break;
        case 'delete':
            $result = deleteCustomer();
            break;
        case 'get':
            $result = getCustomer();
            break;
        case 'search':
            $result = searchCustomers();
            break;
        case 'get_all':
            $result = getAllCustomers();
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function addCustomer() {
    // Validate required fields
    if (empty($_POST['name'])) {
        throw new Exception('Customer name is required');
    }
    
    // Sanitize and validate data
    $data = [
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'loyalty_member' => isset($_POST['loyalty_member']) ? 1 : 0,
        'loyalty_points' => 0,
        'total_purchases' => 0.00,
        'created_date' => date('Y-m-d H:i:s')
    ];
    
    // Validate email format if provided
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check if customer already exists (by email or phone)
    if (!empty($data['email']) && customerExists('email', $data['email'])) {
        throw new Exception('Customer with this email already exists');
    }
    
    if (!empty($data['phone']) && customerExists('phone', $data['phone'])) {
        throw new Exception('Customer with this phone number already exists');
    }
    
    // Save customer
    $customerId = saveCustomer($data);
    
    return [
        'success' => true,
        'message' => 'Customer added successfully',
        'customer_id' => $customerId
    ];
}

function editCustomer() {
    $customerId = intval($_POST['customer_id'] ?? 0);
    if ($customerId <= 0) {
        throw new Exception('Invalid customer ID');
    }
    
    // Get existing customer
    $existingCustomer = getCustomerById($customerId);
    if (!$existingCustomer) {
        throw new Exception('Customer not found');
    }
    
    // Prepare update data
    $data = [];
    $updateFields = ['name', 'email', 'phone', 'address'];
    
    foreach ($updateFields as $field) {
        if (isset($_POST[$field])) {
            $data[$field] = trim($_POST[$field]);
        }
    }
    
    // Handle loyalty membership
    if (isset($_POST['loyalty_member'])) {
        $data['loyalty_member'] = 1;
    }
    
    // Validate email format if provided
    if (isset($data['email']) && !empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check for duplicates (excluding current customer)
    if (isset($data['email']) && !empty($data['email'])) {
        $existing = customerExists('email', $data['email'], $customerId);
        if ($existing) {
            throw new Exception('Another customer with this email already exists');
        }
    }
    
    if (isset($data['phone']) && !empty($data['phone'])) {
        $existing = customerExists('phone', $data['phone'], $customerId);
        if ($existing) {
            throw new Exception('Another customer with this phone number already exists');
        }
    }
    
    // Update customer
    $success = updateCustomer($customerId, $data);
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Customer updated successfully'
        ];
    } else {
        throw new Exception('Failed to update customer');
    }
}

function deleteCustomer() {
    $customerId = intval($_POST['customer_id'] ?? 0);
    if ($customerId <= 0) {
        throw new Exception('Invalid customer ID');
    }
    
    $success = deleteCustomerById($customerId);
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Customer deleted successfully'
        ];
    } else {
        throw new Exception('Failed to delete customer');
    }
}

function getCustomer() {
    $customerId = intval($_POST['customer_id'] ?? 0);
    if ($customerId <= 0) {
        throw new Exception('Invalid customer ID');
    }
    
    $customer = getCustomerById($customerId);
    
    if ($customer) {
        return [
            'success' => true,
            'customer' => $customer
        ];
    } else {
        throw new Exception('Customer not found');
    }
}

function searchCustomers() {
    $query = trim($_POST['query'] ?? '');
    if (empty($query)) {
        throw new Exception('Search query is required');
    }
    
    $customers = searchCustomersByQuery($query);
    
    return [
        'success' => true,
        'customers' => $customers
    ];
}

function getAllCustomers() {
    $customers = getAllCustomersFromFile();
    
    return [
        'success' => true,
        'customers' => $customers
    ];
}

// File-based customer management functions
function saveCustomer($data) {
    $customersFile = '../data/customers.json';
    
    // Create data directory if it doesn't exist
    $dataDir = '../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Load existing customers
    $customers = [];
    if (file_exists($customersFile)) {
        $customers = json_decode(file_get_contents($customersFile), true) ?? [];
    }
    
    // Generate customer ID
    $customerId = count($customers) + 1;
    $data['id'] = $customerId;
    
    // Add customer to array
    $customers[] = $data;
    
    // Save back to file
    file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
    
    return $customerId;
}

function getCustomerById($customerId) {
    $customers = getAllCustomersFromFile();
    
    foreach ($customers as $customer) {
        if ($customer['id'] == $customerId) {
            return $customer;
        }
    }
    
    return null;
}

function updateCustomer($customerId, $data) {
    $customersFile = '../data/customers.json';
    
    if (!file_exists($customersFile)) {
        return false;
    }
    
    $customers = json_decode(file_get_contents($customersFile), true) ?? [];
    
    // Find and update customer
    foreach ($customers as &$customer) {
        if ($customer['id'] == $customerId) {
            foreach ($data as $key => $value) {
                $customer[$key] = $value;
            }
            $customer['updated_date'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    // Save back to file
    file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
    
    return true;
}

function deleteCustomerById($customerId) {
    $customersFile = '../data/customers.json';
    
    if (!file_exists($customersFile)) {
        return false;
    }
    
    $customers = json_decode(file_get_contents($customersFile), true) ?? [];
    
    // Filter out the customer to delete
    $customers = array_filter($customers, function($customer) use ($customerId) {
        return $customer['id'] != $customerId;
    });
    
    // Re-index array
    $customers = array_values($customers);
    
    // Save back to file
    file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
    
    return true;
}

function getAllCustomersFromFile() {
    $customersFile = '../data/customers.json';
    
    if (!file_exists($customersFile)) {
        return [];
    }
    
    return json_decode(file_get_contents($customersFile), true) ?? [];
}

function customerExists($field, $value, $excludeId = null) {
    $customers = getAllCustomersFromFile();
    
    foreach ($customers as $customer) {
        if ($excludeId && $customer['id'] == $excludeId) {
            continue;
        }
        
        if (isset($customer[$field]) && $customer[$field] === $value) {
            return true;
        }
    }
    
    return false;
}

function searchCustomersByQuery($query) {
    $customers = getAllCustomersFromFile();
    $query = strtolower($query);
    
    return array_filter($customers, function($customer) use ($query) {
        return strpos(strtolower($customer['name']), $query) !== false ||
               strpos(strtolower($customer['email'] ?? ''), $query) !== false ||
               strpos(strtolower($customer['phone'] ?? ''), $query) !== false;
    });
}
?>
