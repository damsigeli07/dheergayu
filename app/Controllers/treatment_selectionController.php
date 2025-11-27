<?php

class TreatmentSelectionController {

    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    // Load main form page
    public function index()
    {
        $treatments = $this->model->getAllTreatments();
        include "../app/Views/Treatment/treatment_selection.php";
    }

    // AJAX request: load slots
    public function loadSlots()
    {
        $treatment_id = $_POST['treatment_id'];
        $date = $_POST['date'];

        $slots = $this->model->getAvailableSlots($treatment_id, $date);
        echo json_encode($slots->fetch_all(MYSQLI_ASSOC));
    }

    // Save form
    public function save()
    {
        session_start();

        $patient_id = $_SESSION['patient_id']; // logged in user
        $treatment  = $_POST['treatment'];
        $slot       = $_POST['slot'];
        $date       = $_POST['date'];
        $desc       = $_POST['description'];

        $result = $this->model->saveSelection($patient_id, $treatment, $slot, $date, $desc);

        if ($result) {
            $_SESSION['msg'] = "Treatment successfully booked!";
        } else {
            $_SESSION['msg'] = "Error while saving!";
        }

        header("Location: /treatment_selection");
    }
}
