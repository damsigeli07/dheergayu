<?php
/**
 * /dheergayu/public/api/process-order.php
 *
 * Core order processor — called by:
 *   • payment_success.php  (PayHere return_url, also handles sandbox/test)
 *   • payment_notify.php   (PayHere IPN for production)
 *
 * Actions:
 *   process  – finalise a real PayHere-paid order
 *   simulate – create a paid order directly (sandbox / test button)
 *   pending  – create a pending order row before redirect (idempotent)
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/payhere_config.php';

$action = trim($_POST['action'] ?? $_GET['action'] ?? 'process');

if ($action === 'simulate' && !payhere_test_payment_allowed()) {
    echo json_encode(['success' => false, 'error' => 'Test payment is disabled']);
    exit;
}

switch ($action) {
    case 'process':
    case 'simulate':
        doProcessOrder($conn, true);
        break;

    case 'pending':
        // Store a pending order before redirecting to PayHere
        createPendingOrder($conn);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN PROCESSING FUNCTION
// ─────────────────────────────────────────────────────────────────────────────
function doProcessOrder(mysqli $conn, bool $markPaid): void
{
    $orderId       = trim($_POST['order_id']       ?? $_SESSION['po_order_id']   ?? '');
    $paymentId     = trim($_POST['payment_id']     ?? '');
    $amount        = (float)($_POST['amount']      ?? $_SESSION['po_amount']     ?? 0);
    $customerName  = trim($_POST['customer_name']  ?? $_SESSION['po_name']       ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? $_SESSION['po_email']      ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? $_SESSION['po_phone']      ?? '');
    $address       = trim($_POST['address']        ?? $_SESSION['po_address']    ?? '');
    $city          = trim($_POST['city']           ?? $_SESSION['po_city']       ?? '');
    $userId        = (int)($_POST['user_id']       ?? $_SESSION['user_id']       ?? 0) ?: null;
    $sessionId     = session_id();

    if (empty($orderId)) {
        echo json_encode(['success' => false, 'error' => 'Order ID is required']);
        return;
    }

    // ── Idempotency: already paid? ────────────────────────────────────────────
    $chk = $conn->prepare("SELECT id, status FROM orders WHERE order_id = ? LIMIT 1");
    $chk->bind_param('s', $orderId);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($existing && $existing['status'] === 'paid') {
        echo json_encode(['success' => true, 'message' => 'Order already processed', 'already_done' => true]);
        return;
    }

    // ── Get cart ──────────────────────────────────────────────────────────────
    if ($userId) {
        $cq = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
        $cq->bind_param('i', $userId);
    } else {
        $cq = $conn->prepare("SELECT cart_id FROM cart WHERE session_id = ? LIMIT 1");
        $cq->bind_param('s', $sessionId);
    }
    $cq->execute();
    $cartRow = $cq->get_result()->fetch_assoc();
    $cq->close();

    $cartId = $cartRow['cart_id'] ?? null;
    $items  = [];

    if ($cartId !== null) {
        $iq = $conn->prepare("SELECT * FROM cart_items WHERE cart_id = ?");
        $iq->bind_param('i', $cartId);
        $iq->execute();
        $items = $iq->get_result()->fetch_all(MYSQLI_ASSOC);
        $iq->close();
    }

    // Cart already cleared but pending order exists → just mark paid
    if (empty($items) && $existing) {
        $u = $conn->prepare("UPDATE orders SET status = 'paid', payment_id = ? WHERE order_id = ?");
        $u->bind_param('ss', $paymentId, $orderId);
        $u->execute();
        $u->close();
        echo json_encode(['success' => true, 'message' => 'Order updated to paid']);
        return;
    }

    if (empty($items)) {
        echo json_encode(['success' => false, 'error' => 'Cart is empty – nothing to process']);
        return;
    }

    // ── Calculate total ───────────────────────────────────────────────────────
    if ($amount <= 0) {
        $amount = (float)array_sum(array_map(
            fn($i) => (float)$i['price'] * (int)$i['quantity'], $items
        ));
    }

    $itemsDesc = implode(', ', array_map(
        fn($i) => $i['product_name'] . ' x' . $i['quantity'], $items
    ));

    $conn->begin_transaction();
    try {
        // ── 1. Upsert order row ───────────────────────────────────────────────
        $status = $markPaid ? 'paid' : 'pending';
        if ($existing) {
            $s = $conn->prepare("
                UPDATE orders SET
                    status = ?, payment_id = ?, amount = ?,
                    customer_name = ?, customer_email = ?, customer_phone = ?,
                    delivery_address = ?, delivery_city = ?, order_items = ?
                WHERE order_id = ?
            ");
            $s->bind_param('ssdsssssss',
                $status, $paymentId, $amount,
                $customerName, $customerEmail, $customerPhone,
                $address, $city, $itemsDesc, $orderId
            );
        } else {
            $s = $conn->prepare("
                INSERT INTO orders
                    (order_id, payment_id, user_id, amount, currency,
                     payment_method, status, customer_name, customer_email,
                     customer_phone, delivery_address, delivery_city,
                     order_items, created_at)
                VALUES (?, ?, ?, ?, 'LKR', 'payhere', ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $s->bind_param('ssiisssssss',
                $orderId, $paymentId, $userId, $amount,
                $status, $customerName, $customerEmail, $customerPhone,
                $address, $city, $itemsDesc
            );
        }
        $s->execute();
        $s->close();

        // Stock reduction happens at dispatch time (dispatch-shop-order.php), not here

        // ── 3. Clear cart ─────────────────────────────────────────────────────
        if ($cartId && $markPaid) {
            $d = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $d->bind_param('i', $cartId);
            $d->execute();
            $d->close();
        }

        // Clear session payment data
        foreach (['po_order_id','po_amount','po_name','po_email','po_phone','po_address','po_city'] as $k) {
            unset($_SESSION[$k]);
        }

        $conn->commit();

        echo json_encode([
            'success'     => true,
            'message'     => $markPaid ? 'Order processed successfully' : 'Pending order created',
            'order_id'    => $orderId,
            'amount'      => $amount,
            'items_count' => count($items),
        ]);

    } catch (Throwable $e) {
        $conn->rollback();
        error_log("process-order error [{$orderId}]: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// CREATE PENDING ORDER (called before PayHere redirect)
// ─────────────────────────────────────────────────────────────────────────────
function createPendingOrder(mysqli $conn): void
{
    // Save customer data to session so success page can use it
    $_SESSION['po_order_id'] = trim($_POST['order_id']       ?? '');
    $_SESSION['po_amount']   = (float)($_POST['amount']      ?? 0);
    $_SESSION['po_name']     = trim($_POST['customer_name']  ?? '');
    $_SESSION['po_email']    = trim($_POST['customer_email'] ?? '');
    $_SESSION['po_phone']    = trim($_POST['customer_phone'] ?? '');
    $_SESSION['po_address']  = trim($_POST['address']        ?? '');
    $_SESSION['po_city']     = trim($_POST['city']           ?? '');

    // Create a pending DB row (so we can update it to 'paid' on success)
    $_POST['action'] = 'process';
    doProcessOrder($conn, false);
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK REDUCTION — FIFO by manufacture date then created_at
// ─────────────────────────────────────────────────────────────────────────────
function reduceStock(mysqli $conn, int $productId, int $qty): void
{
    if ($qty <= 0) return;

    $s = $conn->prepare("
        SELECT batch_id, quantity
        FROM   batches
        WHERE  product_id = ? AND quantity > 0
        ORDER  BY mfd ASC, created_at ASC
    ");
    $s->bind_param('i', $productId);
    $s->execute();
    $batches = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();

    foreach ($batches as $b) {
        if ($qty <= 0) break;

        if ((int)$b['quantity'] >= $qty) {
            $newQ = (int)$b['quantity'] - $qty;
            $u = $conn->prepare("UPDATE batches SET quantity = ? WHERE batch_id = ?");
            $u->bind_param('ii', $newQ, $b['batch_id']);
            $u->execute();
            $u->close();
            $qty = 0;
        } else {
            $qty -= (int)$b['quantity'];
            $zero = 0;
            $u = $conn->prepare("UPDATE batches SET quantity = ? WHERE batch_id = ?");
            $u->bind_param('ii', $zero, $b['batch_id']);
            $u->execute();
            $u->close();
        }
    }

    if ($qty > 0) {
        error_log("Stock shortfall: product_id=$productId still needs $qty units after all batches");
    }
}