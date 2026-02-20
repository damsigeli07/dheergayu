<?php
// /dheergayu/public/api/delete-contact.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid submission ID');
    }

    $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete submission');
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Submission deleted successfully'
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