<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$userRole = strtolower($_SESSION['user_role'] ?? $_SESSION['user_type'] ?? $_SESSION['role'] ?? '');

if (!$userId || $userRole !== 'doctor') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$date = trim($_POST['date'] ?? '');
if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date']);
    exit;
}

$dayOfWeek = date('l', strtotime($date));

try {
    $conn->begin_transaction();

    // Dedicated table avoids FK issues with synthetic lock rows in consultations.
    $ddl = "
        CREATE TABLE IF NOT EXISTS doctor_unavailable_days (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT NOT NULL,
            unavailable_date DATE NOT NULL,
            reason VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_doctor_day (doctor_id, unavailable_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    if (!$conn->query($ddl)) {
        throw new Exception('Failed creating availability table: ' . $conn->error);
    }

    // Resolve doctor display name from users table.
    $docStmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ? AND role = 'doctor' LIMIT 1");
    $docStmt->bind_param('i', $userId);
    $docStmt->execute();
    $doc = $docStmt->get_result()->fetch_assoc();
    $docStmt->close();

    if (!$doc) {
        throw new Exception('Doctor account not found');
    }

    $doctorName = 'Dr. ' . trim((string)($doc['last_name'] ?? 'Doctor'));

    // Cancel existing future-day bookings for this doctor/date, but keep completed/cancelled untouched.
    $cancelReason = 'Doctor Cancelled: Doctor unavailable on ' . $date;
    $cancelStmt = $conn->prepare("\n        UPDATE consultations\n        SET status = 'Cancelled', notes = ?\n        WHERE doctor_id = ?\n          AND appointment_date = ?\n          AND status NOT IN ('Cancelled', 'Completed')\n    ");
    $cancelStmt->bind_param('sis', $cancelReason, $userId, $date);
    $cancelStmt->execute();
    $cancelledCount = $cancelStmt->affected_rows;
    $cancelStmt->close();

    // Persist full-day unavailability marker.
    $upsertStmt = $conn->prepare("\n        INSERT INTO doctor_unavailable_days (doctor_id, unavailable_date, reason)\n        VALUES (?, ?, ?)\n        ON DUPLICATE KEY UPDATE reason = VALUES(reason)\n    ");
    $upsertStmt->bind_param('iss', $userId, $date, $cancelReason);
    $upsertStmt->execute();
    $upsertStmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Doctor marked unavailable for ' . $date,
        'date' => $date,
        'cancelled_count' => $cancelledCount,
        'locked_slots_count' => 0,
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    error_log('doctor-unavailable-day error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to apply day unavailability']);
}
