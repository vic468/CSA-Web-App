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
        case 'process_sale':
            $result = processSale();
            break;
        case 'get_inventory':
            $result = getAvailableInventory();
            break;
        case 'get_sales':
            $result = getSales();
            break;
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function processSale() {
    global $inventory;
    
    // Validate required fields
    if (empty($_POST['customer_name'])) {
        throw new Exception('Customer name is required');
    }
    
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        throw new Exception('At least one item must be selected');
    }
    
    $customerName = trim($_POST['customer_name']);
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $items = $_POST['items'];
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $discount = floatval($_POST['discount'] ?? 0);
    
    // Validate items and calculate total
    $saleItems = [];
    $subtotal = 0;
    
    foreach ($items as $itemData) {
        $itemId = intval($itemData['id']);
        $quantity = intval($itemData['quantity']);
        
        if ($itemId <= 0 || $quantity <= 0) {
            throw new Exception('Invalid item or quantity');
        }
        
        // Get item from inventory
        $item = $inventory->getById($itemId);
        if (!$item) {
            throw new Exception("Item with ID $itemId not found");
        }
        
        // Check stock availability
        if ($item['stock'] < $quantity) {
            throw new Exception("Insufficient stock for {$item['name']}. Available: {$item['stock']}, Requested: $quantity");
        }
        
        $itemTotal = $item['price'] * $quantity;
        $subtotal += $itemTotal;
        
        $saleItems[] = [
            'id' => $itemId,
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'total' => $itemTotal
        ];
    }
    
    // Apply discount
    $discountAmount = ($subtotal * $discount) / 100;
    $total = $subtotal - $discountAmount;
    
    if ($total < 0) {
        $total = 0;
    }
    
    // Create sales record
    $saleData = [
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone,
        'items' => json_encode($saleItems),
        'subtotal' => $subtotal,
        'discount' => $discount,
        'discount_amount' => $discountAmount,
        'total' => $total,
        'payment_method' => $paymentMethod,
        'sale_date' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id']
    ];
    
    // Save sale to database (we'll create a Sales model later)
    $saleId = saveSale($saleData);
    
    // Update inventory stock
    foreach ($items as $itemData) {
        $itemId = intval($itemData['id']);
        $quantity = intval($itemData['quantity']);
        
        $item = $inventory->getById($itemId);
        $newStock = $item['stock'] - $quantity;
        
        $inventory->update($itemId, ['stock' => $newStock]);
    }
    
    return [
        'success' => true,
        'message' => 'Sale processed successfully',
        'sale_id' => $saleId,
        'total' => $total,
        'receipt' => generateReceipt($saleData, $saleItems)
    ];
}

function getAvailableInventory() {
    global $inventory;
    
    $items = $inventory->getAll();
    
    // Filter only items with stock > 0
    $availableItems = array_filter($items, function($item) {
        return $item['stock'] > 0;
    });
    
    return [
        'success' => true,
        'items' => array_values($availableItems)
    ];
}

function getSales() {
    // Get recent sales (we'll implement this when we create the Sales model)
    $limit = intval($_POST['limit'] ?? 10);
    $sales = getRecentSales($limit);
    
    return [
        'success' => true,
        'sales' => $sales
    ];
}

function saveSale($saleData) {
    // For now, we'll save to a simple file-based system
    // Later this should be moved to a proper Sales model with database
    $salesFile = '../data/sales.json';
    
    // Create data directory if it doesn't exist
    $dataDir = '../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Load existing sales
    $sales = [];
    if (file_exists($salesFile)) {
        $sales = json_decode(file_get_contents($salesFile), true) ?? [];
    }
    
    // Generate sale ID
    $saleId = count($sales) + 1;
    $saleData['id'] = $saleId;
    
    // Add sale to array
    $sales[] = $saleData;
    
    // Save back to file
    file_put_contents($salesFile, json_encode($sales, JSON_PRETTY_PRINT));
    
    return $saleId;
}

function getRecentSales($limit = 10) {
    $salesFile = '../data/sales.json';
    
    if (!file_exists($salesFile)) {
        return [];
    }
    
    $sales = json_decode(file_get_contents($salesFile), true) ?? [];
    
    // Sort by date (newest first) and limit
    usort($sales, function($a, $b) {
        return strtotime($b['sale_date']) - strtotime($a['sale_date']);
    });
    
    return array_slice($sales, 0, $limit);
}

function generateReceipt($saleData, $saleItems) {
    $receipt = [
        'sale_id' => $saleData['id'],
        'date' => $saleData['sale_date'],
        'customer' => $saleData['customer_name'],
        'items' => $saleItems,
        'subtotal' => $saleData['subtotal'],
        'discount' => $saleData['discount'],
        'discount_amount' => $saleData['discount_amount'],
        'total' => $saleData['total'],
        'payment_method' => $saleData['payment_method']
    ];
    
    return $receipt;
}
?>
