<?php
/**
 * PayHere return_url for consultation / treatment-slot appointment payments only.
 * Finalizes payment via the same API as the Test Payment button, then redirects to appointment success UI.
 */
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = trim($_GET['order_id'] ?? '');
$statusCode = $_GET['status_code'] ?? '';

if ($statusCode !== '' && (string) $statusCode !== '2') {
    header('Location: patient_appointments.php?payment=failed');
    exit;
}

$sessOrder = $_SESSION['apt_pay_order_id'] ?? '';
if ($orderId === '' || $sessOrder === '' || $orderId !== $sessOrder) {
    header('Location: patient_appointments.php?payment=session');
    exit;
}

$appointmentId = (int) ($_SESSION['apt_pay_appointment_id'] ?? 0);
$type = $_SESSION['apt_pay_type'] ?? '';
$amount = (float) ($_SESSION['apt_pay_amount'] ?? 0);
$customerName = $_SESSION['apt_pay_name'] ?? '';
$customerEmail = $_SESSION['apt_pay_email'] ?? '';
$customerPhone = $_SESSION['apt_pay_phone'] ?? '';

$paymentId = trim($_GET['payment_id'] ?? $_GET['payhere_payment_id'] ?? 'payhere');

$post = http_build_query([
    'appointment_id' => $appointmentId,
    'type'             => $type,
    'order_id'         => $orderId,
    'payment_id'       => $paymentId !== '' ? $paymentId : 'payhere',
    'amount'           => $amount,
    'customer_name'    => $customerName,
    'customer_email'   => $customerEmail,
    'customer_phone'   => $customerPhone,
]);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '/dheergayu';
if (preg_match('#^(.+)/app/Views/Patient/#', $script, $m)) {
    $basePath = $m[1];
}
$apiUrl = $protocol . '://' . $host . $basePath . '/public/api/process-appointment-payment.php';

$cookie = session_name() . '=' . session_id();
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nCookie: {$cookie}\r\n",
        'content' => $post,
        'timeout' => 25,
    ],
]);

$raw = @file_get_contents($apiUrl, false, $ctx);
$data = $raw ? json_decode($raw, true) : null;

if (!$data || empty($data['success'])) {
    header('Location: patient_appointments.php?payment=error');
    exit;
}

header('Location: appointment_payment_success.php?order_id=' . rawurlencode($orderId)
    . '&appointment_id=' . $appointmentId
    . '&type=' . rawurlencode($type));
exit;
