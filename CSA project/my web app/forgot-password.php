<?php
session_start();

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send a password reset email here
        // For demo purposes, we'll just show a success message
        $message = 'If an account with that email exists, you will receive password reset instructions shortly.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAZZY's THRIFT SHOP - Forgot Password</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow-heavy);
            border: 3px solid var(--thrift-gold);
            max-width: 500px;
            width: 100%;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-title {
            color: var(--thrift-brown);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .forgot-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--thrift-brown);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: var(--thrift-cream);
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: var(--transition);
            color: var(--thrift-brown);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--thrift-gold);
            box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
            background: white;
        }

        .btn-reset {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
            margin-bottom: 20px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
            color: white;
        }

        .back-to-login {
            text-align: center;
        }

        .back-to-login a {
            color: var(--thrift-gold);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .back-to-login a:hover {
            color: var(--thrift-brown);
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .store-icon {
            color: var(--thrift-gold);
            font-size: 3rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <div class="store-icon">
                <i class="fas fa-store"></i>
            </div>
            <h1 class="forgot-title">Reset Password</h1>
            <p class="forgot-subtitle">Enter your email to receive password reset instructions</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>Email Address
                </label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your email address" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-reset">
                <i class="fas fa-paper-plane me-2"></i>
                Send Reset Instructions
            </button>
        </form>

        <div class="back-to-login">
            <a href="login.php">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
