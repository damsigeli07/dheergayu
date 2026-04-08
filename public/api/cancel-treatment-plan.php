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

    $staff_id = (int)($_SESSION['user_id'] ?? 0);
    if ($staff_id <= 0) {
        echo 'error';
        exit;
    }

    // Only the staff who has been assigned this plan can cancel it.
    $chk = $db->prepare("SELECT assigned_staff_id FROM treatment_plans WHERE plan_id = ? LIMIT 1");
    $chk->bind_param('i', $plan_id);
    $chk->execute();
    $row = $chk->get_result()->fetch_assoc();
    $chk->close();

    $assignedId = (int)($row['assigned_staff_id'] ?? 0);
    if ($assignedId === 0 || $assignedId !== $staff_id) {
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
