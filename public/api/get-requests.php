<?php
// public/api/get-requests.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

    $pharmacist_id = $_GET['pharmacist_id'] ?? $_SESSION['user_id'] ?? null;

    if (!$pharmacist_id) {
        echo json_encode(['success' => false, 'error' => 'Pharmacist ID required']);
        exit;
    }

    // Check if product_requests table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'product_requests'");
    if (!$table_check || $table_check->num_rows == 0) {
        echo json_encode(['success' => true, 'requests' => []]);
        exit;
    }

    $model = new ProductRequestModel($conn);
    $requests = $model->getRequestsByPharmacist((int)$pharmacist_id);

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);

} catch (Exception $e) {
    error_log("Get Requests Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

