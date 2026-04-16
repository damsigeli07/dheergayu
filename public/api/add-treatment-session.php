<?php
// public/api/add-treatment-session.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$plan_id      = intval($_POST['plan_id'] ?? 0);
$session_date = trim($_POST['session_date'] ?? '');
$session_time = trim($_POST['session_time'] ?? '');

if (!$plan_id || !$session_date || !$session_time) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (strtotime($session_date) < strtotime('today')) {
    echo json_encode(['success' => false, 'error' => 'Session date must be today or in the future']);
    exit;
}

$staff_id = (int)$_SESSION['user_id'];

// Verify staff is assigned to this plan
$chk = $conn->prepare("SELECT plan_id, patient_id, assigned_staff_id FROM treatment_plans WHERE plan_id = ? LIMIT 1");
$chk->bind_param('i', $plan_id);
$chk->execute();
$plan = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$plan) {
    echo json_encode(['success' => false, 'error' => 'Plan not found']);
    exit;
}
if ((int)$plan['assigned_staff_id'] !== $staff_id) {
    echo json_encode(['success' => false, 'error' => 'You are not assigned to this plan']);
    exit;
}

// Staff can add a new session only when all existing sessions are completed.
$pending = $conn->prepare("SELECT COUNT(*) AS cnt FROM treatment_sessions WHERE plan_id = ? AND status != 'Completed'");
$pending->bind_param('i', $plan_id);
$pending->execute();
$pendingRow = $pending->get_result()->fetch_assoc();
$pending->close();

$incompleteCount = (int)($pendingRow['cnt'] ?? 0);
if ($incompleteCount > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Cannot add a new session while there are incomplete sessions. Complete current sessions first.'
    ]);
    exit;
}

// Get next session number
$num = $conn->prepare("SELECT MAX(session_number) AS max_num FROM treatment_sessions WHERE plan_id = ?");
$num->bind_param('i', $plan_id);
$num->execute();
$numRow = $num->get_result()->fetch_assoc();
$num->close();
$next_session_number = (int)($numRow['max_num'] ?? 0) + 1;

// Insert new session
$ins = $conn->prepare("INSERT INTO treatment_sessions (plan_id, session_number, session_date, session_time, status) VALUES (?, ?, ?, ?, 'AwaitingPayment')");
$ins->bind_param('iiss', $plan_id, $next_session_number, $session_date, $session_time);
if (!$ins->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to add session: ' . $ins->error]);
    exit;
}
$ins->close();

echo json_encode([
    'success'        => true,
    'session_number' => $next_session_number,
    'session_date'   => $session_date,
    'session_time'   => $session_time,
    'message'        => 'Session ' . $next_session_number . ' added. Patient will be notified to confirm and pay.'
]);
