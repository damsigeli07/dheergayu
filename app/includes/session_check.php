<?php
// app/includes/session_check.php
// Include this at the top of all protected pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Store the intended destination
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}

// Optional: Check if user has the required role for this page
function checkRole($allowed_roles = []) {
    if (empty($allowed_roles)) {
        return true; // No role restriction
    }
    
    // Normalize the user's role
    $user_role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
    
    // Also check alternative session variables
    if (empty($user_role)) {
        $user_role = isset($_SESSION['user_type']) ? strtolower(trim($_SESSION['user_type'])) : '';
    }
    if (empty($user_role)) {
        $user_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
    }
    
    // Normalize allowed roles
    $allowed_roles = array_map('strtolower', $allowed_roles);
    
    // Debug logging (remove in production)
    error_log("Session check - User role: '$user_role', Allowed: " . implode(', ', $allowed_roles));
    error_log("Session data: " . print_r($_SESSION, true));
    
    if (!in_array($user_role, $allowed_roles)) {
        // User doesn't have permission
        http_response_code(403);
        
        // Show a more informative error page
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; text-align: center; }
        .error-box { max-width: 500px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; border-radius: 10px; }
        .error-title { color: #d9534f; font-size: 24px; margin-bottom: 20px; }
        .error-details { color: #666; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #5cb85c; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-title">Access Denied</div>
        <div class="error-details">
            You do not have permission to view this page.<br>
            Required role: ' . implode(' or ', $allowed_roles) . '<br>
            Your role: ' . ($user_role ?: 'not set') . '
        </div>
        <a href="/dheergayu/app/Views/Patient/login.php" class="btn">Login</a>
        <a href="/dheergayu/app/Views/Patient/home.php" class="btn">Home</a>
    </div>
</body>
</html>';
        exit;
    }
    
    return true;
}
?>