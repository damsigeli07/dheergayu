<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/bootloader.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

try {
    $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;

    if ($supplierId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid supplier id'
        ]);
        exit;
    }

    $model = new ProductRequestModel($conn);
    $requests = $model->getRequestsBySupplier($supplierId);

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
} catch (Throwable $e) {
    error_log('Supplier requests API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load supplier requests'
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

