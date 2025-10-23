<?php
require_once(__DIR__ . "/../../config/config.php");

// Handle POST requests for status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    if (!$user_id || !$new_status) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User ID and status are required']);
        exit;
    }
    
    // Validate status
    if (!in_array($new_status, ['Active', 'Inactive'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    try {
        // Update user status in database
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $new_status, $user_id);
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No user found with the given ID']);
            }
        } else {
            throw new Exception("Failed to execute update query: " . $stmt->error);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle GET requests - fetch users data
$users = [];

// Fetch users from users table
$sql_users = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, role, email, phone, status, reg_date FROM users";
$result_users = $conn->query($sql_users);
if ($result_users === false) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => $conn->error]);
    exit;
}

if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch patients from patients table
$sql_patients = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, 'patient' AS role, email, '' AS phone, 'Active' AS status, created_at AS reg_date, dob, nic FROM patients";
$result_patients = $conn->query($sql_patients);
if ($result_patients === false) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => $conn->error]);
    exit;
}

if ($result_patients && $result_patients->num_rows > 0) {
    while ($row = $result_patients->fetch_assoc()) {
        $users[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>
