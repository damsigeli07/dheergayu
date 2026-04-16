<?php
// Staff confirm or decline a treatment plan assignment (offered after patient confirms).
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? '');
if ($user_role !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Staff only']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$staff_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';  // 'confirm' or 'decline'
$offer_id = (int)($_POST['offer_id'] ?? 0);

if (!$offer_id || !in_array($action, ['confirm', 'decline'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS treatment_plan_staff_offer (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    treatment_id INT NOT NULL,
    primary_staff1_id INT NOT NULL,
    primary_staff2_id INT NOT NULL,
    assigned_staff_id INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    UNIQUE KEY one_offer_per_plan (plan_id),
    INDEX idx_offer_staff (primary_staff1_id, primary_staff2_id),
    INDEX idx_offer_status (status)
)");

$stmt = $conn->prepare("SELECT * FROM treatment_plan_staff_offer WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $offer_id);
$stmt->execute();
$offer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$offer) {
    echo json_encode(['success' => false, 'message' => 'Assignment not found']);
    exit;
}

if ($offer['status'] !== 'Pending' || $offer['assigned_staff_id'] !== null) {
    echo json_encode(['success' => false, 'message' => 'This assignment is already taken']);
    exit;
}

$is_primary1 = (int)$offer['primary_staff1_id'] === $staff_id;
$is_primary2 = (int)$offer['primary_staff2_id'] === $staff_id;

if (!$is_primary1 && !$is_primary2) {
    echo json_encode(['success' => false, 'message' => 'You are not assigned to this treatment']);
    exit;
}

if ($action === 'decline') {
    $conn->query("UPDATE treatment_plan_staff_offer SET status = 'Pending' WHERE id = " . (int)$offer_id);
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['success' => true, 'message' => 'You have declined this assignment']);
    exit;
}

// Check for session time conflicts with treatments already assigned to this staff
$conflict_stmt = $conn->prepare("
    SELECT ts_offer.session_date, ts_offer.session_time
    FROM treatment_sessions ts_offer
    JOIN treatment_sessions ts_assigned
        ON ts_assigned.session_date = ts_offer.session_date
        AND ts_assigned.session_time = ts_offer.session_time
    JOIN treatment_plans tp
        ON ts_assigned.plan_id = tp.plan_id
        AND tp.assigned_staff_id = ?
        AND tp.plan_id != ?
    WHERE ts_offer.plan_id = ?
    LIMIT 1
");
$conflict_stmt->bind_param('iii', $staff_id, $offer['plan_id'], $offer['plan_id']);
$conflict_stmt->execute();
$conflict = $conflict_stmt->get_result()->fetch_assoc();
$conflict_stmt->close();

if ($conflict) {
    $conflict_date = date('M j, Y', strtotime($conflict['session_date']));
    $conflict_time = date('g:i A', strtotime($conflict['session_time']));
    echo json_encode([
        'success' => false,
        'message' => "You are already assigned to another treatment on {$conflict_date} at {$conflict_time}. You cannot take this assignment."
    ]);
    exit;
}

$upd = $conn->prepare("UPDATE treatment_plan_staff_offer SET assigned_staff_id = ?, status = 'StaffConfirmed', confirmed_at = NOW() WHERE id = ?");
$upd->bind_param('ii', $staff_id, $offer_id);
$upd->execute();
$upd->close();

// Store assigned_staff_id on treatment_plans for easy lookup
$conn->query("UPDATE treatment_plans SET assigned_staff_id = " . (int)$staff_id . " WHERE plan_id = " . (int)$offer['plan_id']);

if (ob_get_length()) { ob_clean(); }
echo json_encode(['success' => true, 'message' => 'You have confirmed this treatment assignment']);
