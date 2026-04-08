<?php
/**
 * /dheergayu/public/api/process-appointment-payment.php
 *
 * Processes appointment (consultation / treatment) payments.
 * Updates payment_status to 'Completed' and stores transaction details.
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

$appointmentId = (int)($_POST['appointment_id'] ?? 0);
$type          = trim($_POST['type'] ?? '');
$orderId       = trim($_POST['order_id'] ?? '');
$paymentId     = trim($_POST['payment_id'] ?? '');
$amount        = (float)($_POST['amount'] ?? 0);
$customerName  = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$isPending     = isset($_POST['pending']);
$userId        = (int)($_SESSION['user_id'] ?? 0);

if (!$appointmentId || !in_array($type, ['consultation', 'treatment']) || !$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// If this is just saving pending state to session, do that and return
if ($isPending) {
    $_SESSION['apt_pay_order_id']       = $orderId;
    $_SESSION['apt_pay_appointment_id'] = $appointmentId;
    $_SESSION['apt_pay_type']           = $type;
    $_SESSION['apt_pay_amount']         = $amount;
    $_SESSION['apt_pay_name']           = $customerName;
    $_SESSION['apt_pay_email']          = $customerEmail;
    $_SESSION['apt_pay_phone']          = $customerPhone;
    echo json_encode(['success' => true, 'message' => 'Pending state saved']);
    exit;
}

try {
    $conn->begin_transaction();

    if ($type === 'consultation') {
        // Verify ownership
        $chk = $conn->prepare("SELECT id, payment_status FROM consultations WHERE id = ? AND patient_id = ?");
        $chk->bind_param('ii', $appointmentId, $userId);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$row) {
            throw new Exception('Appointment not found');
        }
        if ($row['payment_status'] === 'Completed') {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Already paid', 'already_done' => true]);
            exit;
        }

        $payMethod = $isSimulate ? 'sandbox_test' : 'payhere';
        $txnId = $paymentId !== '' ? $paymentId : $orderId;
        $stmt = $conn->prepare("
            UPDATE consultations
            SET payment_status = 'Completed',
                payment_method = ?,
                transaction_id = ?
            WHERE id = ? AND patient_id = ?
        ");
        $stmt->bind_param('ssii', $payMethod, $txnId, $appointmentId, $userId);
        $stmt->execute();
        $stmt->close();

    } else {
        // Treatment booking - verify ownership
        $chk = $conn->prepare("SELECT booking_id, status FROM treatment_bookings WHERE booking_id = ? AND patient_id = ?");
        $chk->bind_param('ii', $appointmentId, $userId);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$row) {
            throw new Exception('Treatment booking not found');
        }

        // Mark as Confirmed (payment done)
        $stmt = $conn->prepare("
            UPDATE treatment_bookings
            SET status = 'Confirmed'
            WHERE booking_id = ? AND patient_id = ?
        ");
        $stmt->bind_param('ii', $appointmentId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    // Store in orders table for record keeping
    $itemsDesc = ucfirst($type) . ' #' . $appointmentId;
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
    foreach (['apt_pay_order_id','apt_pay_appointment_id','apt_pay_type','apt_pay_amount','apt_pay_name','apt_pay_email','apt_pay_phone'] as $k) {
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
    error_log("Appointment payment error [{$orderId}]: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
