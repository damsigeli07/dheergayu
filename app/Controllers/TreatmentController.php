<?php
// Simple treatment status update handler
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

$treatment_id = (int)($_POST['treatment_id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!$treatment_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'treatment_id and status are required']);
    exit;
}

try {
    // Database connection
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    // Update treatment status
    $stmt = $db->prepare("UPDATE treatments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $treatment_id);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        $db->close();
        
        if ($affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Treatment status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No treatment found with the given ID']);
        }
    } else {
        throw new Exception("Failed to execute update query: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Treatment status update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>