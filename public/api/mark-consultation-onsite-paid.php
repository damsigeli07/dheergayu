<?php
/**
 * Staff records cash/onsite payment for a consultation so the doctor can start
 * after payment_status is Completed (online PayHere also sets this).
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $user_role !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$consultation_id = (int)($_POST['consultation_id'] ?? $_GET['consultation_id'] ?? 0);
if ($consultation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid consultation id']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$stmt = $conn->prepare("
    UPDATE consultations
    SET payment_status = 'Completed',
        payment_method = IFNULL(NULLIF(TRIM(payment_method), ''), 'onsite'),
        updated_at = NOW()
    WHERE id = ?
      AND treatment_type = 'General Consultation'
      AND (payment_status IS NULL OR payment_status != 'Completed')
");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('i', $consultation_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    $chk = $conn->prepare("SELECT id, payment_status FROM consultations WHERE id = ? LIMIT 1");
    $chk->bind_param('i', $consultation_id);
    $chk->execute();
    $row = $chk->get_result()->fetch_assoc();
    $chk->close();
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Consultation not found']);
        exit;
    }
    echo json_encode(['success' => true, 'message' => 'Already marked as paid', 'already_done' => true]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Payment recorded']);
