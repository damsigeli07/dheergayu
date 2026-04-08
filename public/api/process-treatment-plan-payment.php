<?php
/**
 * /dheergayu/public/api/process-treatment-plan-payment.php
 *
 * Processes treatment plan payments.
 * Updates payment_status to 'Completed' in treatment_plans table.
 */
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/payhere_config.php';

$isSimulate = (($_POST['action'] ?? '') === 'simulate');
if ($isSimulate && !payhere_test_payment_allowed()) {
    echo json_encode(['success' => false, 'error' => 'Test payment is disabled']);
    exit;
}

$planId        = (int)($_POST['plan_id'] ?? 0);
$orderId       = trim($_POST['order_id'] ?? '');
$paymentId     = trim($_POST['payment_id'] ?? '');
$amount        = (float)($_POST['amount'] ?? 0);
$customerName  = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$isPending     = isset($_POST['pending']);
$userId        = (int)($_SESSION['user_id'] ?? 0);

if (!$planId || !$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// If this is just saving pending state to session, do that and return
if ($isPending) {
    $_SESSION['tp_pay_order_id']  = $orderId;
    $_SESSION['tp_pay_plan_id']   = $planId;
    $_SESSION['tp_pay_amount']    = $amount;
    $_SESSION['tp_pay_name']      = $customerName;
    $_SESSION['tp_pay_email']     = $customerEmail;
    $_SESSION['tp_pay_phone']     = $customerPhone;
    echo json_encode(['success' => true, 'message' => 'Pending state saved']);
    exit;
}

try {
    $conn->begin_transaction();

    // Verify ownership and current status
    $chk = $conn->prepare("SELECT plan_id, patient_id, payment_status, treatment_name, total_cost FROM treatment_plans WHERE plan_id = ? AND patient_id = ?");
    $chk->bind_param('ii', $planId, $userId);
    $chk->execute();
    $plan = $chk->get_result()->fetch_assoc();
    $chk->close();

    if (!$plan) {
        throw new Exception('Treatment plan not found');
    }
    if ($plan['payment_status'] === 'Completed') {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Already paid', 'already_done' => true]);
        exit;
    }

    // Update payment status to Completed
    $stmt = $conn->prepare("
        UPDATE treatment_plans
        SET payment_status = 'Completed',
            status = 'InProgress'
        WHERE plan_id = ? AND patient_id = ?
    ");
    $stmt->bind_param('ii', $planId, $userId);
    $stmt->execute();
    $stmt->close();

    // Store in orders table for record keeping
    $itemsDesc = 'Treatment Plan #' . $planId . ' - ' . ($plan['treatment_name'] ?? 'Treatment');
    $status = 'paid';

    $orderPayMethod = $isSimulate ? 'sandbox_test' : 'payhere';

    $ins = $conn->prepare("
        INSERT INTO orders
            (order_id, payment_id, user_id, amount, currency,
             payment_method, status, customer_name, customer_email,
             customer_phone, delivery_address, delivery_city,
             order_items, created_at)
        VALUES (?, ?, ?, ?, 'LKR', ?, ?, ?, ?, ?, 'N/A', 'N/A', ?, NOW())
        ON DUPLICATE KEY UPDATE status = VALUES(status), payment_id = VALUES(payment_id)
    ");
    $ins->bind_param('ssiissssss',
        $orderId, $paymentId, $userId, $amount,
        $orderPayMethod, $status, $customerName, $customerEmail, $customerPhone,
        $itemsDesc
    );
    $ins->execute();
    $ins->close();

    // Clear session payment data
    foreach (['tp_pay_order_id','tp_pay_plan_id','tp_pay_amount','tp_pay_name','tp_pay_email','tp_pay_phone'] as $k) {
        unset($_SESSION[$k]);
    }

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'message'  => 'Payment processed successfully',
        'order_id' => $orderId,
        'amount'   => $amount,
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    error_log("Treatment plan payment error [{$orderId}]: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
