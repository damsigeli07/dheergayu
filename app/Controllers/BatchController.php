<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\BatchModel;

class BatchController extends Controller {
    private BatchModel $model;

    public function __construct() {
        $this->model = new BatchModel();
    }

    public function list(): void {
        $inventory = $this->model->getInventoryOverview();
        $this->json(['data' => $inventory]);
    }

    public function batches(): void {
        $productId = (int)($_GET['product_id'] ?? 0);
        if (!$productId) { $this->json(['error' => 'product_id required'], 400); return; }
        $rows = $this->model->getBatchesByProductId($productId);
        $this->json(['data' => $rows]);
    }

    public function create(): void {
        $payload = $_POST;
        $productId = isset($payload['product_id']) ? (int)$payload['product_id'] : null;
        if (!$productId && isset($payload['product_name'])) {
            $productId = $this->model->findProductIdByName($payload['product_name']);
        }
        if (!$productId) { $this->json(['error' => 'product_id required'], 400); return; }

        $ok = $this->model->createBatch(
            $productId,
            trim($payload['batch_number'] ?? ''),
            (int)($payload['quantity'] ?? 0),
            trim($payload['mfd'] ?? ''),
            trim($payload['exp'] ?? ''),
            trim($payload['supplier'] ?? ''),
            trim($payload['status'] ?? 'Good')
        );
        $this->json(['success' => $ok]);
    }

    public function update(): void {
        $payload = $_POST;
        $productId = (int)($payload['product_id'] ?? 0);
        $batchNumber = trim($payload['batch_number'] ?? '');
        if (!$productId || !$batchNumber) { $this->json(['error' => 'product_id and batch_number required'], 400); return; }
        // Preserve existing values if not provided
        $existing = $this->model->getBatch($productId, $batchNumber) ?? [];
        $ok = $this->model->updateBatch(
            $productId,
            $batchNumber,
            isset($payload['quantity']) ? (int)$payload['quantity'] : (int)($existing['quantity'] ?? 0),
            ($payload['mfd'] ?? ($existing['mfd'] ?? '')),
            ($payload['exp'] ?? ($existing['exp'] ?? '')),
            ($payload['supplier'] ?? ($existing['supplier'] ?? '')),
            ($payload['status'] ?? ($existing['status'] ?? 'Good'))
        );
        $this->json(['success' => $ok]);
    }

    public function delete(): void {
        $payload = $_POST;
        $productId = (int)($payload['product_id'] ?? 0);
        $batchNumber = trim($payload['batch_number'] ?? '');
        if (!$productId || !$batchNumber) { $this->json(['error' => 'product_id and batch_number required'], 400); return; }
        $ok = $this->model->deleteBatch($productId, $batchNumber);
        $this->json(['success' => $ok]);
    }
}


