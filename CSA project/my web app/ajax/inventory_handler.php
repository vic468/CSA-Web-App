<?php
require_once '../models/User.php';
require_once '../models/Security.php';
require_once '../models/Inventory.php';

// Initialize models
$user = new User();
$security = new Security();
$inventory = new Inventory();

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
            $result = addInventoryItem();
            break;
        case 'edit':
            $result = editInventoryItem();
            break;
        case 'delete':
            $result = deleteInventoryItem();
            break;
        case 'get':
            $result = getInventoryItem();
            break;
        case 'get_all':
            $result = getAllInventoryItems();
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function addInventoryItem() {
    global $inventory;
    
    // Validate required fields
    $required = ['name', 'category', 'price', 'stock', 'condition'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Sanitize and validate data
    $data = [
        'name' => trim($_POST['name']),
        'category' => trim($_POST['category']),
        'price' => floatval($_POST['price']),
        'stock' => intval($_POST['stock']),
        'condition' => trim($_POST['condition']),
        'description' => trim($_POST['description'] ?? ''),
        'image' => null // Will handle file upload separately
    ];
    
    // Validate price and stock
    if ($data['price'] <= 0) {
        throw new Exception('Price must be greater than 0');
    }
    
    if ($data['stock'] < 0) {
        throw new Exception('Stock cannot be negative');
    }
    
    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $data['image'] = handleImageUpload($_FILES['image']);
    }
    
    // Add item to inventory
    $itemId = $inventory->create($data);
    
    if ($itemId) {
        return [
            'success' => true,
            'message' => 'Item added successfully',
            'item_id' => $itemId
        ];
    } else {
        throw new Exception('Failed to add item to inventory');
    }
}

function editInventoryItem() {
    global $inventory;
    
    $itemId = intval($_POST['item_id'] ?? 0);
    if ($itemId <= 0) {
        throw new Exception('Invalid item ID');
    }
    
    // Get existing item
    $existingItem = $inventory->getById($itemId);
    if (!$existingItem) {
        throw new Exception('Item not found');
    }
    
    // Prepare update data
    $data = [];
    $updateFields = ['name', 'category', 'price', 'stock', 'condition', 'description'];
    
    foreach ($updateFields as $field) {
        if (isset($_POST[$field])) {
            $data[$field] = trim($_POST[$field]);
        }
    }
    
    // Validate numeric fields
    if (isset($data['price'])) {
        $data['price'] = floatval($data['price']);
        if ($data['price'] <= 0) {
            throw new Exception('Price must be greater than 0');
        }
    }
    
    if (isset($data['stock'])) {
        $data['stock'] = intval($data['stock']);
        if ($data['stock'] < 0) {
            throw new Exception('Stock cannot be negative');
        }
    }
    
    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $data['image'] = handleImageUpload($_FILES['image']);
    }
    
    // Update item
    $success = $inventory->update($itemId, $data);
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Item updated successfully'
        ];
    } else {
        throw new Exception('Failed to update item');
    }
}

function deleteInventoryItem() {
    global $inventory;
    
    $itemId = intval($_POST['item_id'] ?? 0);
    if ($itemId <= 0) {
        throw new Exception('Invalid item ID');
    }
    
    $success = $inventory->delete($itemId);
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Item deleted successfully'
        ];
    } else {
        throw new Exception('Failed to delete item');
    }
}

function getInventoryItem() {
    global $inventory;
    
    $itemId = intval($_POST['item_id'] ?? 0);
    if ($itemId <= 0) {
        throw new Exception('Invalid item ID');
    }
    
    $item = $inventory->getById($itemId);
    
    if ($item) {
        return [
            'success' => true,
            'item' => $item
        ];
    } else {
        throw new Exception('Item not found');
    }
}

function getAllInventoryItems() {
    global $inventory;
    
    $items = $inventory->getAll();
    
    return [
        'success' => true,
        'items' => $items
    ];
}

function handleImageUpload($file) {
    $uploadDir = '../uploads/inventory/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('item_') . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/inventory/' . $filename;
    } else {
        throw new Exception('Failed to upload image');
    }
}
?>
