<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

$consultationId = (int)($_POST['consultation_id'] ?? 0);
$dispatched = isset($_POST['dispatched']) && $_POST['dispatched'] === '1';

if ($consultationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid consultation id']);
    exit;
}

try {
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    if ($db->connect_error) {
        throw new Exception('Database connection failed: ' . $db->connect_error);
    }

    // Ensure table exists so dispatch record persists after refresh
    $db->query("CREATE TABLE IF NOT EXISTS consultation_dispatches (
        consultation_id INT NOT NULL PRIMARY KEY,
        status VARCHAR(50) NOT NULL DEFAULT 'Dispatched'
    )");

    if ($dispatched) {
        // Check if already dispatched (avoid deducting inventory twice)
        $checkStmt = $db->prepare("SELECT 1 FROM consultation_dispatches WHERE consultation_id = ? AND status = 'Dispatched' LIMIT 1");
        $checkStmt->bind_param('i', $consultationId);
        $checkStmt->execute();
        $alreadyDispatched = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();

        if (!$alreadyDispatched) {
            // Get prescribed products from consultationforms (id = consultation_id)
            $formStmt = $db->prepare("SELECT personal_products FROM consultationforms WHERE id = ? LIMIT 1");
            $formStmt->bind_param('i', $consultationId);
            $formStmt->execute();
            $formRow = $formStmt->get_result()->fetch_assoc();
            $formStmt->close();

            $items = [];
            if ($formRow && !empty($formRow['personal_products'])) {
                $decoded = json_decode($formRow['personal_products'], true);
                $items = is_array($decoded) ? $decoded : [];
            }

            if (!empty($items)) {
                $db->begin_transaction();
                try {
                    foreach ($items as $item) {
                        $productName = trim($item['product'] ?? $item['name'] ?? '');
                        $qtyNeeded = (int)($item['qty'] ?? $item['quantity'] ?? 0);
                        if ($productName === '' || $qtyNeeded <= 0) continue;

                        // Resolve product_id from name (products table may use product_id or id)
                        $pidStmt = $db->prepare("SELECT * FROM products WHERE name = ? LIMIT 1");
                        $pidStmt->bind_param('s', $productName);
                        $pidStmt->execute();
                        $pidRow = $pidStmt->get_result()->fetch_assoc();
                        $pidStmt->close();
                        $productId = null;
                        if ($pidRow) {
                            $productId = isset($pidRow['product_id']) ? (int)$pidRow['product_id'] : (isset($pidRow['id']) ? (int)$pidRow['id'] : null);
                        }
                        if (!$productId) {
                            $db->rollback();
                            echo json_encode(['success' => false, 'message' => 'Product not found in inventory: ' . $productName]);
                            $db->close();
                            exit;
                        }

                        // Get batches for this product; FIFO by exp (filter by admin source if column exists)
                        $batchesStmt = $db->prepare("
                            SELECT product_id, batch_number, quantity 
                            FROM batches 
                            WHERE product_id = ? AND quantity > 0 
                            ORDER BY exp ASC
                        ");
                        $batchesStmt->bind_param('i', $productId);
                        $batchesStmt->execute();
                        $batches = $batchesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $batchesStmt->close();

                        $totalAvailable = 0;
                        foreach ($batches as $b) $totalAvailable += (int)$b['quantity'];
                        if ($totalAvailable < $qtyNeeded) {
                            $db->rollback();
                            echo json_encode(['success' => false, 'message' => 'Insufficient stock for "' . $productName . '". Required: ' . $qtyNeeded . ', Available: ' . $totalAvailable]);
                            $db->close();
                            exit;
                        }

                        $remaining = $qtyNeeded;
                        $updateBatch = $db->prepare("UPDATE batches SET quantity = quantity - ? WHERE product_id = ? AND batch_number = ?");
                        foreach ($batches as $batch) {
                            if ($remaining <= 0) break;
                            $batchQty = (int)$batch['quantity'];
                            $take = min($batchQty, $remaining);
                            $updateBatch->bind_param('iis', $take, $productId, $batch['batch_number']);
                            $updateBatch->execute();
                            $remaining -= $take;
                        }
                        $updateBatch->close();
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            }
        }

        // Save to consultation_dispatches so it persists after refresh (always write in one shot)
        $saveStmt = $db->prepare("
            INSERT INTO consultation_dispatches (consultation_id, status) VALUES (?, 'Dispatched')
            ON DUPLICATE KEY UPDATE status = 'Dispatched'
        ");
        $saveStmt->bind_param('i', $consultationId);
        if (!$saveStmt->execute()) {
            throw new Exception('Dispatch save failed: ' . $saveStmt->error);
        }
        $saveStmt->close();
    } else {
        $stmt = $db->prepare("DELETE FROM consultation_dispatches WHERE consultation_id = ?");
        $stmt->bind_param('i', $consultationId);
        $stmt->execute();
        $stmt->close();
    }

    $db->close();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($db) && $db) {
        @$db->rollback();
        @$db->close();
    }
    error_log('Pharmacist dispatch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
