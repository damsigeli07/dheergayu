<?php
// /dheergayu/app/Views/Patient/payment_notify.php
// PayHere IPN (Instant Payment Notification) Handler
// This receives payment status updates from PayHere

session_start();
require_once __DIR__ . '/../../../config/payhere_config.php';
require_once __DIR__ . '/../../../config/config.php';

// Log the notification (for debugging)
$logFile = __DIR__ . '/../../../logs/payhere_notifications.log';
$logData = date('Y-m-d H:i:s') . " - " . json_encode($_POST) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Get POST data from PayHere
$merchant_id = $_POST['merchant_id'] ?? '';
$order_id = $_POST['order_id'] ?? '';
$payhere_amount = $_POST['payhere_amount'] ?? '';
$payhere_currency = $_POST['payhere_currency'] ?? '';
$status_code = $_POST['status_code'] ?? '';
$md5sig = $_POST['md5sig'] ?? '';

$payment_id = $_POST['payment_id'] ?? '';
$custom_1 = $_POST['custom_1'] ?? ''; // Can store user_id here
$custom_2 = $_POST['custom_2'] ?? ''; // Can store cart_id here

// Extract customer details from POST data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$customer_name = trim($first_name . ' ' . $last_name);
$customer_email = $_POST['email'] ?? '';
$customer_phone = $_POST['phone'] ?? '';
$delivery_address = $_POST['address'] ?? '';
$delivery_city = $_POST['city'] ?? '';
$order_items = $_POST['items'] ?? '';

// Verify the payment
$isValid = verifyPayherePayment(
    $merchant_id,
    $order_id,
    $payhere_amount,
    $payhere_currency,
    $status_code,
    $md5sig
);

if (!$isValid) {
    // Invalid payment notification
    http_response_code(400);
    echo "Invalid payment verification";
    exit;
}

// Check payment status
// Status codes:
// 2 = Success
// 0 = Pending
// -1 = Canceled
// -2 = Failed
// -3 = Chargedback

try {
    if ($status_code == 2) {
        // Payment successful - Save to database
        
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_id, payment_id, user_id, amount, currency, 
                payment_method, status, customer_name, customer_email, 
                customer_phone, delivery_address, delivery_city, order_items, created_at
            ) VALUES (?, ?, ?, ?, ?, 'payhere', 'paid', ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $userId = $_SESSION['user_id'] ?? null;
        $stmt->bind_param("sssdsssssss", 
            $order_id, $payment_id, $userId, $payhere_amount, $payhere_currency,
            $customer_name, $customer_email, $customer_phone, $delivery_address, 
            $delivery_city, $order_items
        );
        $stmt->execute();
        $stmt->close();
        
        // Log successful payment
        $logFile = __DIR__ . '/../../../logs/payment_success.log';
        $logData = date('Y-m-d H:i:s') . " - SUCCESS: Order $order_id, Amount: $payhere_amount $payhere_currency, Customer: $customer_email\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        echo "Payment processed successfully";
        
    } elseif ($status_code == 0) {
        // Payment pending
        echo "Payment pending";
        
    } else {
        // Payment failed/canceled
        echo "Payment failed or canceled";
    }
    
} catch (Exception $e) {
    error_log("PayHere IPN Error: " . $e->getMessage());
    http_response_code(500);
    echo "Error processing payment";
}
?>