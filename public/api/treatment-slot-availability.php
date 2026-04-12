<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
ob_start();

try {
    require_once __DIR__ . '/../../config/config.php';

    $treatment_id = (int)($_GET['treatment_id'] ?? 0);
    $date         = trim($_GET['date'] ?? '');

    if (!$treatment_id || !$date) {
        ob_end_clean();
        echo json_encode(['slots' => []]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT ts.slot_id, ts.slot_time,
               CASE WHEN EXISTS (
                   SELECT 1 FROM treatment_bookings tb
                   WHERE tb.slot_id = ts.slot_id
                     AND tb.booking_date = ?
                     AND tb.status != 'Cancelled'
               ) THEN 1 ELSE 0 END AS booked
        FROM treatment_slots ts
        WHERE ts.treatment_id = ? AND ts.is_active = 1
        ORDER BY ts.slot_time ASC
    ");
    $stmt->bind_param('si', $date, $treatment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $slots = [];
    $seen  = [];
    while ($row = $result->fetch_assoc()) {
        $t = $row['slot_time'];
        if (in_array($t, $seen)) continue;
        $seen[] = $t;
        $slots[] = [
            'slot_time' => $t,
            'booked'    => (int)$row['booked']
        ];
    }
    $stmt->close();

    ob_end_clean();
    echo json_encode(['slots' => $slots]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['slots' => [], 'error' => $e->getMessage()]);
}
exit;
