<?php
// app/Controllers/AppointmentController.php
require_once __DIR__ . '/../Models/AppointmentModel.php';

// AJAX handler for cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    session_start();
    require_once __DIR__ . '/../../config/config.php';
    
    $model = new AppointmentModel($conn);
    $appointment_id = $_POST['appointment_id'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if ($appointment_id && $reason) {
        $success = $model->cancelAppointment($appointment_id, $reason);
        echo $success ? 'success' : 'error';
    } else {
        echo 'error';
    }
    exit;
}

class AppointmentController {
    private $model;

    public function __construct($conn) {
        $this->model = new AppointmentModel($conn);
    }

    public function showAppointments() {
        $appointments = $this->model->getAllAppointments($_SESSION['doctor_id']);
        include __DIR__ . '/../Views/Doctor/appointmentslist.php';
    }
}
?>