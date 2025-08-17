<?php
require_once 'models/User.php';
require_once 'models/Security.php';

// Initialize user model and security
$user = new User();
$security = new Security();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request detected.';
        $security->logSecurityEvent('CSRF_ATTEMPT', [
            'ip' => $security->getClientIP(),
            'form' => 'register'
        ]);
    } else {
        // Rate limiting check
        $clientIP = $security->getClientIP();
        if (!$security->checkRateLimit($clientIP, 'register')) {
            $error = 'Too many registration attempts. Please try again later.';
        } else {
            // Get and sanitize form data
            $username = $security->sanitizeInput($_POST['username'] ?? '');
            $email = $security->sanitizeInput($_POST['email'] ?? '', 'email');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $error = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
            } else {
                // Attempt to create user
                if ($user->create($username, $email, $password)) {
                    $message = 'Registration successful! You can now login.';
                    $security->logSecurityEvent('REGISTRATION_SUCCESS', [
                        'username' => $username,
                        'email' => $email,
                        'ip' => $clientIP
                    ]);
                } else {
                    $error = 'Registration failed. Username or email may already exist.';
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = $security->generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Staff Registration</title>
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
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(45, 27, 105, 0.37);
            --shadow-heavy: 0 20px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --thrift-brown: #2D1B69;
            --thrift-gold: #9C27B0;
            --thrift-orange: #7B1FA2;
            --thrift-cream: #F3E5F5;
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(156, 39, 176, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(123, 31, 162, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(45, 27, 105, 0.1) 0%, transparent 50%);
        }

        /* Animated background elements */
        body::before,
        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: var(--secondary-gradient);
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            z-index: -1;
        }

        body::before {
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        body::after {
            bottom: -150px;
            right: -150px;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .registration-container {
            width: 100%;
            max-width: 550px;
            background: var(--thrift-cream);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            backdrop-filter: blur(20px);
            border: 3px solid var(--thrift-gold);
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            background: var(--primary-gradient);
            padding: 40px 30px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--thrift-gold);
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .security-badges {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .security-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .security-badge:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.3);
        }

        .form-section {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .form-label i {
            margin-right: 8px;
            color: #667eea;
            width: 16px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: #f8fafc;
            color: #2d3748;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--thrift-brown);
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
            transform: translateY(-1px);
        }

        .form-control.error {
            border-color: #e53e3e;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        .form-control.success {
            border-color: #38a169;
            box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.1);
        }

        .form-text {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }

        .form-text i {
            margin-right: 5px;
            font-size: 0.7rem;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            padding: 5px;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--thrift-brown);
        }

        .password-strength-container {
            margin-top: 10px;
        }

        .password-strength-bar {
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: var(--transition);
            border-radius: 3px;
        }

        .strength-weak { background: var(--danger-gradient); }
        .strength-medium { background: var(--warning-gradient); }
        .strength-strong { background: var(--success-gradient); }

        .password-requirements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .requirement {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            color: #718096;
            transition: var(--transition);
        }

        .requirement.met {
            color: #38a169;
        }

        .requirement i {
            margin-right: 6px;
            font-size: 0.7rem;
        }

        .btn-register {
            width: 100%;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 16px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #742a2a;
        }

        .security-features {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }

        .security-features h6 {
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .security-features h6 i {
            margin-right: 8px;
            color: #38a169;
        }

        .security-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .security-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #4a5568;
        }

        .security-item i {
            margin-right: 8px;
            color: #38a169;
            font-size: 0.8rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .registration-container {
                margin: 10px;
                max-width: none;
            }
            
            .header-section,
            .form-section {
                padding: 30px 20px;
            }
            
            .security-badges {
                flex-direction: column;
                align-items: center;
            }
            
            .password-requirements {
                grid-template-columns: 1fr;
            }
            
            .security-list {
                grid-template-columns: 1fr;
            }
        }

        /* Loading animation */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-content">
                <div class="logo-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h1>NAZZY's THRIFT SHOP</h1>
                <p>Join Our Team - Staff Registration</p>
                <div class="security-badges">
                    <div class="security-badge">
                        <i class="fas fa-tags"></i>
                        Inventory
                    </div>
                    <div class="security-badge">
                        <i class="fas fa-chart-line"></i>
                        Sales
                    </div>
                    <div class="security-badge">
                        <i class="fas fa-users"></i>
                        Customers
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <?php if ($message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registrationForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>Staff ID
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required maxlength="50" pattern="[a-zA-Z0-9_]{3,50}">
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i>
                        Create a unique staff ID (3-50 characters, letters, numbers, and underscores only)
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>Work Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i>
                        Your work email for store communications
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-key"></i>Password
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password" 
                               required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    
                    <div class="password-strength-container">
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="passwordStrengthFill"></div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-circle"></i>At least 8 characters
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="fas fa-circle"></i>One lowercase letter
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="fas fa-circle"></i>One uppercase letter
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-circle"></i>One number
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="fas fa-circle"></i>One special character
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-check-circle"></i>Confirm Password
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm-password-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-register" id="registerBtn">
                    <i class="fas fa-user-plus me-2"></i>
                    Join Our Team
                </button>
            </form>

            <div class="login-link">
                <p>Already a team member? 
                    <a href="login.php">Sign in here</a>
                </p>
            </div>

            <div class="security-features">
                <h6>
                    <i class="fas fa-store"></i>
                    Store Features
                </h6>
                <div class="security-list">
                    <div class="security-item">
                        <i class="fas fa-check"></i>Inventory Management
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check"></i>Sales Tracking
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check"></i>Customer Database
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check"></i>Staff Scheduling
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check"></i>Financial Reports
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check"></i>Marketing Tools
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId === 'password' ? 'password-eye' : 'confirm-password-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        }

        // Enhanced password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('passwordStrengthFill');
            
            // Check requirements
            const requirements = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(`req-${req}`);
                if (requirements[req]) {
                    element.classList.add('met');
                    element.querySelector('i').className = 'fas fa-check';
                } else {
                    element.classList.remove('met');
                    element.querySelector('i').className = 'fas fa-circle';
                }
            });
            
            // Calculate strength percentage
            const metCount = Object.values(requirements).filter(Boolean).length;
            const strengthPercentage = (metCount / 5) * 100;
            
            // Update strength bar
            strengthFill.style.width = strengthPercentage + '%';
            strengthFill.className = 'password-strength-fill';
            
            if (strengthPercentage <= 40) {
                strengthFill.classList.add('strength-weak');
            } else if (strengthPercentage <= 80) {
                strengthFill.classList.add('strength-medium');
            } else {
                strengthFill.classList.add('strength-strong');
            }
        });

        // Real-time form validation
        const form = document.getElementById('registrationForm');
        const inputs = form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
        });

        function validateField(e) {
            const field = e.target;
            const value = field.value.trim();
            
            // Remove existing error/success classes
            field.classList.remove('error', 'success');
            
            if (!value) {
                field.classList.add('error');
                return false;
            }
            
            // Field-specific validation
            let isValid = true;
            
            switch(field.id) {
                case 'username':
                    isValid = /^[a-zA-Z0-9_]{3,50}$/.test(value);
                    break;
                case 'email':
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    break;
                case 'password':
                    isValid = value.length >= 8 && 
                             /[a-z]/.test(value) && 
                             /[A-Z]/.test(value) && 
                             /[0-9]/.test(value) && 
                             /[^A-Za-z0-9]/.test(value);
                    break;
                case 'confirm_password':
                    const password = document.getElementById('password').value;
                    isValid = value === password && value.length > 0;
                    break;
            }
            
            if (isValid) {
                field.classList.add('success');
            } else {
                field.classList.add('error');
            }
            
            return isValid;
        }

        function clearFieldError(e) {
            const field = e.target;
            field.classList.remove('error');
        }

        // Form submission with enhanced validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField({ target: input })) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                showNotification('Please fix the errors above', 'error');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('registerBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '';
            
            // Submit form after a brief delay to show loading animation
            setTimeout(() => {
                form.submit();
            }, 1000);
        });

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
                ${message}
            `;
            
            const formSection = document.querySelector('.form-section');
            formSection.insertBefore(notification, formSection.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Add smooth animations to form elements
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe form groups for animation
        document.querySelectorAll('.form-group').forEach((group, index) => {
            group.style.opacity = '0';
            group.style.transform = 'translateY(20px)';
            group.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            observer.observe(group);
        });

        // Add hover effects to security badges
        document.querySelectorAll('.security-badge').forEach(badge => {
            badge.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.05)';
            });
            
            badge.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
