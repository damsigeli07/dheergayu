<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

$isJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) !== 'staff')) {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Unauthorized']); }
    else { header('Location: /dheergayu/app/Views/Patient/login.php'); }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Method not allowed']); }
    else { header('Location: /dheergayu/app/Views/Staff/staffprofile.php'); }
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$age = isset($_POST['age']) && $_POST['age'] !== '' ? (int) $_POST['age'] : null;
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$address = trim($_POST['address'] ?? '');
$gender = trim($_POST['gender'] ?? '');

if ($name !== '' && $first_name === '' && $last_name === '') {
    $parts = preg_split('/\s+/', $name, 2);
    $first_name = $parts[0] ?? '';
    $last_name = $parts[1] ?? '';
}
if ($first_name === '' && $last_name === '') {
    $first_name = $name;
}

if ($first_name === '' || $email === '') {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Name and email are required']); }
    else { $_SESSION['staff_profile_error'] = 'Name and email are required'; header('Location: /dheergayu/app/Views/Staff/editstaffprofile.php'); }
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ? AND LOWER(role) = 'staff'");
    $stmt->bind_param('ssssi', $first_name, $last_name, $email, $contact, $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("CREATE TABLE IF NOT EXISTS staff_info (
        user_id INT NOT NULL PRIMARY KEY,
        age INT NULL,
        address VARCHAR(500) NULL,
        gender VARCHAR(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_staff_info_user (user_id)
    )");

    $upsert = $conn->prepare("INSERT INTO staff_info (user_id, age, address, gender) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE age = VALUES(age), address = VALUES(address), gender = VALUES(gender)");
    $upsert->bind_param('iiss', $user_id, $age, $address, $gender);
    $upsert->execute();
    $upsert->close();

    $_SESSION['user_name'] = trim($first_name . ' ' . $last_name);
    $conn->close();

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        $_SESSION['staff_profile_success'] = 'Profile updated successfully.';
        header('Location: /dheergayu/app/Views/Staff/staffprofile.php');
    }
    exit;
} catch (Exception $e) {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    else { $_SESSION['staff_profile_error'] = $e->getMessage(); header('Location: /dheergayu/app/Views/Staff/editstaffprofile.php'); }
    exit;
}
