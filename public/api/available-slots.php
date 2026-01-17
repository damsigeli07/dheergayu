<?php
// Disable ALL output before JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers FIRST
header('Content-Type: application/json');

// Start output buffering
ob_start();

try {
    require_once __DIR__ . '/../../config/config.php';
    
    $date = $_GET['date'] ?? '';

    if (!$date) {
        ob_end_clean();
        echo json_encode(['error' => 'Date required']);
        exit;
    }

    // Get day of week from date
    $dayOfWeek = date('l', strtotime($date));

    // Find doctors working on this day
    $scheduleQuery = "SELECT doctor_id, doctor_name, start_time, end_time 
                      FROM doctor_schedule 
                      WHERE day_of_week = ? AND is_active = 1";
    $stmt = $conn->prepare($scheduleQuery);
    $stmt->bind_param('s', $dayOfWeek);
    $stmt->execute();
    $scheduleResult = $stmt->get_result();

    $availableSlots = [];

    while ($schedule = $scheduleResult->fetch_assoc()) {
        $doctor_id = $schedule['doctor_id'];
        $doctor_name = $schedule['doctor_name'];
        $start = new DateTime($schedule['start_time']);
        $end = new DateTime($schedule['end_time']);
        
        // Generate 30-minute slots
        $currentSlot = clone $start;
        while ($currentSlot < $end) {
            $slotTime = $currentSlot->format('H:i:s');
            
            // Check if slot is already booked for this doctor
            $checkQuery = "SELECT status FROM consultations 
                           WHERE appointment_date = ? 
                           AND appointment_time = ? 
                           AND doctor_id = ?
                           AND status != 'Cancelled'";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('ssi', $date, $slotTime, $doctor_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            $status = 'available';
            if ($checkResult->num_rows > 0) {
                $row = $checkResult->fetch_assoc();
                $status = ($row['status'] === 'locked') ? 'locked' : 'booked';
            }
            
            $availableSlots[] = [
                'time' => $slotTime,
                'status' => $status,
                'doctor_id' => $doctor_id,
                'doctor_name' => $doctor_name
            ];
            
            $checkStmt->close();
            
            // Move to next 30-minute slot
            $currentSlot->modify('+30 minutes');
        }
    }

    $stmt->close();

    // Sort slots by time
    usort($availableSlots, function($a, $b) {
        return strcmp($a['time'], $b['time']);
    });

    ob_end_clean();
    echo json_encode(['slots' => $availableSlots]);
    
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
exit;
?>