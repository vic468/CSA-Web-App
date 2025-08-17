<?php
// Session timeout check - Include this file in all protected pages
require_once __DIR__ . '/../models/Security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize security class
$security = new Security();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not authenticated
    header('Location: login.php?error=session_required');
    exit();
}

// Check session timeout
if (!$security->checkSessionTimeout()) {
    // Session has expired, redirect to login
    header('Location: login.php?error=session_expired');
    exit();
}

// Optional: Warn user when session is about to expire (5 minutes remaining)
$session_info = $security->getSessionInfo();
if ($session_info && $session_info['expires_in'] <= 300) { // 5 minutes
    $expires_in_minutes = ceil($session_info['expires_in'] / 60);
    $_SESSION['session_warning'] = "Your session will expire in {$expires_in_minutes} minute(s). Please save your work.";
}
?>
