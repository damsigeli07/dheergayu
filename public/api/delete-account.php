<?php
// public/api/delete-account.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';

$user_id = $_SESSION['user_id'];

// Delete from all related tables
$conn->begin_transaction();

try {
    // Delete consultations
    $stmt = $conn->prepare("DELETE FROM consultations WHERE patient_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete treatments
    $stmt = $conn->prepare("DELETE FROM treatments WHERE patient_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete patient profile
    $stmt = $conn->prepare("DELETE FROM patient_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete user account
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    // Destroy session
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to delete account']);
}

$conn->close();
?>