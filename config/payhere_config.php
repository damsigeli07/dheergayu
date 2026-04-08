<?php


// SANDBOX MODE (for testing)
define('PAYHERE_MODE', 'sandbox'); 

define('PAYHERE_MERCHANT_ID', '1235035'); 
define('PAYHERE_MERCHANT_SECRET', 'NDI3NjI3Njk5OTM0NzcwMDkzMTAzOTc4NjQxNTA5MTI4MzE2NjM5MA=='); 

// PayHere API URLs
if (PAYHERE_MODE === 'sandbox') {
    define('PAYHERE_CHECKOUT_URL', 'https://sandbox.payhere.lk/pay/checkout');
} else {
    define('PAYHERE_CHECKOUT_URL', 'https://www.payhere.lk/pay/checkout');
}

// Your website URLs (update these based on your domain)
define('PAYHERE_RETURN_URL', 'http://localhost/dheergayu/app/Views/Patient/payment_success.php');
define('PAYHERE_CANCEL_URL', 'http://localhost/dheergayu/app/Views/Patient/payment_cancel.php');
define('PAYHERE_NOTIFY_URL', 'http://localhost/dheergayu/app/Views/Patient/payment_notify.php');

// Same host/path as PAYHERE_RETURN_URL — used so appointment / treatment-plan PayHere flows do not land on the shop cart success page.
if (!defined('PAYHERE_APPOINTMENT_RETURN_URL')) {
    define('PAYHERE_APPOINTMENT_RETURN_URL', str_replace('payment_success.php', 'appointment_payhere_return.php', PAYHERE_RETURN_URL));
}
if (!defined('PAYHERE_APPOINTMENT_CANCEL_URL')) {
    define('PAYHERE_APPOINTMENT_CANCEL_URL', str_replace('payment_success.php', 'patient_appointments.php', PAYHERE_RETURN_URL));
}
if (!defined('PAYHERE_TREATMENT_PLAN_RETURN_URL')) {
    define('PAYHERE_TREATMENT_PLAN_RETURN_URL', str_replace('payment_success.php', 'treatment_plan_payhere_return.php', PAYHERE_RETURN_URL));
}
if (!defined('PAYHERE_TREATMENT_PLAN_CANCEL_URL')) {
    define('PAYHERE_TREATMENT_PLAN_CANCEL_URL', str_replace('payment_success.php', 'patient_appointments.php', PAYHERE_RETURN_URL));
}

// Business Details
define('PAYHERE_BUSINESS_NAME', 'Dheergayu Ayurvedic Center');
define('PAYHERE_BUSINESS_EMAIL', 'info@dheergayu.com');

// Currency
define('PAYHERE_CURRENCY', 'LKR'); // Sri Lankan Rupees

/**
 * When true, "Test Payment" (no gateway) is available even if PAYHERE_MODE is live.
 * Set to false on production servers.
 */
if (!defined('PAYHERE_ALLOW_TEST_PAYMENT')) {
    define('PAYHERE_ALLOW_TEST_PAYMENT', true);
}

function payhere_test_payment_allowed(): bool {
    return (defined('PAYHERE_MODE') && PAYHERE_MODE === 'sandbox')
        || (defined('PAYHERE_ALLOW_TEST_PAYMENT') && PAYHERE_ALLOW_TEST_PAYMENT === true);
}

/**
 * Generate PayHere MD5 Hash
 * Required for payment verification
 */
function generatePayhereHash($merchant_id, $order_id, $amount, $currency) {
    $merchant_secret = PAYHERE_MERCHANT_SECRET;
    
    // Remove decimal points from amount
    $amountFormatted = number_format($amount, 2, '.', '');
    
    // Create hash string
    $hash = strtoupper(
        md5(
            $merchant_id . 
            $order_id . 
            $amountFormatted . 
            $currency . 
            strtoupper(md5($merchant_secret))
        )
    );
    
    return $hash;
}

/**
 * Verify PayHere Payment Notification
 * Use this in payment_notify.php
 */
function verifyPayherePayment($merchant_id, $order_id, $payhere_amount, $payhere_currency, $status_code, $md5sig) {
    $merchant_secret = PAYHERE_MERCHANT_SECRET;
    
    $local_md5sig = strtoupper(
        md5(
            $merchant_id . 
            $order_id . 
            $payhere_amount . 
            $payhere_currency . 
            $status_code . 
            strtoupper(md5($merchant_secret))
        )
    );
    
    return ($local_md5sig === $md5sig);
}

/**
 * Generate unique Order ID
 */
function generateOrderId() {
    return 'DH' . time() . rand(1000, 9999);
}
?>