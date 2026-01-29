<?php
// public/api/treatment-booking-handler.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Models/treatment_selectionModel.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$model = new TreatmentSelectionModel($conn);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_booking':
            $booking_id = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);
            
            if (!$booking_id) {
                echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
                exit;
            }
            
            $booking = $model->getBookingById($booking_id);
            
            if (!$booking) {
                echo json_encode(['success' => false, 'error' => 'Booking not found']);
                exit;
            }
            
            // Verify ownership
            if ($booking['patient_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            
            echo json_encode(['success' => true, 'booking' => $booking]);
            break;
            
        case 'cancel':
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            
            if (!$booking_id) {
                echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
                exit;
            }
            
            // Verify ownership
            $booking = $model->getBookingById($booking_id);
            if (!$booking || $booking['patient_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            
            $success = $model->cancelBooking($booking_id, $reason);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Treatment booking cancelled successfully' : 'Failed to cancel booking'
            ]);
            break;
            
        case 'reschedule':
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            $new_slot_id = (int)($_POST['new_slot_id'] ?? 0);
            $new_date = $_POST['new_date'] ?? '';
            
            if (!$booking_id || !$new_slot_id || !$new_date) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }
            
            // Verify ownership
            $booking = $model->getBookingById($booking_id);
            if (!$booking || $booking['patient_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            
            $success = $model->rescheduleBooking($booking_id, $new_slot_id, $new_date);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Treatment rescheduled successfully' : 'Failed to reschedule - slot may be taken'
            ]);
            break;
            
        case 'get_bookings':
            $bookings = $model->getPatientBookings($_SESSION['user_id']);
            echo json_encode(['success' => true, 'bookings' => $bookings]);
            break;
            
        case 'complete':
            // For staff/admin to mark treatment as completed
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            
            if (!$booking_id) {
                echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
                exit;
            }
            
            $success = $model->completeBooking($booking_id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Treatment marked as completed' : 'Failed to update status'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Treatment booking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>