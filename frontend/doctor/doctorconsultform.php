<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Consultation Form</title>
    <link rel="stylesheet" href="css/doctorconsultform.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>DOCTOR'S CONSULTATION FORM</h1>
        </header>

        <div class="form-container">
            <form method="POST" action="">
                <div class="main-content">
                    <div class="left-section">
                        <h2>Consultation Form</h2>
                        
                        <div class="form-group">
                            <label for="patient_name">Patient's Name</label>
                            <input type="text" id="patient_name" name="patient_name" placeholder="Enter patient's name">
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label for="age">Age</label>
                                <input type="number" id="age" name="age" placeholder="Enter age">
                            </div>
                            <div class="form-group half">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="consultation_history">Patient's previous consultation history</label>
                            <textarea id="consultation_history" name="consultation_history" placeholder="Previous consultation details..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="diagnosis">Diagnosis</label>
                            <textarea id="diagnosis" name="diagnosis" placeholder="Enter diagnosis..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="prescribed_products">Prescribed Products</label>
                            <textarea id="prescribed_products" name="prescribed_products" placeholder="Enter prescribed medicines..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="recommended_treatment">Recommended Treatment</label>
                            <textarea id="recommended_treatment" name="recommended_treatment" placeholder="Recommended treatment details..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>

                    <div class="right-section">
                        <div class="auto-filled-section">
                            <h3>Auto-filled data</h3>
                            
                            <div class="info-group">
                                <strong>Patient No:</strong>
                                <div class="info-value">P001234</div>
                            </div>

                            <div class="info-group">
                                <strong>Previous Visits:</strong>
                                <div class="info-value">Last visit: 15/04/2025</div>
                                <div class="info-value">Total visits: 3</div>
                            </div>

                            <div class="info-group">
                                <strong>Contact Info:</strong>
                                <div class="info-value">+94 77 123 4567</div>
                            </div>
                        </div>

                        <div class="checklist-section">
                            <h3>Should check-in</h3>
                            <div class="checklist-item">- Check patient vitals</div>
                            <div class="checklist-item">- Review previous medications</div>
                            <div class="checklist-item">- Update patient history</div>
                            <div class="checklist-item">- Follow-up appointment</div>
                            <div class="checklist-item">- Send to pharmacy</div>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-secondary">Save</button>
                    <button type="submit" class="btn btn-primary">Send to Pharmacy</button>
                    <button type="button" class="btn btn-tertiary">Print</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    if ($_POST) {
        // Handle form submission
        $patient_name = $_POST['patient_name'] ?? '';
        $age = $_POST['age'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $consultation_history = $_POST['consultation_history'] ?? '';
        $diagnosis = $_POST['diagnosis'] ?? '';
        $prescribed_products = $_POST['prescribed_products'] ?? '';
        $recommended_treatment = $_POST['recommended_treatment'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        echo "<script>alert('Form submitted successfully!');</script>";
    }
    ?>
</body>
</html>