<?php
// public/api/update-session-date.php  — patient reschedules an AwaitingPayment session
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$plan_id        = intval($_POST['plan_id'] ?? 0);
$session_number = intval($_POST['session_number'] ?? 0);
$session_date   = trim($_POST['session_date'] ?? '');
$session_time   = trim($_POST['session_time'] ?? '');
$patient_id     = (int)$_SESSION['user_id'];

if (!$plan_id || !$session_number || !$session_date || !$session_time) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (strtotime($session_date) < strtotime('today')) {
    echo json_encode(['success' => false, 'error' => 'Date must be today or in the future']);
    exit;
}

// Verify patient owns this plan
$chk = $conn->prepare("SELECT plan_id FROM treatment_plans WHERE plan_id = ? AND patient_id = ? LIMIT 1");
$chk->bind_param('ii', $plan_id, $patient_id);
$chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'error' => 'Plan not found']);
    exit;
}
$chk->close();

// Only allow rescheduling AwaitingPayment sessions
$upd = $conn->prepare("UPDATE treatment_sessions SET session_date = ?, session_time = ? WHERE plan_id = ? AND session_number = ? AND status = 'AwaitingPayment' LIMIT 1");
$upd->bind_param('ssii', $session_date, $session_time, $plan_id, $session_number);
$upd->execute();
if ($upd->affected_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Session not found or cannot be rescheduled']);
    exit;
}
$upd->close();

echo json_encode(['success' => true, 'message' => 'Session date updated. You can now proceed to pay.']);
