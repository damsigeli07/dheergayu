<?php
// /dheergayu/public/api/generate-payhere-hash.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/payhere_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $orderId = $_POST['order_id'] ?? '';
    $amount = $_POST['amount'] ?? '';

    if (empty($orderId) || empty($amount)) {
        throw new Exception('Missing required parameters');
    }

    // Generate hash
    $hash = generatePayhereHash(
        PAYHERE_MERCHANT_ID,
        $orderId,
        $amount,
        PAYHERE_CURRENCY
    );

    echo json_encode([
        'success' => true,
        'hash' => $hash
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>