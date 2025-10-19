<?php
require_once(__DIR__ . "/../../config/config.php");

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
$sql_patients = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, 'patient' AS role, email, '' AS phone, 'Active' AS status, created_at AS reg_date FROM patients";
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
