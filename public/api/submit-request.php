<?php
// public/api/submit-request.php (used by pharmacist)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

    $action = $_POST['action'] ?? '';

    if ($action === 'create_order') {
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $request_date = trim($_POST['request_date'] ?? date('Y-m-d'));
        $pharmacist_id = (int)($_POST['pharmacist_id'] ?? $_SESSION['user_id']);
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);

        if (!$supplier_id || !is_array($items) || empty($items)) {
            echo json_encode(['success' => false, 'error' => 'Supplier and at least one product with quantity are required']);
            exit;
        }

        $model = new ProductRequestModel($conn);
        $count = $model->createRequestBatch($supplier_id, $request_date, $pharmacist_id, $items);

        if ($count > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order submitted successfully! ' . $count . ' line(s) added.',
                'count' => $count
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No valid items to submit. Enter quantity for at least one product.'
            ]);
        }
    } elseif ($action === 'create_request') {
        $data = [
            'product_name' => $_POST['product_name'] ?? '',
            'quantity' => (int)($_POST['quantity'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'request_date' => $_POST['request_date'] ?? date('Y-m-d'),
            'pharmacist_id' => (int)($_POST['pharmacist_id'] ?? $_SESSION['user_id'])
        ];

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
    } elseif ($action === 'update_request') {
        $request_id = (int)($_POST['request_id'] ?? 0);
        $product_name = trim($_POST['product_name'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $pharmacist_id = (int)($_POST['pharmacist_id'] ?? $_SESSION['user_id']);

        if (!$request_id || $product_name === '' || $quantity <= 0) {
            echo json_encode(['success' => false, 'error' => 'Request ID, product name and quantity are required.']);
            exit;
        }

        $model = new ProductRequestModel($conn);
        $ok = $model->updateRequest($request_id, $pharmacist_id, $product_name, $quantity);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Order line updated.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update failed. Request may be delivered or not found.']);
        }
    } elseif ($action === 'delete_request') {
        $request_id = (int)($_POST['request_id'] ?? 0);
        $pharmacist_id = (int)($_POST['pharmacist_id'] ?? $_SESSION['user_id']);

        if (!$request_id) {
            echo json_encode(['success' => false, 'error' => 'Request ID is required.']);
            exit;
        }

        $model = new ProductRequestModel($conn);
        $ok = $model->deleteRequest($request_id, $pharmacist_id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Order line cancelled.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Cancel failed. Request may be delivered or not found.']);
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

