<?php
// app/Controllers/AppointmentController.php

require_once __DIR__ . '/../Models/AppointmentModel.php';

class AppointmentController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new AppointmentModel($conn);
    }

    // CREATE: Book consultation
    public function bookConsultation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['error' => 'Invalid request']);
        }

        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $doctor_id = intval($_POST['doctor_id'] ?? 0);
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $patient_name = $_POST['patient_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $age = intval($_POST['age'] ?? 0);
        $gender = $_POST['gender'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'onsite';

        if (!$doctor_id || !$appointment_date || !$appointment_time || !$patient_name || !$email || !$phone) {
            return json_encode(['error' => 'Missing required fields']);
        }

        $appointment_id = $this->model->bookConsultation($patient_id, $doctor_id, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method);

        if ($appointment_id) {
            return json_encode([
                'success' => true,
                'appointment_id' => $appointment_id,
                'payment_method' => $payment_method
            ]);
        }
        return json_encode(['error' => 'Failed to book appointment']);
    }

    // CREATE: Book treatment
    public function bookTreatment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['error' => 'Invalid request']);
        }

        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $treatment_type = $_POST['treatment_type'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $patient_name = $_POST['patient_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $age = intval($_POST['age'] ?? 0);
        $gender = $_POST['gender'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'onsite';

        if (!$treatment_type || !$appointment_date || !$appointment_time || !$patient_name || !$email || !$phone) {
            return json_encode(['error' => 'Missing required fields']);
        }

        $appointment_id = $this->model->bookTreatment($patient_id, $treatment_type, $appointment_date, $appointment_time, $patient_name, $email, $phone, $age, $gender, $payment_method);

        if ($appointment_id) {
            return json_encode([
                'success' => true,
                'appointment_id' => $appointment_id,
                'payment_method' => $payment_method
            ]);
        }
        return json_encode(['error' => 'Failed to book treatment']);
    }

    // READ: Get all appointments for patient
    public function getMyAppointments() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        return json_encode($this->model->getAllAppointments($patient_id));
    }

    // READ: Get consultation details
    public function getConsultation() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            return json_encode(['error' => 'Invalid ID']);
        }

        $appointment = $this->model->getConsultationById($id);
        if (!$appointment) {
            return json_encode(['error' => 'Appointment not found']);
        }

        return json_encode(['data' => $appointment]);
    }

    // READ: Get treatment details
    public function getTreatment() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            return json_encode(['error' => 'Invalid ID']);
        }

        $appointment = $this->model->getTreatmentById($id);
        if (!$appointment) {
            return json_encode(['error' => 'Appointment not found']);
        }

        return json_encode(['data' => $appointment]);
    }

    // UPDATE: Cancel appointment
    public function cancelAppointment() {
        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $id = intval($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'consultation';

        if (!$id) {
            return json_encode(['error' => 'Invalid ID']);
        }

        if ($this->model->cancelAppointment($id, $type)) {
            return json_encode(['success' => true, 'message' => 'Appointment cancelled']);
        }
        return json_encode(['error' => 'Failed to cancel']);
    }

    // READ: Get available slots
    public function getAvailableSlots() {
        $date = $_GET['date'] ?? '';
        if (!$date) {
            return json_encode(['error' => 'Date required']);
        }

        $slots = $this->model->getAvailableSlots($date);
        return json_encode(['slots' => $slots]);
    }

    // READ: Get doctors
    public function getDoctors() {
        $doctors = $this->model->getDoctors();
        return json_encode(['doctors' => $doctors]);
    }

    // UPDATE: Confirm payment
    public function confirmPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['error' => 'Invalid request']);
        }

        $patient_id = $_SESSION['user_id'] ?? null;
        if (!$patient_id) {
            return json_encode(['error' => 'Not logged in']);
        }

        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        $transaction_id = $_POST['transaction_id'] ?? '';
        $type = $_POST['type'] ?? 'consultation';

        if (!$appointment_id) {
            return json_encode(['error' => 'Invalid appointment ID']);
        }

        if ($this->model->confirmPayment($appointment_id, $type, $transaction_id)) {
            return json_encode(['success' => true, 'message' => 'Payment confirmed']);
        }
        return json_encode(['error' => 'Failed to confirm payment']);
    }
}
?>