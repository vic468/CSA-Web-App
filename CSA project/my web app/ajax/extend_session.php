<?php
header('Content-Type: application/json');
require_once '../models/Security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

try {
    $security = new Security();
    
    // Extend the session
    $extended = $security->extendSession();
    
    if ($extended) {
        $session_info = $security->getSessionInfo();
        
        echo json_encode([
            'success' => true,
            'message' => 'Session extended successfully',
            'expires_in' => $session_info['expires_in'],
            'session_duration' => $session_info['session_duration']
        ]);
        
        // Log session extension
        $security->logSecurityEvent('SESSION_EXTENDED', [
            'user_id' => $_SESSION['user_id'],
            'session_duration' => $session_info['session_duration']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to extend session']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    
    error_log("Session extension error: " . $e->getMessage());
}
?>
