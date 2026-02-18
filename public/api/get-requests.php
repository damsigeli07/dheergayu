<?php
// public/api/get-requests.php - Always reads from product_requests table (no caching)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

    // Only use session user_id when the logged-in user is a pharmacist (from users table).
    // Check both user_type and user_role (LoginController sets user_role only).
    $raw = null;
    if (isset($_GET['pharmacist_id']) && $_GET['pharmacist_id'] !== '') {
        $raw = $_GET['pharmacist_id'];
    } elseif (!empty($_SESSION['user_id'])) {
        $role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '');
        if (strtolower((string)$role) === 'pharmacist') {
            $raw = $_SESSION['user_id'];
        }
    }
    if ($raw === '' || $raw === 'null' || $raw === null) {
        $pharmacist_id = 0;
    } else {
        $pharmacist_id = (int) $raw;
    }

    if ($pharmacist_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Please log in as a pharmacist to view requests.', 'requests' => []]);
        exit;
    }

    // Always read from database: check table exists then query
    $table_check = $conn->query("SHOW TABLES LIKE 'product_requests'");
    if (!$table_check || $table_check->num_rows == 0) {
        echo json_encode(['success' => true, 'requests' => [], 'message' => 'Table not set up yet.']);
        exit;
    }

    $model = new ProductRequestModel($conn);
    $requests = $model->getRequestsByPharmacist($pharmacist_id);
    // Normalize: ensure we have arrays and consistent keys
    $requests = is_array($requests) ? $requests : [];

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);

} catch (Exception $e) {
    error_log("Get Requests Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'requests' => []
    ]);
}
?>

