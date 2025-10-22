<?php
// Treatment create and update handler
header('Content-Type: application/json');

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

    // Get form data
    $treatment_id = (int)($_POST['treatment_id'] ?? 0);
    $treatment_name = $_POST['treatment_name'] ?? '';
    $description = $_POST['description'] ?? null;
    $duration = $_POST['duration'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? '';
    $action = $_POST['action'] ?? '';

    // Check if this is a delete operation
    if ($action === 'delete' && $treatment_id > 0) {
        // DELETE treatment
        try {
            // First, get the treatment data to move to deletedadmintreatment table
            $stmt = $db->prepare("SELECT * FROM admin_treatment WHERE treatment_id = ?");
            $stmt->bind_param('i', $treatment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $treatment = $result->fetch_assoc();
            $stmt->close();
            
            if (!$treatment) {
                echo json_encode(['success' => false, 'message' => 'Treatment not found']);
                exit;
            }
            
            $db->begin_transaction();
            
            // Insert into deletedadmintreatment table
            $stmt1 = $db->prepare("INSERT INTO deletedadmintreatment (treatment_id, treatment_name, description, duration, price, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt1->bind_param('isssds', 
                $treatment['treatment_id'], 
                $treatment['treatment_name'], 
                $treatment['description'], 
                $treatment['duration'], 
                $treatment['price'], 
                $treatment['status']
            );
            $stmt1->execute();
            $stmt1->close();
            
            // Delete from admin_treatment table
            $stmt2 = $db->prepare("DELETE FROM admin_treatment WHERE treatment_id = ?");
            $stmt2->bind_param('i', $treatment_id);
            $stmt2->execute();
            $stmt2->close();
            
            $db->commit();
            $db->close();
            echo json_encode(['success' => true, 'message' => 'Treatment deleted successfully']);
            exit;
            
        } catch (Exception $e) {
            $db->rollback();
            $db->close();
            throw $e;
        }
    }

    // Check if this is an update (has treatment_id) or create (no treatment_id)
    if ($treatment_id > 0) {
        // UPDATE existing treatment
        if (!$treatment_name || !$duration || !$status) {
            echo json_encode(['success' => false, 'message' => 'Treatment name, duration, and status are required']);
            exit;
        }

        $stmt = $db->prepare("UPDATE admin_treatment SET treatment_name = ?, description = ?, duration = ?, price = ?, status = ? WHERE treatment_id = ?");
        $stmt->bind_param('sssdsi', $treatment_name, $description, $duration, $price, $status, $treatment_id);
        
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
        if (!$treatment_name || !$duration || !$status) {
            echo json_encode(['success' => false, 'message' => 'Treatment name, duration, and status are required']);
            exit;
        }

        // Get next treatment_id
        $nextId = 1;
        $res = $db->query("SELECT COALESCE(MAX(treatment_id), 0) + 1 AS next_id FROM admin_treatment");
        if ($res) {
            $row = $res->fetch_assoc();
            $nextId = (int)$row['next_id'];
        }

        $stmt = $db->prepare("INSERT INTO admin_treatment (treatment_id, treatment_name, description, duration, price, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssds', $nextId, $treatment_name, $description, $duration, $price, $status);
        
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