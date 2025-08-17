<?php

class Security {
    private $conn;
    
    public function __construct($database_connection = null) {
        $this->conn = $database_connection;
    }
    
    // Generate CSRF token
    public function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Validate CSRF token
    public function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Sanitize input
    public function sanitizeInput($input, $type = 'string') {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        if ($type === 'email') {
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
        }
        
        return $input;
    }
    
    // Get client IP
    public function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // Simple rate limiting (in production, use Redis or database)
    public function checkRateLimit($ip, $action, $limit = 5, $window = 300) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = "rate_limit_{$action}_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $window];
        }
        
        $rate_data = $_SESSION[$key];
        
        // Reset if window expired
        if (time() > $rate_data['reset_time']) {
            $_SESSION[$key] = ['count' => 1, 'reset_time' => time() + $window];
            return true;
        }
        
        // Check if limit exceeded
        if ($rate_data['count'] >= $limit) {
            return false;
        }
        
        // Increment counter
        $_SESSION[$key]['count']++;
        return true;
    }
    
    // Log security events (in production, use proper logging)
    public function logSecurityEvent($event_type, $data = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event_type,
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'data' => $data
        ];
        
        // In production, log to file or database
        error_log("Security Event: " . json_encode($log_entry));
        
        return true;
    }
    
    // Password strength validation
    public function validatePasswordStrength($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }
    
    // Account lockout protection
    public function checkAccountLockout($username, $max_attempts = 5, $lockout_duration = 900) { // 15 minutes
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = "lockout_{$username}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'locked_until' => 0];
        }
        
        $lockout_data = $_SESSION[$key];
        
        // Check if account is currently locked
        if ($lockout_data['locked_until'] > time()) {
            $remaining = $lockout_data['locked_until'] - time();
            return [
                'locked' => true,
                'remaining_time' => $remaining,
                'attempts' => $lockout_data['attempts']
            ];
        }
        
        // Reset if lockout period has expired
        if ($lockout_data['locked_until'] > 0 && $lockout_data['locked_until'] <= time()) {
            $_SESSION[$key] = ['attempts' => 0, 'locked_until' => 0];
        }
        
        return [
            'locked' => false,
            'attempts' => $lockout_data['attempts'],
            'remaining_attempts' => max(0, $max_attempts - $lockout_data['attempts'])
        ];
    }
    
    public function recordFailedLogin($username, $max_attempts = 5, $lockout_duration = 900) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = "lockout_{$username}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'locked_until' => 0];
        }
        
        $_SESSION[$key]['attempts']++;
        
        // Lock account if max attempts reached
        if ($_SESSION[$key]['attempts'] >= $max_attempts) {
            $_SESSION[$key]['locked_until'] = time() + $lockout_duration;
            
            $this->logSecurityEvent('ACCOUNT_LOCKED', [
                'username' => $username,
                'attempts' => $_SESSION[$key]['attempts'],
                'locked_until' => date('Y-m-d H:i:s', $_SESSION[$key]['locked_until'])
            ]);
        }
        
        return $_SESSION[$key];
    }
    
    public function resetFailedAttempts($username) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = "lockout_{$username}";
        unset($_SESSION[$key]);
    }
    
    // Session timeout management
    public function checkSessionTimeout($timeout_duration = 1800) { // 30 minutes default
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            $_SESSION['session_start'] = time();
            return true;
        }
        
        $inactive_time = time() - $_SESSION['last_activity'];
        
        if ($inactive_time > $timeout_duration) {
            $this->logSecurityEvent('SESSION_TIMEOUT', [
                'inactive_time' => $inactive_time,
                'session_duration' => time() - $_SESSION['session_start']
            ]);
            
            // Clear session data
            session_unset();
            session_destroy();
            
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function getSessionInfo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['last_activity']) || !isset($_SESSION['session_start'])) {
            return null;
        }
        
        $current_time = time();
        $session_duration = $current_time - $_SESSION['session_start'];
        $inactive_time = $current_time - $_SESSION['last_activity'];
        
        return [
            'session_start' => $_SESSION['session_start'],
            'last_activity' => $_SESSION['last_activity'],
            'session_duration' => $session_duration,
            'inactive_time' => $inactive_time,
            'expires_in' => 1800 - $inactive_time // Default 30 min timeout
        ];
    }
    
    public function extendSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}
?>
