<?php
require_once __DIR__ . '/../../core/bootloader.php';
require_once __DIR__ . '/../../app/Models/ProductRequestModel.php';

header('Content-Type: application/json');

if (!isset($_POST['request_id'])) {
    echo json_encode(["success" => false, "message" => "Missing request_id"]);
    exit;
}

$requestId = intval($_POST['request_id']);

$model = new ProductRequestModel($conn);

$updated = $model->markAsDelivered($requestId);

echo json_encode([
    "success" => $updated,
    "message" => $updated ? "Status updated to delivered." : "Failed to update status."
]);
