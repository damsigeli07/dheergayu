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
    $accountType = strtolower(input('account_type'));
    $email = input('email');
    $nic = input('nic');
    $dob = input('dob');
    $phone = preg_replace('/\D+/', '', input('phone'));
    $newPassword = input('new_password');
    $confirmPassword = input('confirm_password');

    if ($accountType === '' || $email === '' || $newPassword === '' || $confirmPassword === '') {
        throw new Exception('Please fill all required fields.');
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

    $targetTable = '';
    $idColumn = 'id';
    $verifyStmt = null;

    if ($accountType === 'patient') {
        if ($nic === '' || $dob === '') {
            throw new Exception('NIC and Date of Birth are required for patient reset.');
        }
        $targetTable = 'patients';
        $verifyStmt = $conn->prepare("SELECT id FROM patients WHERE email = ? AND nic = ? AND dob = ? LIMIT 1");
        $verifyStmt->bind_param('sss', $email, $nic, $dob);
    } elseif (in_array($accountType, ['admin', 'doctor', 'staff', 'pharmacist'], true)) {
        if ($phone === '') {
            throw new Exception('Phone number is required for this account type.');
        }
        $targetTable = 'users';
        $verifyStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = ? AND REPLACE(phone,' ','') = ? LIMIT 1");
        $verifyStmt->bind_param('sss', $email, $accountType, $phone);
    } elseif ($accountType === 'supplier') {
        if ($phone === '') {
            throw new Exception('Phone number is required for supplier reset.');
        }
        $targetTable = 'suppliers';
        $verifyStmt = $conn->prepare("SELECT id FROM suppliers WHERE email = ? AND REPLACE(phone,' ','') = ? LIMIT 1");
        $verifyStmt->bind_param('ss', $email, $phone);
    } else {
        throw new Exception('Invalid account type.');
    }

    $verifyStmt->execute();
    $userRow = $verifyStmt->get_result()->fetch_assoc();
    $verifyStmt->close();

    if (!$userRow) {
        throw new Exception('Verification failed. Please check your details.');
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateSql = "UPDATE {$targetTable} SET password = ? WHERE {$idColumn} = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('si', $hashedPassword, $userRow[$idColumn]);
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
