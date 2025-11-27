<?php
// Treatment create and update handler
header('Content-Type: application/json');
session_start();

// Allow both POST form submissions and JSON POSTs from the treatment selection popup.
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// If this is a front-end treatment selection save request, handle it here and return.
if (strtolower($action) === 'save_selection') {
    // Read JSON body
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $appointment_id = $data['appointment_id'] ?? '';
    $treatment_id = isset($data['treatment_id']) ? (int)$data['treatment_id'] : 0;
    $treatment_name = $data['treatment_name'] ?? ($data['treatment_type'] ?? '');
    $date = $data['date'] ?? '';
    $time = $data['time'] ?? '';
    $description = $data['description'] ?? ($data['treatment_description'] ?? '');

    if (!$treatment_name || !$date || !$time) {
        echo json_encode(['success' => false, 'message' => 'Treatment name, date and time are required']);
        exit;
    }

    // Save selection to session (temporary reservation). You can later persist to DB.
    $_SESSION['treatment_selection'] = [
        'appointment_id' => $appointment_id,
        'treatment_id' => $treatment_id,
        'treatment_name' => $treatment_name,
        'date' => $date,
        'time' => $time,
        'description' => $description,
        'reserved_at' => time()
    ];

    echo json_encode(['success' => true]);
    exit;
}

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
    if (isset($_FILES['treatment_image']) && $_FILES['treatment_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../public/assets/images/Patient/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['treatment_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.']);
            exit;
        }
        
        // Generate unique filename
        $filename = 'treatment_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['treatment_image']['tmp_name'], $target_path)) {
            // Save relative path for database
            $image_path = '/dheergayu/public/assets/images/Patient/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // Get form data (admin form). Make description optional; duration/price are optional fields.
    $treatment_id = (int)($_POST['treatment_id'] ?? 0);
    // treat "treatment_type" as same as treatment_name if provided
    $treatment_name = $_POST['treatment_name'] ?? ($_POST['treatment_type'] ?? '');
    $description = $_POST['description'] ?? null; // optional
    $duration = $_POST['duration'] ?? ''; // optional
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'Active'; // default to Active if not provided
    $action = $_POST['action'] ?? '';

    // Check if this is a get operation (for editing)
    if ($action === 'get' && $treatment_id > 0) {
        try {
            $stmt = $db->prepare("SELECT treatment_id, treatment_name, description, duration, price, image, status FROM treatment_list WHERE treatment_id = ?");
            $stmt->bind_param('i', $treatment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $treatment = $result->fetch_assoc();
            $stmt->close();
            $db->close();
            
            if ($treatment) {
                echo json_encode(['success' => true, 'treatment' => $treatment]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Treatment not found']);
            }
            exit;
        } catch (Exception $e) {
            $db->close();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Check if this is a deactivate operation
    if ($action === 'deactivate' && $treatment_id > 0) {
        try {
            $stmt = $db->prepare("UPDATE treatment_list SET status = 'Inactive' WHERE treatment_id = ?");
            $stmt->bind_param('i', $treatment_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                echo json_encode(['success' => true, 'message' => 'Treatment deactivated successfully']);
            } else {
                throw new Exception("Failed to deactivate treatment: " . $stmt->error);
            }
            exit;
        } catch (Exception $e) {
            $db->close();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Check if this is an activate operation
    if ($action === 'activate' && $treatment_id > 0) {
        try {
            $stmt = $db->prepare("UPDATE treatment_list SET status = 'Active' WHERE treatment_id = ?");
            $stmt->bind_param('i', $treatment_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                echo json_encode(['success' => true, 'message' => 'Treatment activated successfully']);
            } else {
                throw new Exception("Failed to activate treatment: " . $stmt->error);
            }
            exit;
        } catch (Exception $e) {
            $db->close();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Check if this is an update (has treatment_id) or create (no treatment_id)
    if ($treatment_id > 0) {
        // UPDATE existing treatment
        if (!$treatment_name) {
            echo json_encode(['success' => false, 'message' => 'Treatment name is required']);
            exit;
        }

        // Get existing treatment to check for old image
        $stmt_check = $db->prepare("SELECT image FROM treatment_list WHERE treatment_id = ?");
        $stmt_check->bind_param('i', $treatment_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_treatment = $result_check->fetch_assoc();
        $stmt_check->close();
        
        // If new image uploaded, delete old image and use new one; otherwise keep existing
        $final_image_path = $image_path;
        if ($image_path && $existing_treatment && !empty($existing_treatment['image'])) {
            // Delete old image file
            $old_image_path_db = $existing_treatment['image'];
            $old_image_path = preg_replace('#^/dheergayu/#', '', $old_image_path_db);
            $full_old_path = __DIR__ . '/../../' . $old_image_path;
            if (file_exists($full_old_path)) {
                @unlink($full_old_path);
            }
        } elseif (!$image_path && $existing_treatment) {
            // Keep existing image if no new image uploaded
            $final_image_path = $existing_treatment['image'];
        }

        if ($final_image_path) {
            $stmt = $db->prepare("UPDATE treatment_list SET treatment_name = ?, description = ?, duration = ?, price = ?, image = ?, status = ? WHERE treatment_id = ?");
            $stmt->bind_param('sssdssi', $treatment_name, $description, $duration, $price, $final_image_path, $status, $treatment_id);
        } else {
            $stmt = $db->prepare("UPDATE treatment_list SET treatment_name = ?, description = ?, duration = ?, price = ?, status = ? WHERE treatment_id = ?");
            $stmt->bind_param('sssdsi', $treatment_name, $description, $duration, $price, $status, $treatment_id);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            $db->close();
            
            if ($affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Treatment updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No treatment found with the given ID']);
            }
        } else {
            throw new Exception("Failed to execute update query: " . $stmt->error);
        }
    } else {
        // CREATE new treatment
        if (!$treatment_name) {
            echo json_encode(['success' => false, 'message' => 'Treatment name is required']);
            exit;
        }

        // Get next treatment_id
        $nextId = 1;
        $res = $db->query("SELECT COALESCE(MAX(treatment_id), 0) + 1 AS next_id FROM treatment_list");
        if ($res) {
            $row = $res->fetch_assoc();
            $nextId = (int)$row['next_id'];
        }

        // Default image if none provided
        if (!$image_path) {
            $image_path = '/dheergayu/public/assets/images/Patient/health-treatments.jpg';
        }

        $stmt = $db->prepare("INSERT INTO treatment_list (treatment_id, treatment_name, description, duration, price, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssdss', $nextId, $treatment_name, $description, $duration, $price, $image_path, $status);

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            echo json_encode(['success' => true, 'message' => 'Treatment created successfully']);
        } else {
            throw new Exception("Failed to execute insert query: " . $stmt->error);
        }
    }
    
} catch (Exception $e) {
    error_log("Treatment operation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>