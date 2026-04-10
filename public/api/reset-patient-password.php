<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

function input(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

try {
    $email = input('email');
    $nic = input('nic');
    $dob = input('dob');
    $newPassword = input('new_password');
    $confirmPassword = input('confirm_password');

    if ($email === '' || $nic === '' || $dob === '' || $newPassword === '' || $confirmPassword === '') {
        throw new Exception('All fields are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address.');
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception('Passwords do not match.');
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $newPassword)) {
        throw new Exception('Password must be 8+ chars with uppercase, lowercase, number and special character.');
    }

    $stmt = $conn->prepare("SELECT id FROM patients WHERE email = ? AND nic = ? AND dob = ? LIMIT 1");
    $stmt->bind_param('sss', $email, $nic, $dob);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$patient) {
        throw new Exception('Patient verification failed. Please check email, NIC, and date of birth.');
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE patients SET password = ? WHERE id = ?");
    $updateStmt->bind_param('si', $hashedPassword, $patient['id']);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(['success' => true, 'message' => 'Password reset successful. Please login with your new password.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
