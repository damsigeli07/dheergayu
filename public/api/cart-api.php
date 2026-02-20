<?php
// /dheergayu/public/api/cart-api.php
// CRUD API for cart operations
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            addToCart($conn);
            break;
        case 'get':
            getCart($conn);
            break;
        case 'update':
            updateQuantity($conn);
            break;
        case 'remove':
            removeItem($conn);
            break;
        case 'clear':
            clearCart($conn);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// ============ CREATE - Add item to cart ============
function addToCart($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    $productId = intval($_POST['product_id'] ?? 0);
    $productType = $_POST['product_type'] ?? 'admin';
    $productName = trim($_POST['product_name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $image = trim($_POST['image'] ?? '');
    
    if ($productId <= 0 || empty($productName) || $price <= 0) {
        throw new Exception('Invalid product data');
    }
    
    // Get or create cart
    $cartId = getOrCreateCart($conn, $userId, $sessionId);
    
    // Check if item already exists in cart
    $stmt = $conn->prepare("
        SELECT cart_item_id, quantity 
        FROM cart_items 
        WHERE cart_id = ? AND product_id = ? AND product_type = ?
    ");
    $stmt->bind_param("iis", $cartId, $productId, $productType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // UPDATE existing item
        $newQuantity = $row['quantity'] + $quantity;
        $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $row['cart_item_id']);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // INSERT new item
        $insertStmt = $conn->prepare("
            INSERT INTO cart_items (cart_id, product_id, product_type, product_name, price, quantity, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->bind_param("iissdis", $cartId, $productId, $productType, $productName, $price, $quantity, $image);
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart',
        'cart_count' => getCartCount($conn, $cartId)
    ]);
}

// ============ READ - Get cart items ============
function getCart($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    $cartId = getExistingCart($conn, $userId, $sessionId);
    
    if (!$cartId) {
        echo json_encode(['success' => true, 'items' => [], 'count' => 0]);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT cart_item_id, product_id, product_type, product_name, price, quantity, image 
        FROM cart_items 
        WHERE cart_id = ?
        ORDER BY added_at DESC
    ");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'cart_item_id' => $row['cart_item_id'],
            'id' => $row['product_id'],
            'type' => $row['product_type'],
            'name' => $row['product_name'],
            'price' => floatval($row['price']),
            'quantity' => intval($row['quantity']),
            'image' => $row['image']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ]);
}

// ============ UPDATE - Update item quantity ============
function updateQuantity($conn) {
    $cartItemId = intval($_POST['cart_item_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($cartItemId <= 0 || $quantity < 0) {
        throw new Exception('Invalid data');
    }
    
    if ($quantity === 0) {
        // DELETE if quantity is 0
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
        $stmt->bind_param("i", $cartItemId);
    } else {
        // UPDATE quantity
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $stmt->bind_param("ii", $quantity, $cartItemId);
    }
    
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Cart updated']);
}

// ============ DELETE - Remove item from cart ============
function removeItem($conn) {
    $cartItemId = intval($_POST['cart_item_id'] ?? 0);
    
    if ($cartItemId <= 0) {
        throw new Exception('Invalid cart item ID');
    }
    
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Item removed']);
}

// ============ DELETE - Clear entire cart ============
function clearCart($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    $cartId = getExistingCart($conn, $userId, $sessionId);
    
    if ($cartId) {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Cart cleared']);
}

// ============ HELPER FUNCTIONS ============
function getOrCreateCart($conn, $userId, $sessionId) {
    $cartId = getExistingCart($conn, $userId, $sessionId);
    
    if ($cartId) {
        return $cartId;
    }
    
    // Create new cart
    $stmt = $conn->prepare("INSERT INTO cart (user_id, session_id) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $sessionId);
    $stmt->execute();
    $cartId = $stmt->insert_id;
    $stmt->close();
    
    return $cartId;
}

function getExistingCart($conn, $userId, $sessionId) {
    if ($userId) {
        // Logged in user - find by user_id
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
    } else {
        // Guest user - find by session_id
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE session_id = ? LIMIT 1");
        $stmt->bind_param("s", $sessionId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['cart_id'] ?? null;
}

function getCartCount($conn, $cartId) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return intval($row['total'] ?? 0);
}
?>