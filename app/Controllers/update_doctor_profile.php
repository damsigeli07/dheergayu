<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

$isJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) !== 'doctor')) {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Unauthorized']); }
    else { header('Location: /dheergayu/app/Views/Patient/login.php'); }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Method not allowed']); }
    else { header('Location: /dheergayu/app/Views/Doctor/doctorprofile.php'); }
    exit;
}

$user_id = (int) ($_POST['user_id'] ?? $_SESSION['user_id']);
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');

if ($email === '') {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Email is required']); }
    else { $_SESSION['doctor_profile_error'] = 'Email is required'; header('Location: /dheergayu/app/Views/Doctor/editdoctorprofile.php'); }
    exit;
}

try {
    // Update basic users table (email, phone)
    $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ? WHERE id = ? AND role = 'doctor'");
    $stmt->bind_param('ssi', $email, $contact, $user_id);
    $stmt->execute();
    $stmt->close();

    // Create doctor_info table if not exists and upsert specialization
    $conn->query("CREATE TABLE IF NOT EXISTS doctor_info (
        user_id INT NOT NULL PRIMARY KEY,
        specialization VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_doctor_info_user (user_id)
    )");

    $upsert = $conn->prepare("INSERT INTO doctor_info (user_id, specialization) VALUES (?, ?) ON DUPLICATE KEY UPDATE specialization = VALUES(specialization)");
    $upsert->bind_param('is', $user_id, $specialization);
    $upsert->execute();
    $upsert->close();

    // Optionally update session user_name if changed (we don't allow name edits here)
    $conn->close();

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        $_SESSION['doctor_profile_success'] = 'Profile updated successfully.';
        header('Location: /dheergayu/app/Views/Doctor/doctorprofile.php');
    }
    exit;
} catch (Exception $e) {
    if ($isJson) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    else { $_SESSION['doctor_profile_error'] = $e->getMessage(); header('Location: /dheergayu/app/Views/Doctor/editdoctorprofile.php'); }
    exit;
}
