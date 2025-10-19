
<?php
// AJAX endpoint to fetch consultation form data for modal view
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_consultation_form' && isset($_GET['appointment_id'])) {
    require_once __DIR__ . '/../Models/ConsultationFormModel.php';
    $db = new mysqli('localhost', 'root', '', 'dheergayu_db');
    $model = new ConsultationFormModel($db);
    $appointment_id = intval($_GET['appointment_id']);
    $form = $model->getConsultationFormByAppointmentId($appointment_id);
    header('Content-Type: application/json');
    if ($form) {
        echo json_encode($form);
    } else {
        echo json_encode(new stdClass()); // Return empty object if not found
    }
    exit;
}

require_once __DIR__ . '/../Models/ConsultationFormModel.php';
require_once __DIR__ . '/../Models/AppointmentModel.php';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');
$model = new ConsultationFormModel($db);
$appointmentModel = new AppointmentModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'age' => $_POST['age'] ?? '',
        'diagnosis' => $_POST['diagnosis'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'personal_products' => $_POST['personal_products'] ?? '[]',
        'recommended_treatment' => $_POST['recommended_treatment'] ?? '',
        'question_1' => $_POST['question_1'] ?? '',
        'question_2' => $_POST['question_2'] ?? '',
        'question_3' => $_POST['question_3'] ?? '',
        'question_4' => $_POST['question_4'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'patient_no' => $_POST['patient_no'] ?? '',
        'last_visit_date' => $_POST['last_visit_date'] ?? '',
        'total_visits' => $_POST['total_visits'] ?? 0,
        'contact_info' => $_POST['contact_info'] ?? '',
        'check_patient_vitals' => isset($_POST['check_patient_vitals']) ? 1 : 0,
        'review_previous_medications' => isset($_POST['review_previous_medications']) ? 1 : 0,
        'update_patient_history' => isset($_POST['update_patient_history']) ? 1 : 0,
        'follow_up_appointment' => isset($_POST['follow_up_appointment']) ? 1 : 0,
        'send_to_pharmacy' => isset($_POST['send_to_pharmacy']) ? 1 : 0,
        'appointment_id' => $_POST['appointment_id'] ?? '',
    ];
    if ($action === 'update_consultation_form') {
        $success = $model->updateConsultationForm($data);
        header('Content-Type: application/json');
        if ($success) {
            $appointmentModel->setCompletedStatus($data['appointment_id']);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    } else {
        $success = $model->saveConsultationForm($data);
        header('Content-Type: application/json');
        if ($success) {
            $appointmentModel->setCompletedStatus($data['appointment_id']);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }
}

// For GET: fetch appointment details for pre-fill
$appointment_id = $_GET['appointment_id'] ?? '';
$appointment = $appointment_id ? $model->getAppointmentDetails($appointment_id) : null;
?>