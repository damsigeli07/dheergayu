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
        $productSource = $_GET['product_source'] ?? null;
        $rows = $this->model->getBatchesByProductId($productId, $productSource);
        $this->json(['data' => $rows]);
    }

    public function create(): void {
        try {
            // Ensure no output before JSON
            ob_clean();
            
            $payload = $_POST;
            
            // Validate required fields
            $productId = isset($payload['product_id']) ? (int)$payload['product_id'] : null;
            if (!$productId && isset($payload['product_name'])) {
                $productId = $this->model->findProductIdByName($payload['product_name']);
            }
            if (!$productId) { 
                $this->json(['success' => false, 'error' => 'product_id required'], 400); 
                return; 
            }

            $batchNumber = trim($payload['batch_number'] ?? '');
            if (empty($batchNumber)) {
                $this->json(['success' => false, 'error' => 'batch_number is required'], 400);
                return;
            }
            
            // Log the batch number for debugging
            error_log("BatchController::create - batch_number received: " . var_export($batchNumber, true));
            error_log("BatchController::create - batch_number type: " . gettype($batchNumber));
            error_log("BatchController::create - batch_number length: " . strlen($batchNumber));

            $quantity = (int)($payload['quantity'] ?? 0);
            if ($quantity <= 0) {
                $this->json(['success' => false, 'error' => 'quantity must be greater than 0'], 400);
                return;
            }

            $mfd = trim($payload['mfd'] ?? '');
            $exp = trim($payload['exp'] ?? '');
            $supplier = trim($payload['supplier'] ?? '');
            
            if (empty($mfd) || empty($exp) || empty($supplier)) {
                $this->json(['success' => false, 'error' => 'mfd, exp, and supplier are required'], 400);
                return;
            }

            $productSource = trim($payload['product_source'] ?? 'admin');
            $status = trim($payload['status'] ?? 'Good');
            
            $ok = $this->model->createBatch(
                $productId,
                $productSource,
                $batchNumber,
                $quantity,
                $mfd,
                $exp,
                $supplier,
                $status
            );
            
            if ($ok) {
                $this->json(['success' => true]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to create batch. Check server logs for details.'], 500);
            }
        } catch (\Exception $e) {
            error_log("BatchController::create error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
        }
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


