<?php
// /dheergayu/config/payhere_config.php
// PayHere Payment Gateway Configuration

// SANDBOX MODE (for testing)
define('PAYHERE_MODE', 'sandbox'); // Change to 'live' for production

// PayHere Credentials - GET THESE FROM: https://sandbox.payhere.lk
// Settings > Domains & Credentials
define('PAYHERE_MERCHANT_ID', '1221234'); // ⚠️ REPLACE with your Merchant ID
define('PAYHERE_MERCHANT_SECRET', 'YOUR_MERCHANT_SECRET_HERE'); // ⚠️ REPLACE with your Merchant Secret

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

// Business Details
define('PAYHERE_BUSINESS_NAME', 'Dheergayu Ayurvedic Center');
define('PAYHERE_BUSINESS_EMAIL', 'info@dheergayu.com');

// Currency
define('PAYHERE_CURRENCY', 'LKR'); // Sri Lankan Rupees

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