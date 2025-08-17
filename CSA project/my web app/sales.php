<?php
require_once 'models/User.php';

// Initialize user model
$user = new User();

// Check if user is authenticated
if (!$user->isAuthenticated()) {
    header('Location: login.php');
    exit();
}

// Get user data
$userData = $user->getById($_SESSION['user_id']);

// Handle logout
if (isset($_GET['logout'])) {
    $user->logout();
    header('Location: login.php');
    exit();
}

// Initialize empty sales data - will be populated from database
$recentSales = [];

$salesStats = [
    'today' => 0.00,
    'week' => 0.00,
    'month' => 0.00,
    'transactions' => 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Sales Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2D1B69 0%, #4A148C 100%);
            --secondary-gradient: linear-gradient(135deg, #7B1FA2 0%, #9C27B0 100%);
            --success-gradient: linear-gradient(135deg, #388E3C 0%, #4CAF50 100%);
            --warning-gradient: linear-gradient(135deg, #F57C00 0%, #FF9800 100%);
            --danger-gradient: linear-gradient(135deg, #D32F2F 0%, #F44336 100%);
            --info-gradient: linear-gradient(135deg, #1976D2 0%, #2196F3 100%);
            --thrift-brown: #2D1B69;
            --thrift-gold: #9C27B0;
            --thrift-orange: #7B1FA2;
            --thrift-cream: #F3E5F5;
            --shadow-light: 0 4px 15px rgba(45, 27, 105, 0.1);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(156, 39, 176, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(123, 31, 162, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(45, 27, 105, 0.1) 0%, transparent 50%);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 3px solid var(--thrift-gold);
            box-shadow: var(--shadow-light);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--thrift-brown) !important;
            font-size: 1.5rem;
        }

        .navbar-brand i {
            color: var(--thrift-gold);
            margin-right: 10px;
        }

        .nav-link {
            color: var(--thrift-brown) !important;
            font-weight: 500;
            transition: var(--transition);
            border-radius: 8px;
            margin: 0 5px;
        }

        .nav-link:hover {
            background: var(--thrift-gold);
            color: var(--thrift-brown) !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: var(--thrift-gold);
            color: var(--thrift-brown) !important;
        }

        .btn-logout {
            background: var(--danger-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
            color: white;
        }

        .main-container {
            padding: 30px 20px;
        }

        .page-header {
            background: var(--thrift-cream);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid var(--thrift-gold);
            box-shadow: var(--shadow-heavy);
        }

        .page-title {
            color: var(--thrift-brown);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            background: var(--success-gradient);
            color: white;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--thrift-brown);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .main-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
        }

        .sidebar {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
            height: fit-content;
        }

        .section-title {
            color: var(--thrift-brown);
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1.3rem;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--thrift-gold);
        }

        .sale-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }

        .sale-item:hover {
            background: #f8f9fa;
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .sale-item:last-child {
            border-bottom: none;
        }

        .sale-info h6 {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sale-details {
            color: #666;
            font-size: 0.9rem;
        }

        .sale-amount {
            font-weight: 700;
            color: var(--success-gradient);
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
            background: var(--secondary-gradient);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 15px 10px;
            color: white;
            font-weight: 600;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
            color: white;
            text-decoration: none;
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .action-btn.sales {
            background: var(--success-gradient);
        }

        .action-btn.refunds {
            background: var(--warning-gradient);
        }

        .action-btn.reports {
            background: var(--info-gradient);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-store"></i>
                NAZZY's THRIFT SHOP
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">
                            <i class="fas fa-tags"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sales.php">
                            <i class="fas fa-chart-line"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php">
                            <i class="fas fa-calendar"></i> Schedule
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">
                        <i class="fas fa-user-circle"></i>
                        Welcome, <?php echo htmlspecialchars($userData['username'] ?? 'Staff'); ?>
                    </span>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Sales Management
            </h1>
            <p class="page-subtitle">
                Track sales performance, manage transactions, and analyze revenue
            </p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($salesStats['today'], 2); ?></div>
                <div class="stat-label">Sales Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($salesStats['week'], 2); ?></div>
                <div class="stat-label">This Week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($salesStats['month'], 2); ?></div>
                <div class="stat-label">This Month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo $salesStats['transactions']; ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Sales
                </h3>
                
                <?php if (empty($recentSales)): ?>
                <div class="empty-state">
                    <i class="fas fa-cash-register"></i>
                    <h5>No Recent Sales</h5>
                    <p>Sales transactions will appear here once you start making sales.</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                        <i class="fas fa-plus me-2"></i>Make Your First Sale
                    </button>
                </div>
                <?php else: ?>
                    <?php foreach ($recentSales as $sale): ?>
                    <div class="sale-item">
                        <div class="sale-info">
                            <h6><?php echo htmlspecialchars($sale['customer']); ?></h6>
                            <div class="sale-details">
                                <?php echo htmlspecialchars($sale['items']); ?> • 
                                <?php echo date('M j, g:i A', strtotime($sale['date'])); ?>
                            </div>
                        </div>
                        <div class="sale-amount">$<?php echo number_format($sale['total'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-4">
                        <button class="btn btn-primary">
                            <i class="fas fa-eye"></i> View All Sales
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <h3 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h3>
                
                <div class="quick-actions">
                    <a href="#" class="action-btn sales" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                        <i class="fas fa-cash-register"></i>
                        New Sale
                    </a>
                    <a href="inventory.php" class="action-btn refunds">
                        <i class="fas fa-undo"></i>
                        Process Refund
                    </a>
                    <a href="reports.php" class="action-btn reports">
                        <i class="fas fa-file-alt"></i>
                        Sales Report
                    </a>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="text-center mb-2">
                        <i class="fas fa-chart-pie text-primary"></i>
                        Sales Summary
                    </h6>
                    <div class="text-center">
                        <div class="text-success fw-bold">Today: $<?php echo number_format($salesStats['today'], 2); ?></div>
                        <div class="text-primary">Week: $<?php echo number_format($salesStats['week'], 2); ?></div>
                        <div class="text-info">Month: $<?php echo number_format($salesStats['month'], 2); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Sale Modal -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSaleModalLabel">
                        <i class="fas fa-cash-register me-2"></i>New Sale Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newSaleForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customerName" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customerName" name="customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerEmail" class="form-label">Customer Email (Optional)</label>
                                    <input type="email" class="form-control" id="customerEmail" name="customer_email">
                                </div>
                                <div class="mb-3">
                                    <label for="paymentMethod" class="form-label">Payment Method</label>
                                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="check">Check</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemSelect" class="form-label">Select Items</label>
                                    <select class="form-select" id="itemSelect">
                                        <option value="">Choose an item to add</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Selected Items</label>
                                    <div id="selectedItems" class="border rounded p-3" style="min-height: 120px; background: #f8f9fa;">
                                        <p class="text-muted mb-0">No items selected</p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="saleNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="saleNotes" name="notes" rows="3" placeholder="Add any additional notes about this sale..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cash-register me-2"></i>Process Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects to sale items
            const saleItems = document.querySelectorAll('.sale-item');
            saleItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Add click handlers for quick actions
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = '50%';
                    ripple.style.top = '50%';
                    ripple.style.width = '100px';
                    ripple.style.height = '100px';
                    ripple.style.marginLeft = '-50px';
                    ripple.style.marginTop = '-50px';
                    
                    this.style.position = 'relative';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Sales functionality
        let selectedItems = [];
        let availableItems = [];

        // Load available inventory items
        async function loadInventoryItems() {
            try {
                const response = await fetch('ajax/inventory_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all&csrf_token=' + encodeURIComponent('<?php echo $_SESSION["csrf_token"]; ?>')
                });
                
                const data = await response.json();
                if (data.success) {
                    availableItems = data.items.filter(item => item.stock > 0);
                    populateItemSelect();
                }
            } catch (error) {
                console.error('Error loading inventory:', error);
            }
        }

        function populateItemSelect() {
            const select = document.getElementById('itemSelect');
            select.innerHTML = '<option value="">Choose an item to add</option>';
            
            availableItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} - $${parseFloat(item.price).toFixed(2)} (Stock: ${item.stock})`;
                select.appendChild(option);
            });
        }

        // Handle item selection
        document.getElementById('itemSelect').addEventListener('change', function() {
            const itemId = this.value;
            if (!itemId) return;
            
            const item = availableItems.find(i => i.id == itemId);
            if (!item) return;
            
            // Check if item already selected
            const existingItem = selectedItems.find(i => i.id == itemId);
            if (existingItem) {
                if (existingItem.quantity < item.stock) {
                    existingItem.quantity++;
                    updateSelectedItems();
                } else {
                    alert('Cannot add more items than available in stock');
                }
            } else {
                selectedItems.push({
                    id: item.id,
                    name: item.name,
                    price: parseFloat(item.price),
                    quantity: 1,
                    maxStock: item.stock
                });
                updateSelectedItems();
            }
            
            this.value = '';
        });

        function updateSelectedItems() {
            const container = document.getElementById('selectedItems');
            
            if (selectedItems.length === 0) {
                container.innerHTML = '<p class="text-muted mb-0">No items selected</p>';
                document.getElementById('totalAmount').value = '0.00';
                return;
            }
            
            let html = '';
            let total = 0;
            
            selectedItems.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">$${item.price.toFixed(2)} each</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${index}, -1)">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeQuantity(${index}, 1)">+</button>
                            <span class="ms-3 fw-bold">$${itemTotal.toFixed(2)}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            document.getElementById('totalAmount').value = total.toFixed(2);
        }

        function changeQuantity(index, change) {
            const item = selectedItems[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                removeItem(index);
            } else if (newQuantity <= item.maxStock) {
                item.quantity = newQuantity;
                updateSelectedItems();
            } else {
                alert('Cannot exceed available stock');
            }
        }

        function removeItem(index) {
            selectedItems.splice(index, 1);
            updateSelectedItems();
        }

        // Handle form submission
        document.getElementById('newSaleForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (selectedItems.length === 0) {
                alert('Please select at least one item');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'process_sale');
            formData.append('items', JSON.stringify(selectedItems));
            formData.append('csrf_token', '<?php echo $_SESSION["csrf_token"]; ?>');
            
            try {
                const response = await fetch('ajax/sales_handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Sale processed successfully!');
                    
                    // Reset form
                    this.reset();
                    selectedItems = [];
                    updateSelectedItems();
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('newSaleModal'));
                    modal.hide();
                    
                    // Reload page to show new sale
                    location.reload();
                } else {
                    alert('Error processing sale: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error processing sale. Please try again.');
            }
        });

        // Load inventory when modal opens
        document.getElementById('newSaleModal').addEventListener('show.bs.modal', function() {
            loadInventoryItems();
        });
    </script>
</body>
</html>

