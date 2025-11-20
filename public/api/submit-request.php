<?php
// public/api/submit-request.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

    $action = $_POST['action'] ?? '';

    if ($action === 'create_request') {
        $data = [
            'product_name' => $_POST['product_name'] ?? '',
            'quantity' => (int)($_POST['quantity'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'request_date' => $_POST['request_date'] ?? date('Y-m-d'),
            'pharmacist_id' => (int)($_POST['pharmacist_id'] ?? $_SESSION['user_id'])
        ];

        // Validate required fields
        if (empty($data['product_name']) || $data['quantity'] <= 0 || $data['supplier_id'] <= 0) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit;
        }

        $model = new ProductRequestModel($conn);
        $result = $model->createRequest($data);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Product request submitted successfully!',
                'request_id' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to submit request. Please try again.'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Submit Request Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

