<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

try {
    $session_id = (int)($_POST['session_id'] ?? 0);
    if (!$session_id) {
        echo json_encode(['success' => false, 'error' => 'session_id required']);
        exit;
    }

    // Get treatment_id for this session
    $stmt = $conn->prepare("
        SELECT ts.session_id, ts.oils_dispensed, tp.treatment_id
        FROM treatment_sessions ts
        JOIN treatment_plans tp ON tp.plan_id = ts.plan_id
        WHERE ts.session_id = ?
    ");
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Session not found']);
        exit;
    }
    if ($row['oils_dispensed']) {
        echo json_encode(['success' => false, 'error' => 'Oils already dispensed for this session']);
        exit;
    }

    $treatment_id = (int)$row['treatment_id'];

    // Get oils needed for this treatment
    $oils_stmt = $conn->prepare("
        SELECT tp.product_id, tp.quantity_per_session, p.name AS oil_name
        FROM treatment_products tp
        JOIN products p ON p.product_id = tp.product_id
        WHERE tp.treatment_id = ?
    ");
    $oils_stmt->bind_param('i', $treatment_id);
    $oils_stmt->execute();
    $oils = $oils_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $oils_stmt->close();

    if (empty($oils)) {
        echo json_encode(['success' => false, 'error' => 'No oils linked to this treatment']);
        exit;
    }

    $today = date('Y-m-d');
    $dispensed = [];
    $errors = [];

    foreach ($oils as $oil) {
        $product_id = (int)$oil['product_id'];
        $needed = (int)$oil['quantity_per_session'];
        $oil_name = $oil['oil_name'];

        // Check total available stock (non-expired, FIFO)
        $stock_stmt = $conn->prepare("
            SELECT batch_id, quantity FROM batches
            WHERE product_id = ? AND product_source = 'treatment'
            AND (exp IS NULL OR exp >= ?)
            AND quantity > 0
            ORDER BY exp ASC, batch_id ASC
        ");
        $stock_stmt->bind_param('is', $product_id, $today);
        $stock_stmt->execute();
        $batches = $stock_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stock_stmt->close();

        $totalAvailable = array_sum(array_column($batches, 'quantity'));
        if ($totalAvailable < $needed) {
            $errors[] = "$oil_name: only $totalAvailable bottle(s) available, need $needed";
            continue;
        }

        // Deduct FIFO
        $remaining = $needed;
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $deduct = min($remaining, (int)$batch['quantity']);
            $upd = $conn->prepare("UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?");
            $upd->bind_param('ii', $deduct, $batch['batch_id']);
            $upd->execute();
            $upd->close();
            $remaining -= $deduct;
        }
        $dispensed[] = "$oil_name x$needed";
    }

    if (!empty($errors) && empty($dispensed)) {
        echo json_encode(['success' => false, 'error' => 'Insufficient stock: ' . implode(', ', $errors)]);
        exit;
    }

    // Mark session as oils dispensed
    $mark = $conn->prepare("UPDATE treatment_sessions SET oils_dispensed = 1 WHERE session_id = ?");
    $mark->bind_param('i', $session_id);
    $mark->execute();
    $mark->close();

    $msg = 'Dispensed: ' . implode(', ', $dispensed);
    if (!empty($errors)) $msg .= '. Warnings: ' . implode(', ', $errors);

    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
