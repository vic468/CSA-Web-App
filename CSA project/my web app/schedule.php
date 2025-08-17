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
    <title>NAZZY's THRIFT SHOP - Staff Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/app-styles.css" rel="stylesheet">
    <style>
        /* Page-specific calendar styles */

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

        .calendar-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .calendar-nav button {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .calendar-nav button:hover {
            transform: scale(1.1);
        }

        .current-month {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--thrift-brown);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e9ecef;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .calendar-day-header {
            background: var(--thrift-brown);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day {
            background: white;
            min-height: 120px;
            padding: 0.5rem;
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-day:hover {
            background: var(--thrift-cream);
        }

        .calendar-day.other-month {
            background: #f8f9fa;
            color: #adb5bd;
        }

        .calendar-day.today {
            background: var(--thrift-cream);
            border: 2px solid var(--thrift-gold);
        }

        .day-number {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .shift-indicator {
            background: var(--thrift-gold);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
            display: block;
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
                        <a class="nav-link active" href="schedule.php"><i class="fas fa-calendar me-1"></i>Schedule</a>
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
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-calendar"></i>
                Staff Schedule
            </h1>
            <p class="page-subtitle">Manage staff schedules, shifts, and time tracking</p>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Staff Members</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Scheduled Shifts</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Hours This Week</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3 class="stats-number">0</h3>
                    <p class="stats-label">Open Shifts</p>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="calendar-card">
            <div class="calendar-header">
                <h3 class="current-month">January 2025</h3>
                <div class="calendar-nav">
                    <button type="button" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" id="todayBtn" class="btn btn-outline-primary">Today</button>
                    <button type="button" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
                
                <!-- Calendar days will be generated by JavaScript -->
                <div id="calendarDays"></div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h3 class="action-title">Create Schedule</h3>
                    <p class="action-description">
                        Create new staff schedules and assign shifts for the upcoming week or month.
                    </p>
                    <button class="btn btn-action">
                        <i class="fas fa-plus me-2"></i>New Schedule
                    </button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="action-title">Time Tracking</h3>
                    <p class="action-description">
                        Track staff clock-in/out times and manage attendance records.
                    </p>
                    <button class="btn btn-action">
                        <i class="fas fa-clock me-2"></i>Time Clock
                    </button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3 class="action-title">Shift Management</h3>
                    <p class="action-description">
                        Manage shift swaps, coverage requests, and schedule changes.
                    </p>
                    <button class="btn btn-action">
                        <i class="fas fa-exchange-alt me-2"></i>Manage Shifts
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State for Schedules -->
        <div class="row">
            <div class="col-12">
                <div class="action-card">
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>No Schedules Created</h3>
                        <p>Start by creating your first staff schedule to manage work shifts and time tracking.</p>
                        <button class="btn btn-action" style="width: auto; margin-top: 1rem;">
                            <i class="fas fa-plus me-2"></i>Create First Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Session Timeout Management -->
    <script src="js/session-timeout.js"></script>
    
    <script>
        // Calendar functionality
        let currentDate = new Date();
        
        function generateCalendar(year, month) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Add empty cells for days before the first day of the month
            for (let i = 0; i < startingDayOfWeek; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day other-month';
                calendarDays.appendChild(emptyDay);
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                
                // Check if it's today
                const today = new Date();
                if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                    dayElement.classList.add('today');
                }
                
                dayElement.innerHTML = `
                    <div class="day-number">${day}</div>
                    <!-- Shifts will be added here dynamically -->
                `;
                
                dayElement.addEventListener('click', function() {
                    alert(`Schedule management for ${month + 1}/${day}/${year} will be implemented soon!`);
                });
                
                calendarDays.appendChild(dayElement);
            }
            
            // Update month display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            document.querySelector('.current-month').textContent = `${monthNames[month]} ${year}`;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
            
            // Navigation buttons
            document.getElementById('prevMonth').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
            });
            
            document.getElementById('nextMonth').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
            });
            
            document.getElementById('todayBtn').addEventListener('click', function() {
                currentDate = new Date();
                generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
            });
            
            // Action buttons
            const actionButtons = document.querySelectorAll('.btn-action');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    alert('This feature will be implemented soon!');
                });
            });
        });
    </script>
</body>
</html>
