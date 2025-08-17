<?php
require_once 'includes/session_check.php';
require_once 'models/Security.php';

$security = new Security();
$csrf_token = $security->generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Reports & Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/app-styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Page-specific chart and report styles */

        .page-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--thrift-gold);
        }

        .page-title {
            color: var(--thrift-brown);
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-subtitle {
            color: #6c757d;
            margin: 0.5rem 0 0 0;
        }

        .report-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            border: 1px solid #e9ecef;
            height: 100%;
            margin-bottom: 2rem;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .report-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .report-title {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .report-description {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .btn-report {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
        }

        .btn-report:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 27, 105, 0.3);
            color: white;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--thrift-gold);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--thrift-brown);
            margin: 0;
        }

        .stats-label {
            color: #6c757d;
            font-weight: 500;
            margin: 0.5rem 0 0 0;
        }

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .chart-title {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }

        .filter-bar {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--thrift-gold);
            box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
        }

        .empty-chart {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            color: #6c757d;
            flex-direction: column;
        }

        .empty-chart i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .report-category {
            border-left: 4px solid var(--thrift-gold);
            padding-left: 1rem;
            margin-bottom: 2rem;
        }

        .report-category h3 {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .report-category p {
            color: #6c757d;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-store me-2"></i>NAZZY's THRIFT SHOP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php"><i class="fas fa-boxes me-1"></i>Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php"><i class="fas fa-cash-register me-1"></i>Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php"><i class="fas fa-users me-1"></i>Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php"><i class="fas fa-calendar me-1"></i>Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php"><i class="fas fa-chart-bar me-1"></i>Reports</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i>
                Reports & Analytics
            </h1>
            <p class="page-subtitle">Comprehensive business insights and performance analytics</p>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-select">
                        <option value="all">All Reports</option>
                        <option value="sales">Sales Reports</option>
                        <option value="inventory">Inventory Reports</option>
                        <option value="customer">Customer Reports</option>
                        <option value="staff">Staff Reports</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format</label>
                    <select class="form-select">
                        <option value="view">View Online</option>
                        <option value="pdf">Export PDF</option>
                        <option value="excel">Export Excel</option>
                        <option value="csv">Export CSV</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-report d-block">
                        <i class="fas fa-search me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">$0.00</h3>
                    <p class="stats-label">Total Revenue</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Total Sales</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Items Sold</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">$0.00</h3>
                    <p class="stats-label">Avg. Sale Value</p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <h3 class="chart-title">Sales Trend</h3>
                    <div class="empty-chart">
                        <i class="fas fa-chart-line"></i>
                        <h5>No Sales Data Available</h5>
                        <p>Sales trend chart will appear here once you have transaction data.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h3 class="chart-title">Top Categories</h3>
                    <div class="empty-chart">
                        <i class="fas fa-chart-pie"></i>
                        <h5>No Category Data</h5>
                        <p>Category breakdown will show here.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Reports Section -->
        <div class="report-category">
            <h3>Sales Reports</h3>
            <p>Analyze sales performance, trends, and revenue metrics</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="report-title">Daily Sales Report</h3>
                    <p class="report-description">
                        View daily sales performance, transaction counts, and revenue breakdown by payment method.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <h3 class="report-title">Weekly Sales Summary</h3>
                    <p class="report-description">
                        Weekly sales trends, peak hours analysis, and staff performance comparison.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="report-title">Monthly Revenue</h3>
                    <p class="report-description">
                        Comprehensive monthly revenue analysis with year-over-year comparisons.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Reports Section -->
        <div class="report-category">
            <h3>Inventory Reports</h3>
            <p>Track inventory levels, turnover rates, and stock performance</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="report-title">Stock Levels</h3>
                    <p class="report-description">
                        Current inventory levels, low stock alerts, and reorder recommendations.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="report-title">Top Selling Items</h3>
                    <p class="report-description">
                        Best performing products by sales volume, revenue, and profit margins.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="report-title">Inventory Turnover</h3>
                    <p class="report-description">
                        Inventory turnover rates, slow-moving items, and optimization suggestions.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Reports Section -->
        <div class="report-category">
            <h3>Customer Reports</h3>
            <p>Understand customer behavior, loyalty, and purchasing patterns</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="report-title">Customer Analytics</h3>
                    <p class="report-description">
                        Customer demographics, purchase frequency, and lifetime value analysis.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="report-title">Loyalty Program</h3>
                    <p class="report-description">
                        Loyalty program performance, points redemption, and member engagement.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="report-title">Purchase Patterns</h3>
                    <p class="report-description">
                        Customer purchase patterns, seasonal trends, and product preferences.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Staff Reports Section -->
        <div class="report-category">
            <h3>Staff Reports</h3>
            <p>Monitor staff performance, schedules, and productivity metrics</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="report-title">Staff Performance</h3>
                    <p class="report-description">
                        Individual staff sales performance, customer service metrics, and productivity.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="report-title">Time & Attendance</h3>
                    <p class="report-description">
                        Staff attendance records, hours worked, and schedule adherence tracking.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="report-title">Payroll Summary</h3>
                    <p class="report-description">
                        Payroll calculations, overtime tracking, and labor cost analysis.
                    </p>
                    <button class="btn btn-report">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Session Timeout Management -->
    <script src="js/session-timeout.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Reports page loaded');
            
            // Add event listeners for report buttons
            const reportButtons = document.querySelectorAll('.btn-report');
            reportButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportTitle = this.closest('.report-card')?.querySelector('.report-title')?.textContent || 'Report';
                    alert(`${reportTitle} generation will be implemented soon!`);
                });
            });
            
            // Date range change handler
            const dateRangeSelect = document.querySelector('select');
            if (dateRangeSelect) {
                dateRangeSelect.addEventListener('change', function() {
                    console.log('Date range changed to:', this.value);
                    // Future: Update charts and data based on selected range
                });
            }
        });
    </script>
</body>
</html>
