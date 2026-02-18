<?php
// Staff confirm or decline a treatment plan assignment (offered after patient confirms).
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
    backup_staff_id INT NOT NULL,
    assigned_staff_id INT NULL,
    primary1_declined TINYINT(1) NOT NULL DEFAULT 0,
    primary2_declined TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    UNIQUE KEY one_offer_per_plan (plan_id),
    INDEX idx_offer_staff (primary_staff1_id, primary_staff2_id, backup_staff_id),
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
$is_backup   = (int)$offer['backup_staff_id'] === $staff_id;

if (!$is_primary1 && !$is_primary2 && !$is_backup) {
    echo json_encode(['success' => false, 'message' => 'You are not assigned to this treatment']);
    exit;
}

if ($action === 'decline') {
    if ($is_primary1) {
        $conn->query("UPDATE treatment_plan_staff_offer SET primary1_declined = 1 WHERE id = " . (int)$offer_id);
    } elseif ($is_primary2) {
        $conn->query("UPDATE treatment_plan_staff_offer SET primary2_declined = 1 WHERE id = " . (int)$offer_id);
    }
    echo json_encode(['success' => true, 'message' => 'You have declined this assignment']);
    exit;
}

// action === 'confirm'
if ($is_backup) {
    $p1 = (int)$offer['primary1_declined'];
    $p2 = (int)$offer['primary2_declined'];
    if (!$p1 || !$p2) {
        echo json_encode(['success' => false, 'message' => 'Backup can only confirm after both primary staff have declined']);
        exit;
    }
}

$upd = $conn->prepare("UPDATE treatment_plan_staff_offer SET assigned_staff_id = ?, status = 'StaffConfirmed', confirmed_at = NOW() WHERE id = ?");
$upd->bind_param('ii', $staff_id, $offer_id);
$upd->execute();
$upd->close();

// Optionally store assigned_staff_id on treatment_plans for easy lookup
$chk = $conn->query("SHOW COLUMNS FROM treatment_plans LIKE 'assigned_staff_id'");
if ($chk && $chk->num_rows === 0) {
    $conn->query("ALTER TABLE treatment_plans ADD COLUMN assigned_staff_id INT NULL AFTER status");
}
$conn->query("UPDATE treatment_plans SET assigned_staff_id = " . (int)$staff_id . " WHERE plan_id = " . (int)$offer['plan_id']);

echo json_encode(['success' => true, 'message' => 'You have confirmed this treatment assignment']);
