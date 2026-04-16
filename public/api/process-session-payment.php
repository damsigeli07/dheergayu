<?php
// public/api/process-session-payment.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/payhere_config.php';

$isSimulate = (($_POST['action'] ?? '') === 'simulate');
if ($isSimulate && !payhere_test_payment_allowed()) {
    echo json_encode(['success' => false, 'error' => 'Test payment is disabled']);
    exit;
}

$planId        = (int)($_POST['plan_id'] ?? 0);
$sessionNumber = (int)($_POST['session_number'] ?? 0);
$orderId       = trim($_POST['order_id'] ?? '');
$paymentId     = trim($_POST['payment_id'] ?? '');
$amount        = (float)($_POST['amount'] ?? 0);
$customerName  = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$isPending     = isset($_POST['pending']);
$userId        = (int)($_SESSION['user_id'] ?? 0);

if (!$planId || !$sessionNumber || !$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if ($isPending) {
    $_SESSION['sp_pay_order_id']     = $orderId;
    $_SESSION['sp_pay_plan_id']      = $planId;
    $_SESSION['sp_pay_session_num']  = $sessionNumber;
    $_SESSION['sp_pay_amount']       = $amount;
    $_SESSION['sp_pay_name']         = $customerName;
    $_SESSION['sp_pay_email']        = $customerEmail;
    $_SESSION['sp_pay_phone']        = $customerPhone;
    echo json_encode(['success' => true]);
    exit;
}

try {
    $conn->begin_transaction();

    // Verify ownership
    $chk = $conn->prepare("SELECT plan_id FROM treatment_plans WHERE plan_id = ? AND patient_id = ? LIMIT 1");
    $chk->bind_param('ii', $planId, $userId);
    $chk->execute();
    if (!$chk->get_result()->fetch_assoc()) {
        throw new Exception('Plan not found');
    }
    $chk->close();

    // Check session is still awaiting payment
    $sc = $conn->prepare("SELECT session_number, status FROM treatment_sessions WHERE plan_id = ? AND session_number = ? LIMIT 1");
    $sc->bind_param('ii', $planId, $sessionNumber);
    $sc->execute();
    $sess = $sc->get_result()->fetch_assoc();
    $sc->close();

    if (!$sess) {
        throw new Exception('Session not found');
    }
    if ($sess['status'] === 'Confirmed') {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Already paid', 'already_done' => true]);
        exit;
    }

    // Mark session as Confirmed
    $upd = $conn->prepare("UPDATE treatment_sessions SET status = 'Confirmed' WHERE plan_id = ? AND session_number = ? LIMIT 1");
    $upd->bind_param('ii', $planId, $sessionNumber);
    $upd->execute();
    $upd->close();

    // Set plan back to InProgress so staff can open it for the new session
    $conn->query("UPDATE treatment_plans SET status = 'InProgress' WHERE plan_id = " . intval($planId) . " AND status = 'Completed'");

    // Record in orders table
    $itemsDesc = 'Session #' . $sessionNumber . ' — Treatment Plan #' . $planId;
    $payMethod = $isSimulate ? 'sandbox_test' : 'payhere';

    $ins = $conn->prepare("
        INSERT INTO orders (order_id, payment_id, user_id, amount, currency, payment_method, status, customer_name, customer_email, customer_phone, delivery_address, delivery_city, order_items, created_at)
        VALUES (?, ?, ?, ?, 'LKR', ?, 'paid', ?, ?, ?, 'N/A', 'N/A', ?, NOW())
        ON DUPLICATE KEY UPDATE status = 'paid', payment_id = VALUES(payment_id)
    ");
    $ins->bind_param('ssidsssss', $orderId, $paymentId, $userId, $amount, $payMethod, $customerName, $customerEmail, $customerPhone, $itemsDesc);
    $ins->execute();
    $ins->close();

    foreach (['sp_pay_order_id','sp_pay_plan_id','sp_pay_session_num','sp_pay_amount','sp_pay_name','sp_pay_email','sp_pay_phone'] as $k) {
        unset($_SESSION[$k]);
    }

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'message'  => 'Session payment confirmed',
        'order_id' => $orderId,
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    error_log("Session payment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
