<?php
// app/Views/Patient/session_payment_return.php
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId    = trim($_GET['order_id'] ?? '');
$statusCode = $_GET['status_code'] ?? '';

if ($statusCode !== '' && (string)$statusCode !== '2') {
    header('Location: patient_appointments.php?payment=failed');
    exit;
}

$sessOrder = $_SESSION['sp_pay_order_id'] ?? '';
if ($orderId === '' || $sessOrder === '' || $orderId !== $sessOrder) {
    header('Location: patient_appointments.php?payment=session');
    exit;
}

$planId        = (int)($_SESSION['sp_pay_plan_id']     ?? 0);
$sessionNumber = (int)($_SESSION['sp_pay_session_num'] ?? 0);
$amount        = (float)($_SESSION['sp_pay_amount']    ?? 0);
$customerName  = $_SESSION['sp_pay_name']  ?? '';
$customerEmail = $_SESSION['sp_pay_email'] ?? '';
$customerPhone = $_SESSION['sp_pay_phone'] ?? '';
$paymentId     = trim($_GET['payment_id'] ?? $_GET['payhere_payment_id'] ?? 'payhere');

$post = http_build_query([
    'plan_id'        => $planId,
    'session_number' => $sessionNumber,
    'order_id'       => $orderId,
    'payment_id'     => $paymentId ?: 'payhere',
    'amount'         => $amount,
    'customer_name'  => $customerName,
    'customer_email' => $customerEmail,
    'customer_phone' => $customerPhone,
]);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '/dheergayu';
if (preg_match('#^(.+)/app/Views/Patient/#', $script, $m)) {
    $basePath = $m[1];
}
$apiUrl = $protocol . '://' . $host . $basePath . '/public/api/process-session-payment.php';

$cookie = session_name() . '=' . session_id();
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nCookie: {$cookie}\r\n",
        'content' => $post,
        'timeout' => 25,
    ],
]);

$raw  = @file_get_contents($apiUrl, false, $ctx);
$data = $raw ? json_decode($raw, true) : null;

if (!$data || empty($data['success'])) {
    header('Location: patient_appointments.php?payment=error');
    exit;
}

header('Location: appointment_payment_success.php?order_id=' . rawurlencode($orderId)
    . '&plan_id=' . $planId
    . '&session_number=' . $sessionNumber
    . '&type=session');
exit;
