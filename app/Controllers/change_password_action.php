<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dheergayu/app/Views/change_password.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (strlen($newPassword) < 6) {
    $_SESSION['change_pw_error'] = 'Password must be at least 6 characters.';
    header('Location: /dheergayu/app/Views/change_password.php');
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['change_pw_error'] = 'Passwords do not match.';
    header('Location: /dheergayu/app/Views/change_password.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
$stmt->bind_param('si', $hashed, $userId);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect to their dashboard
$role = strtolower($_SESSION['user_role'] ?? '');
switch ($role) {
    case 'doctor':
        header('Location: /dheergayu/app/Views/Doctor/doctordashboard.php');
        break;
    case 'staff':
        header('Location: /dheergayu/app/Views/Staff/stafftreatment.php');
        break;
    case 'pharmacist':
        header('Location: /dheergayu/app/Views/Pharmacist/pharmacisthome.php');
        break;
    case 'admin':
        header('Location: /dheergayu/app/Views/Admin/admindashboard.php');
        break;
    default:
        header('Location: /dheergayu/app/Views/Patient/login.php');
}
exit;
