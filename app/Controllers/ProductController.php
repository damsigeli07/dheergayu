<?php
// Product create and update handler
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    // Database connection
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../public/assets/images/Admin/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.']);
            exit;
        }
        
        // Generate unique filename
        $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $_POST['product_name'] ?? 'product') . '_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
            // Save relative path for database (matching the format in the SQL dump: images/filename)
            $image_path = 'images/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // Get form data
    $product_id = (int)($_POST['product_id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $product_price = (float)($_POST['product_price'] ?? 0);
    $product_description = trim($_POST['product_description'] ?? '');
    $product_type = trim($_POST['product_type'] ?? 'admin'); // Default to 'admin' if not specified
    $action = $_POST['action'] ?? '';

    // Check if this is a get operation (for editing)
    if ($action === 'get' && $product_id > 0) {
        try {
            $stmt = $db->prepare("SELECT product_id, name, price, description, image FROM products WHERE product_id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();
            $db->close();
            
            if ($product) {
                echo json_encode(['success' => true, 'product' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
            exit;
        } catch (Exception $e) {
            $db->close();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Check if this is a delete operation
    if ($action === 'delete' && $product_id > 0) {
        try {
            // Get product to delete image
            $stmt_get = $db->prepare("SELECT image FROM products WHERE product_id = ?");
            $stmt_get->bind_param('i', $product_id);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            $product = $result_get->fetch_assoc();
            $stmt_get->close();
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            // Delete image file if exists
            if (!empty($product['image'])) {
                $image_path = __DIR__ . '/../../public/assets/images/Admin/' . str_replace('images/', '', $product['image']);
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }
            }
            
            // Delete from products table
            $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param('i', $product_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
            } else {
                throw new Exception("Failed to delete product: " . $stmt->error);
            }
            exit;
        } catch (Exception $e) {
            $db->close();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Validation
    if (empty($product_name)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }

    if ($product_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Product price must be greater than 0']);
        exit;
    }

    // Check if this is an update (has product_id) or create (no product_id)
    if ($product_id > 0) {
        // UPDATE existing product
        // Get existing product to check for old image
        $stmt_check = $db->prepare("SELECT image FROM products WHERE product_id = ?");
        $stmt_check->bind_param('i', $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_product = $result_check->fetch_assoc();
        $stmt_check->close();
        
        // If new image uploaded, delete old image and use new one; otherwise keep existing
        $final_image_path = $image_path;
        if ($image_path && $existing_product && !empty($existing_product['image'])) {
            // Delete old image file
            $old_image_path = __DIR__ . '/../../public/assets/images/Admin/' . str_replace('images/', '', $existing_product['image']);
            if (file_exists($old_image_path)) {
                @unlink($old_image_path);
            }
        } elseif (!$image_path && $existing_product) {
            // Keep existing image if no new image uploaded
            $final_image_path = $existing_product['image'];
        }

        // Check if product_type column exists
        $checkColumn = $db->query("SHOW COLUMNS FROM products LIKE 'product_type'");
        $hasProductType = ($checkColumn && $checkColumn->num_rows > 0);
        
        if ($final_image_path) {
            if ($hasProductType) {
                $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, product_type = ? WHERE product_id = ?");
                $stmt->bind_param('sdsssi', $product_name, $product_price, $product_description, $final_image_path, $product_type, $product_id);
            } else {
                $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE product_id = ?");
                $stmt->bind_param('sdssi', $product_name, $product_price, $product_description, $final_image_path, $product_id);
            }
        } else {
            if ($hasProductType) {
                $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, product_type = ? WHERE product_id = ?");
                $stmt->bind_param('sdssi', $product_name, $product_price, $product_description, $product_type, $product_id);
            } else {
                $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE product_id = ?");
                $stmt->bind_param('sdsi', $product_name, $product_price, $product_description, $product_id);
            }
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            $db->close();
            
            if ($affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No product found with the given ID']);
            }
        } else {
            throw new Exception("Failed to execute update query: " . $stmt->error);
        }
    } else {
        // CREATE new product
        // Check if product name already exists
        $stmt_check = $db->prepare("SELECT product_id FROM products WHERE name = ?");
        $stmt_check->bind_param('s', $product_name);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $stmt_check->close();
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Product with this name already exists']);
            exit;
        }
        $stmt_check->close();

        // Get next product_id
        $nextId = 1;
        $res = $db->query("SELECT COALESCE(MAX(product_id), 0) + 1 AS next_id FROM products");
        if ($res) {
            $row = $res->fetch_assoc();
            $nextId = (int)$row['next_id'];
        }

        // Default image if none provided
        if (!$image_path) {
            $image_path = 'images/dheergayu.png';
        }

        // Check if product_type column exists
        $checkColumn = $db->query("SHOW COLUMNS FROM products LIKE 'product_type'");
        $hasProductType = ($checkColumn && $checkColumn->num_rows > 0);
        
        if ($hasProductType) {
            $stmt = $db->prepare("INSERT INTO products (product_id, name, price, description, image, product_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isdsss', $nextId, $product_name, $product_price, $product_description, $image_path, $product_type);
        } else {
            $stmt = $db->prepare("INSERT INTO products (product_id, name, price, description, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('isdss', $nextId, $product_name, $product_price, $product_description, $image_path);
        }

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            echo json_encode(['success' => true, 'message' => 'Product created successfully']);
        } else {
            throw new Exception("Failed to execute insert query: " . $stmt->error);
        }
    }
    
} catch (Exception $e) {
    error_log("Product operation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

