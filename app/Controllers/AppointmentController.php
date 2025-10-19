<?php
require_once __DIR__ . '/../Models/AppointmentModel.php';

// AJAX handler for cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    $model = new AppointmentModel($db);
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

    public function __construct($db) {
        $this->model = new AppointmentModel($db);
    }

    public function showAppointments() {
        $appointments = $this->model->getAllAppointments();
        include __DIR__ . '/../Views/Doctor/appointmentslist.php';
    }
}
