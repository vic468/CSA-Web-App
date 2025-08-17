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

// Initialize empty data arrays - will be populated from database
$shopStats = [
    'total_sales' => 0.00,
    'items_sold' => 0,
    'inventory_count' => 0,
    'customers_today' => 0,
    'pending_orders' => 0,
    'low_stock_items' => 0,
    'top_category' => 'No data yet',
    'best_seller' => 'No data yet'
];

$recentSales = [];

$inventoryAlerts = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Management Dashboard</title>
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

        .welcome-section {
            background: var(--thrift-cream);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            border: 3px solid var(--thrift-gold);
            box-shadow: var(--shadow-heavy);
        }

        .welcome-title {
            color: var(--thrift-brown);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
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
            border: 2px solid transparent;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
            border-color: var(--thrift-gold);
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
            background: var(--primary-gradient);
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

        .sales-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }

        .sales-item:hover {
            background: #f8f9fa;
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .sales-item:last-child {
            border-bottom: none;
        }

        .item-info h6 {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .item-time {
            color: #666;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--success-gradient);
            font-size: 1.1rem;
        }

        .alert-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1rem;
        }

        .alert-icon.critical {
            background: var(--danger-gradient);
            color: white;
        }

        .alert-icon.low {
            background: var(--warning-gradient);
            color: white;
        }

        .alert-info h6 {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .alert-stock {
            color: #666;
            font-size: 0.9rem;
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

        .action-btn.inventory {
            background: var(--info-gradient);
        }

        .action-btn.customers {
            background: var(--secondary-gradient);
        }

        .action-btn.reports {
            background: var(--warning-gradient);
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

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-top: 20px;
            box-shadow: var(--shadow-light);
            border: 2px solid var(--thrift-gold);
        }

        .chart-placeholder {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: 500;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">
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
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">
                <i class="fas fa-sun"></i>
                Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($userData['username'] ?? 'Staff'); ?>!
            </h1>
            <p class="welcome-subtitle">
                Here's what's happening at NAZZY's THRIFT SHOP today
            </p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($shopStats['total_sales'], 2); ?></div>
                <div class="stat-label">Total Sales Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo $shopStats['items_sold']; ?></div>
                <div class="stat-label">Items Sold Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value"><?php echo $shopStats['inventory_count']; ?></div>
                <div class="stat-label">Items in Inventory</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $shopStats['customers_today']; ?></div>
                <div class="stat-label">Customers Today</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
                <h3 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Recent Sales Activity
                </h3>
                
                <?php if (empty($recentSales)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h5>No Recent Sales</h5>
                    <p>Sales activity will appear here once you start making transactions.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($recentSales as $sale): ?>
                    <div class="sales-item">
                        <div class="item-info">
                            <h6><?php echo htmlspecialchars($sale['item']); ?></h6>
                            <div class="item-time"><?php echo $sale['time']; ?></div>
                        </div>
                        <div class="item-price">$<?php echo number_format($sale['price'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Chart Placeholder -->
                <div class="chart-container">
                    <h4 class="section-title">
                        <i class="fas fa-chart-area"></i>
                        Sales Trend (Last 7 Days)
                    </h4>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-line me-2"></i>
                        Interactive Chart Coming Soon
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <h3 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Inventory Alerts
                </h3>
                
                <?php if (empty($inventoryAlerts)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h5>No Inventory Alerts</h5>
                    <p>All inventory levels are healthy. Alerts will appear here when items are running low.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($inventoryAlerts as $alert): ?>
                    <div class="alert-item">
                        <div class="alert-icon <?php echo $alert['status']; ?>">
                            <i class="fas fa-<?php echo $alert['status'] === 'critical' ? 'exclamation' : 'info'; ?>-circle"></i>
                        </div>
                        <div class="alert-info">
                            <h6><?php echo htmlspecialchars($alert['item']); ?></h6>
                            <div class="alert-stock">Only <?php echo $alert['stock']; ?> left in stock</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <h3 class="section-title mt-4">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h3>
                
                <div class="quick-actions">
                    <a href="sales.php" class="action-btn sales">
                        <i class="fas fa-cash-register"></i>
                        New Sale
                    </a>
                    <a href="inventory.php" class="action-btn inventory">
                        <i class="fas fa-plus"></i>
                        Add Item
                    </a>
                    <a href="customers.php" class="action-btn customers">
                        <i class="fas fa-user-plus"></i>
                        New Customer
                    </a>
                    <a href="reports.php" class="action-btn reports">
                        <i class="fas fa-file-alt"></i>
                        Generate Report
                    </a>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="text-center mb-2">
                        <i class="fas fa-star text-warning"></i>
                        Today's Highlights
                    </h6>
                    <div class="text-center">
                        <div class="text-muted">Top Category: <?php echo $shopStats['top_category']; ?></div>
                        <div class="text-muted">Best Seller: <?php echo $shopStats['best_seller']; ?></div>
                    </div>
                </div>
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

            // Add hover effects to sales items
            const salesItems = document.querySelectorAll('.sales-item');
            salesItems.forEach(item => {
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
    </script>
</body>
</html>
