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
    <title>NAZZY's THRIFT SHOP - Customer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/app-styles.css" rel="stylesheet">
    <style>
        /* Page-specific overrides if needed */

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

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            border: 1px solid #e9ecef;
            height: 100%;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
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

        .action-title {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .action-description {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .btn-action {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
        }

        .btn-action:hover {
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

        .search-bar {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--thrift-gold);
            box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
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
                        <a class="nav-link active" href="customers.php"><i class="fas fa-users me-1"></i>Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php"><i class="fas fa-calendar me-1"></i>Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar me-1"></i>Reports</a>
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
        <div class="content-header">
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Customer Management
            </h1>
            <p class="page-subtitle">Manage customer information, loyalty points, and purchase history</p>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Total Customers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Active This Month</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">$0.00</h3>
                    <p class="stats-label">Total Revenue</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Loyalty Members</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-bar">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search customers by name, email, or phone...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-control">
                        <option value="">All Customers</option>
                        <option value="active">Active Customers</option>
                        <option value="loyalty">Loyalty Members</option>
                        <option value="inactive">Inactive Customers</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus me-2"></i>Add New Customer
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="action-title">Add New Customer</h3>
                    <p class="action-description">
                        Register a new customer with contact information and set up their loyalty account.
                    </p>
                    <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus me-2"></i>Add Customer
                    </button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="action-title">Search Customers</h3>
                    <p class="action-description">
                        Find existing customers by name, phone number, email, or loyalty card number.
                    </p>
                    <button class="btn btn-action">
                        <i class="fas fa-search me-2"></i>Search Database
                    </button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="action-title">Loyalty Program</h3>
                    <p class="action-description">
                        Manage customer loyalty points, rewards, and special promotions.
                    </p>
                    <button class="btn btn-action">
                        <i class="fas fa-star me-2"></i>Manage Loyalty
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer List (Empty State) -->
        <div class="row">
            <div class="col-12">
                <div class="action-card">
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Customers Found</h3>
                        <p>Start building your customer database by adding your first customer.</p>
                        <button class="btn btn-action" style="width: auto; margin-top: 1rem;" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-2"></i>Add Your First Customer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Add New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customerFirstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="customerFirstName" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerLastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="customerLastName" name="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerEmail" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="customerEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerPhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="customerPhone" name="phone" placeholder="(555) 123-4567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customerAddress" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="customerAddress" name="address">
                                </div>
                                <div class="mb-3">
                                    <label for="customerCity" class="form-label">City</label>
                                    <input type="text" class="form-control" id="customerCity" name="city">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customerState" class="form-label">State</label>
                                            <input type="text" class="form-control" id="customerState" name="state" placeholder="CA">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customerZip" class="form-label">ZIP Code</label>
                                            <input type="text" class="form-control" id="customerZip" name="zip_code" placeholder="12345">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="loyaltyMember" name="loyalty_member" value="1">
                                        <label class="form-check-label" for="loyaltyMember">
                                            Enroll in Loyalty Program
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="customerNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="customerNotes" name="notes" rows="3" placeholder="Any additional notes about this customer..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Add Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Session Timeout Management -->
    <script src="js/session-timeout.js"></script>
    
    <script>
        // Customer management functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Customer Management page loaded');
            
            // Handle customer form submission
            document.getElementById('addCustomerForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'add');
                formData.append('csrf_token', '<?php echo $_SESSION["csrf_token"]; ?>');
                
                try {
                    const response = await fetch('ajax/customer_handler.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Customer added successfully!');
                        
                        // Reset form
                        this.reset();
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                        modal.hide();
                        
                        // Reload page to show updated customer count
                        location.reload();
                    } else {
                        alert('Error adding customer: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error adding customer. Please try again.');
                }
            });
            
            // Search functionality placeholder
            const searchInput = document.querySelector('input[placeholder*="Search customers"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    // Future: implement customer search
                    console.log('Searching for:', this.value);
                });
            }
            
            // Filter functionality placeholder
            const filterSelect = document.querySelector('select');
            if (filterSelect) {
                filterSelect.addEventListener('change', function() {
                    // Future: implement customer filtering
                    console.log('Filter changed to:', this.value);
                });
            }
        });
    </script>
</body>
</html>
