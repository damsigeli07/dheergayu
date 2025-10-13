<?php
// backend/admin/admin_users.php
require_once("../../backend/db_connect.php");

// Fetch all users
$sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, role, email, phone, status, reg_date FROM users";
$result = $conn->query($sql);

$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>
