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

// Initialize empty inventory data - will be populated from database
$inventoryItems = [];

// Keep category and condition options for form dropdowns
$categories = ['All', 'Clothing', 'Home Decor', 'Music', 'Accessories', 'Electronics', 'Furniture', 'Books', 'Art'];
$conditions = ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Inventory Management</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        .controls-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
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

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--thrift-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .inventory-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .inventory-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
            border-color: var(--thrift-gold);
        }

        .item-image {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 3rem;
            position: relative;
        }

        .item-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 215, 0, 0.1) 50%, transparent 70%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .item-details {
            padding: 20px;
        }

        .item-name {
            color: var(--thrift-brown);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .item-category {
            background: var(--info-gradient);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .item-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-gradient);
            margin-bottom: 10px;
        }

        .item-stock {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .stock-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .stock-high { background: #28a745; }
        .stock-medium { background: #ffc107; }
        .stock-low { background: #dc3545; }

        .item-condition {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .item-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-edit {
            background: var(--info-gradient);
            border: none;
            color: white;
        }

        .btn-delete {
            background: var(--danger-gradient);
            border: none;
            color: white;
        }

        .btn-view {
            background: var(--secondary-gradient);
            border: none;
            color: white;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--thrift-brown);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .inventory-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
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
                        <a class="nav-link active" href="inventory.php">
                            <i class="fas fa-tags"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php">
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
                <i class="fas fa-tags"></i>
                Inventory Management
            </h1>
            <p class="page-subtitle">
                Manage your thrift shop inventory, track stock levels, and organize your items
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-number"><?php echo count($inventoryItems); ?></div>
                <div class="stat-label">Total Items</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number">$0.00</div>
                <div class="stat-label">Total Value</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Active Categories</div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="searchItem" class="form-label">Search Items</label>
                    <input type="text" class="form-control" id="searchItem" placeholder="Search by name...">
                </div>
                <div class="col-md-2">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="conditionFilter" class="form-label">Condition</label>
                    <select class="form-select" id="conditionFilter">
                        <option value="">All Conditions</option>
                        <?php foreach ($conditions as $condition): ?>
                            <option value="<?php echo $condition; ?>"><?php echo $condition; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="stockFilter" class="form-label">Stock Level</label>
                    <select class="form-select" id="stockFilter">
                        <option value="">All Levels</option>
                        <option value="high">High Stock</option>
                        <option value="medium">Medium Stock</option>
                        <option value="low">Low Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Grid -->
        <div class="inventory-grid">
            <?php if (empty($inventoryItems)): ?>
                <!-- Empty State -->
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-boxes" style="font-size: 4rem; color: #dee2e6;"></i>
                        </div>
                        <h3 class="text-muted mb-3">No Inventory Items</h3>
                        <p class="text-muted mb-4">Start building your inventory by adding your first item.</p>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="fas fa-plus me-2"></i>Add Your First Item
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($inventoryItems as $item): ?>
                <div class="inventory-card">
                    <div class="item-image">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="item-details">
                        <h5 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <span class="item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        
                        <div class="item-stock">
                            <div class="stock-indicator <?php echo $item['stock'] > 10 ? 'stock-high' : ($item['stock'] > 5 ? 'stock-medium' : 'stock-low'); ?>"></div>
                            <span><?php echo $item['stock']; ?> in stock</span>
                        </div>
                        
                        <div class="item-condition">
                            <i class="fas fa-star"></i> Condition: <?php echo htmlspecialchars($item['condition']); ?>
                        </div>
                        
                        <div class="item-actions">
                            <button class="btn btn-sm btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Item
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemName" class="form-label">Item Name</label>
                                    <input type="text" class="form-control" id="itemName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemCategory" class="form-label">Category</label>
                                    <select class="form-select" id="itemCategory" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach (array_slice($categories, 1) as $category): ?>
                                            <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="itemPrice" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="itemPrice" name="price" step="0.01" min="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="itemStock" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="itemStock" name="stock" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="itemCondition" class="form-label">Condition</label>
                                    <select class="form-select" id="itemCondition" name="condition" required>
                                        <option value="">Select Condition</option>
                                        <?php foreach ($conditions as $condition): ?>
                                            <option value="<?php echo $condition; ?>"><?php echo $condition; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="itemDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="itemDescription" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="itemImage" class="form-label">Item Image</label>
                            <input type="file" class="form-control" id="itemImage" name="image" accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addItemBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Add Item</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let inventoryItems = <?php echo json_encode($inventoryItems); ?>;
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchItem');
            const categoryFilter = document.getElementById('categoryFilter');
            const conditionFilter = document.getElementById('conditionFilter');
            const stockFilter = document.getElementById('stockFilter');
            const addItemForm = document.querySelector('#addItemModal form');
            const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));

            // Initialize page
            loadInventoryItems();
            
            // Form submission
            addItemForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleAddItem();
            });

            function filterItems() {
                const inventoryCards = document.querySelectorAll('.inventory-card');
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCategory = categoryFilter.value;
                const selectedCondition = conditionFilter.value;
                const selectedStock = stockFilter.value;

                inventoryCards.forEach(card => {
                    const itemName = card.querySelector('.item-name')?.textContent.toLowerCase() || '';
                    const itemCategory = card.querySelector('.item-category')?.textContent || '';
                    const itemCondition = card.querySelector('.item-condition')?.textContent || '';
                    const stockIndicator = card.querySelector('.stock-indicator');
                    const stockLevel = stockIndicator?.classList.contains('stock-high') ? 'high' : 
                                     stockIndicator?.classList.contains('stock-medium') ? 'medium' : 'low';

                    const matchesSearch = itemName.includes(searchTerm);
                    const matchesCategory = !selectedCategory || itemCategory === selectedCategory;
                    const matchesCondition = !selectedCondition || itemCondition.includes(selectedCondition);
                    const matchesStock = !selectedStock || stockLevel === selectedStock;

                    if (matchesSearch && matchesCategory && matchesCondition && matchesStock) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeIn 0.3s ease-in';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput?.addEventListener('input', filterItems);
            categoryFilter?.addEventListener('change', filterItems);
            conditionFilter?.addEventListener('change', filterItems);
            stockFilter?.addEventListener('change', filterItems);

            // Add CSS for animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `;
            document.head.appendChild(style);
        });
        
        function handleAddItem() {
            const form = document.querySelector('#addItemModal form');
            const formData = new FormData(form);
            const addBtn = document.getElementById('addItemBtn');
            const spinner = addBtn.querySelector('.spinner-border');
            const btnText = addBtn.querySelector('.btn-text');
            
            // Add CSRF token and action
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'add');
            
            // Show loading state
            addBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Adding...';
            
            fetch('ajax/inventory_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
                    loadInventoryItems(); // Reload inventory
                    updateStats(); // Update statistics
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while adding the item');
            })
            .finally(() => {
                // Reset button state
                addBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Add Item';
            });
        }
        
        function loadInventoryItems() {
            fetch('ajax/inventory_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_all&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inventoryItems = data.items || [];
                    renderInventoryItems();
                    updateStats();
                }
            })
            .catch(error => {
                console.error('Error loading inventory:', error);
            });
        }
        
        function renderInventoryItems() {
            const inventoryGrid = document.querySelector('.inventory-grid');
            
            if (inventoryItems.length === 0) {
                inventoryGrid.innerHTML = `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-boxes" style="font-size: 4rem; color: #dee2e6;"></i>
                            </div>
                            <h3 class="text-muted mb-3">No Inventory Items</h3>
                            <p class="text-muted mb-4">Start building your inventory by adding your first item.</p>
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="fas fa-plus me-2"></i>Add Your First Item
                            </button>
                        </div>
                    </div>
                `;
            } else {
                inventoryGrid.innerHTML = inventoryItems.map(item => `
                    <div class="inventory-card" data-item-id="${item.id}">
                        <div class="item-image">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">` : '<i class="fas fa-image"></i>'}
                        </div>
                        <div class="item-details">
                            <h5 class="item-name">${escapeHtml(item.name)}</h5>
                            <span class="item-category">${escapeHtml(item.category)}</span>
                            <div class="item-price">$${parseFloat(item.price).toFixed(2)}</div>
                            
                            <div class="item-stock">
                                <div class="stock-indicator ${getStockClass(item.stock)}"></div>
                                <span>${item.stock} in stock</span>
                            </div>
                            
                            <div class="item-condition">
                                <i class="fas fa-star"></i> Condition: ${escapeHtml(item.condition)}
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn btn-sm btn-view" onclick="viewItem(${item.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-edit" onclick="editItem(${item.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-delete" onclick="deleteItem(${item.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // Add hover effects
                document.querySelectorAll('.inventory-card').forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-8px) scale(1.02)';
                    });
                    
                    card.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0) scale(1)';
                    });
                });
            }
        }
        
        function updateStats() {
            const totalItems = inventoryItems.length;
            const lowStockItems = inventoryItems.filter(item => item.stock <= 5).length;
            const totalValue = inventoryItems.reduce((sum, item) => sum + (item.price * item.stock), 0);
            const activeCategories = [...new Set(inventoryItems.map(item => item.category))].length;
            
            // Update stat cards
            const statCards = document.querySelectorAll('.stat-card');
            if (statCards[0]) statCards[0].querySelector('.stat-number').textContent = totalItems;
            if (statCards[1]) statCards[1].querySelector('.stat-number').textContent = lowStockItems;
            if (statCards[2]) statCards[2].querySelector('.stat-number').textContent = `$${totalValue.toFixed(2)}`;
            if (statCards[3]) statCards[3].querySelector('.stat-number').textContent = activeCategories;
        }
        
        function getStockClass(stock) {
            if (stock > 10) return 'stock-high';
            if (stock > 5) return 'stock-medium';
            return 'stock-low';
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert alert at the top of the main container
            const container = document.querySelector('.main-container');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 5000);
        }
        
        function viewItem(itemId) {
            const item = inventoryItems.find(i => i.id == itemId);
            if (item) {
                alert(`Item Details:\n\nName: ${item.name}\nCategory: ${item.category}\nPrice: $${item.price}\nStock: ${item.stock}\nCondition: ${item.condition}\nDescription: ${item.description || 'No description'}`);
            }
        }
        
        function editItem(itemId) {
            // For now, show a simple alert. Later we can implement a full edit modal
            alert('Edit functionality will be implemented soon!');
        }
        
        function deleteItem(itemId) {
            if (confirm('Are you sure you want to delete this item?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('item_id', itemId);
                formData.append('csrf_token', csrfToken);
                
                fetch('ajax/inventory_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        loadInventoryItems();
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred while deleting the item');
                });
            }
        }
    </script>
</body>
</html>
