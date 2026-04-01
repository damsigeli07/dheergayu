<?php
// public/api/cancel-treatment-plan.php
header('Content-Type: text/plain');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo 'error';
    exit;
}

// Check if user is staff
$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if ($user_role !== 'staff') {
    echo 'error';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = intval($_POST['plan_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($plan_id === 0 || empty($reason)) {
        echo 'error';
        exit;
    }
    
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    
    if ($db->connect_error) {
        echo 'error';
        exit;
    }
    
    // Update treatment plan status to Cancelled
    $stmt = $db->prepare("UPDATE treatment_plans SET status = 'Cancelled' WHERE plan_id = ?");
    $stmt->bind_param('i', $plan_id);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    
    $stmt->close();
    $db->close();
} else {
    echo 'error';
}
