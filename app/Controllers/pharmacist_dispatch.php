<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

$consultationId = (int)($_POST['consultation_id'] ?? 0);
$dispatched = isset($_POST['dispatched']) && $_POST['dispatched'] === '1';

if ($consultationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid consultation id']);
    exit;
}

try {
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    if ($db->connect_error) {
        throw new Exception('Database connection failed: ' . $db->connect_error);
    }

    if ($dispatched) {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $stmt = $db->prepare("INSERT INTO consultation_dispatches (consultation_id, dispatched_by, dispatched_at, status)
                                  VALUES (?, ?, NOW(), 'Dispatched')
                                  ON DUPLICATE KEY UPDATE dispatched_by = VALUES(dispatched_by), dispatched_at = NOW(), status = 'Dispatched'");
            $stmt->bind_param('ii', $consultationId, $userId);
        } else {
            $stmt = $db->prepare("INSERT INTO consultation_dispatches (consultation_id, dispatched_at, status)
                                  VALUES (?, NOW(), 'Dispatched')
                                  ON DUPLICATE KEY UPDATE dispatched_at = NOW(), status = 'Dispatched'");
            $stmt->bind_param('i', $consultationId);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $db->prepare("DELETE FROM consultation_dispatches WHERE consultation_id = ?");
        $stmt->bind_param('i', $consultationId);
        $stmt->execute();
        $stmt->close();
    }

    $db->close();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Pharmacist dispatch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

