<?php
// public/api/treatment_selection.php – slot loader/saver for doctor popup

declare(strict_types=1);

use Core\Database;

require_once __DIR__ . '/../../core/bootloader.php';
header('Content-Type: application/json');

$db = Database::connect();
require_once __DIR__ . '/../../app/Models/treatment_selectionModel.php';
$model = new TreatmentSelectionModel($db);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'loadSlots') {
        $treatmentId = (int)($_POST['treatment_id'] ?? 0);
        $date = trim($_POST['date'] ?? '');
        if (!$treatmentId || !$date) {
            echo json_encode(['success' => false, 'message' => 'Missing params']);
            exit;
        }
        $selected = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime('today');
        if (!$selected || $selected <= $today) {
            echo json_encode(['success' => false, 'message' => 'Date must be in the future']);
            exit;
        }
        $slots = $model->getAvailableSlots($treatmentId, $date);
        echo json_encode(['success' => true, 'slots' => $slots]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
        // When doctor books from consult form popup, POST contains patient_id (the patient being consulted).
        // Use that so the booking is for the patient, not the logged-in doctor.
        $patientIdForBooking = !empty($_POST['patient_id']) ? (int)$_POST['patient_id'] : (int)($_SESSION['user_id'] ?? 0);

        $treatmentId = (int)($_POST['treatment_id'] ?? 0);
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $date = trim($_POST['date'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$patientIdForBooking || !$treatmentId || !$slotId || !$date) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters (include patient when booking from consultation)']);
            exit;
        }
        $selected = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime('today');
        if (!$selected || $selected <= $today) {
            echo json_encode(['success' => false, 'message' => 'Date must be in the future']);
            exit;
        }

        $bookingId = $model->saveSelection($patientIdForBooking, $treatmentId, $slotId, $date, $description);
        if ($bookingId) {
            echo json_encode(['success' => true, 'booking_id' => (int)$bookingId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Slot already booked or save failed']);
        }
        exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error processing request',
        'error' => $e->getMessage()
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);

