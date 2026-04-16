<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SUPPLIER_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}

if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'supplier') {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dheergayu/app/Views/Supplier/supplier_change_password.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (strlen($newPassword) < 6) {
    $_SESSION['change_pw_error'] = 'Password must be at least 6 characters.';
    header('Location: /dheergayu/app/Views/Supplier/supplier_change_password.php');
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['change_pw_error'] = 'Passwords do not match.';
    header('Location: /dheergayu/app/Views/Supplier/supplier_change_password.php');
    exit;
}

$supplierId = (int)$_SESSION['user_id'];
$hashed     = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE suppliers SET password = ?, must_change_password = 0 WHERE id = ?");
$stmt->bind_param('si', $hashed, $supplierId);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: /dheergayu/app/Views/Supplier/supplierrequest.php');
exit;
