<?php
// /dheergayu/public/api/update-contact-status.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($id <= 0) {
        throw new Exception('Invalid submission ID');
    }

    $validStatuses = ['new', 'read', 'replied', 'archived'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }

    $stmt = $conn->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update status');
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>