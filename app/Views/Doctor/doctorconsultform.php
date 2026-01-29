<?php
require_once __DIR__ . '/../../../core/bootloader.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../patient/login.php');
    exit;
}

// Handle POST request for saving consultation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Controllers/ConsultationFormController.php';
    // The controller will handle the response and exit
}

// Handle GET request - Display the form
$appointment_id = $_GET['appointment_id'] ?? '';
$appointment = null;

if ($appointment_id) {
    $db = \Core\Database::connect();
    $stmt = $db->prepare("
        SELECT c.*, p.id as patient_id, p.first_name, p.last_name 
        FROM consultations c
        LEFT JOIN users p ON c.patient_id = p.id
        WHERE c.id = ?
    ");
    $stmt->bind_param('i', $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // If no appointment found, redirect back
    if (!$appointment) {
        header('Location: doctordashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Consultation Form</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctorconsultform.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>DOCTOR'S CONSULTATION FORM</h1>
        </header>
        <hr class="title-divider" />

        <div class="form-container">
            <form id="consultationForm" method="POST">
                <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment_id) ?>">
                <input type="hidden" name="patient_id" value="<?= htmlspecialchars($appointment['patient_id'] ?? '') ?>">
                <input type="hidden" name="patient_no" value="<?= htmlspecialchars($appointment['patient_no'] ?? '') ?>">
                
                <div class="main-content">
                    <div class="left-section">
                        <h2>Consultation Form</h2>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($appointment['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group half">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($appointment['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label>Age</label>
                                <input type="number" name="age" value="<?= htmlspecialchars($appointment['age'] ?? '') ?>" required>
                            </div>
                            <div class="form-group half">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="">Select</option>
                                    <option value="Male" <?= ($appointment['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($appointment['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($appointment['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Diagnosis</label>
                            <textarea name="diagnosis" required></textarea>
                        </div>

                        <!-- Products Section -->
                        <div class="form-group">
                            <label>Prescribed Products</label>
                            <div class="product-selector">
                                <input type="text" id="product_search" placeholder="Search product..." list="product_list">
                                <datalist id="product_list"></datalist>
                                <input type="number" id="product_qty" min="1" placeholder="Qty">
                                <button type="button" id="add_product">Add</button>
                            </div>
                            <input type="hidden" name="personal_products" id="personal_products" value="[]">
                            <table class="product-table" id="cart_table" style="display:none;">
                                <thead>
                                    <tr><th>Product</th><th>Qty</th><th>Remove</th></tr>
                                </thead>
                                <tbody id="product_list_table"></tbody>
                            </table>
                        </div>

                        <!-- Treatment Plan Section -->
                        <div class="form-group">
                            <label>Treatment Plan</label>
                            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="treatment_plan_choice" value="no_need" checked> No treatment needed
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="treatment_plan_choice" value="single_session"> Single session
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="treatment_plan_choice" value="multiple_sessions"> Multiple sessions
                                </label>
                                <button type="button" id="open_schedule_generator" disabled 
                                        style="background:linear-gradient(135deg,#28a745,#20c997);color:#fff;padding:8px 16px;border:none;border-radius:6px;font-size:14px;">
                                    Generate Schedule
                                </button>
                                <button type="button" id="open_single_treatment" disabled 
                                        style="background:linear-gradient(135deg,#d17f1b,#e88f39);color:#fff;padding:8px 16px;border:none;border-radius:6px;font-size:14px;">
                                    Select Treatment
                                </button>
                            </div>

                            <!-- Single session display -->
                            <div id="single_treatment_summary" style="display:none;margin-top:8px;border:1px solid rgba(209,127,27,0.12);padding:8px;border-radius:6px;">
                                <strong>Selected:</strong>
                                <div id="single_treatment_text"></div>
                                <button type="button" id="edit_single_treatment" style="background:linear-gradient(135deg,#d17f1b,#e88f39);color:#fff;padding:6px 12px;border:none;border-radius:6px;font-size:13px;margin-top:6px;">Edit</button>
                            </div>

                            <!-- Multiple sessions display -->
                            <div id="treatment_schedule_summary" style="display:none;margin-top:12px;border:1px solid rgba(40,167,69,0.3);padding:12px;border-radius:6px;background:#d4edda;">
                                <strong style="color:#155724;">Treatment Schedule:</strong>
                                <div id="schedule_details" style="margin-top:8px;"></div>
                            </div>

                            <input type="hidden" name="treatment_schedule_data" id="treatment_schedule_data" value="">
                            <input type="hidden" name="single_treatment_data" id="single_treatment_data" value="">
                            <input type="hidden" name="recommended_treatment" id="recommended_treatment" value="">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes"></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back</button>
                    <button type="submit" name="save_type" value="save" class="btn btn-secondary">Save</button>
                    <button type="submit" name="save_type" value="pharmacy" class="btn btn-primary">Send to Pharmacy</button>
                    <button type="button" class="btn btn-tertiary" onclick="window.print()">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
// Product management
var selectedProducts = [];
var availableProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProductsFromDatabase();
});

function loadProductsFromDatabase() {
    fetch('/dheergayu/public/api/get-products.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableProducts = data.products;
                populateProductList();
            }
        })
        .catch(error => console.error('Error loading products:', error));
}

function populateProductList() {
    const datalist = document.getElementById('product_list');
    datalist.innerHTML = '';
    availableProducts.forEach(product => {
        const option = document.createElement('option');
        option.value = `${product.name} (Av: ${product.available_quantity} units)`;
        option.setAttribute('data-product-id', product.id);
        option.setAttribute('data-available-qty', product.available_quantity);
        datalist.appendChild(option);
    });
}

document.getElementById("add_product").addEventListener("click", function() {
    let product = document.getElementById("product_search").value.trim();
    let qty = parseInt(document.getElementById("product_qty").value.trim());

    if (!product || !qty || qty <= 0) {
        alert("Please enter product and valid quantity.");
        return;
    }

    const cleanProductName = product.split(' (Av:')[0];
    selectedProducts.push({ product: cleanProductName, qty: qty });
    
    document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
    
    let table = document.getElementById("product_list_table");
    let cartTable = document.getElementById("cart_table");
    cartTable.style.display = "table";

    let row = document.createElement("tr");
    row.innerHTML = `
        <td>${cleanProductName}</td>
        <td>${qty}</td>
        <td><button type="button" class="remove-btn">X</button></td>
    `;

    row.querySelector(".remove-btn").addEventListener("click", function() {
        selectedProducts = selectedProducts.filter(p => !(p.product === cleanProductName && p.qty === qty));
        document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
        row.remove();
        if (table.children.length === 0) cartTable.style.display = "none";
    });

    table.appendChild(row);
    document.getElementById("product_search").value = "";
    document.getElementById("product_qty").value = "";
});

// Treatment plan management
const treatmentPlanRadios = document.querySelectorAll('input[name="treatment_plan_choice"]');
const scheduleGeneratorBtn = document.getElementById('open_schedule_generator');
const singleTreatmentBtn = document.getElementById('open_single_treatment');
const scheduleSummary = document.getElementById('treatment_schedule_summary');
const singleSummary = document.getElementById('single_treatment_summary');

function updateTreatmentButtons() {
    const choice = document.querySelector('input[name="treatment_plan_choice"]:checked')?.value;
    
    scheduleGeneratorBtn.disabled = choice !== 'multiple_sessions';
    singleTreatmentBtn.disabled = choice !== 'single_session';
    
    if (choice === 'no_need') {
        scheduleSummary.style.display = 'none';
        singleSummary.style.display = 'none';
        document.getElementById('treatment_schedule_data').value = '';
        document.getElementById('single_treatment_data').value = '';
        document.getElementById('recommended_treatment').value = 'No treatment needed';
    } else if (choice !== 'multiple_sessions') {
        scheduleSummary.style.display = 'none';
        document.getElementById('treatment_schedule_data').value = '';
    } else if (choice !== 'single_session') {
        singleSummary.style.display = 'none';
        document.getElementById('single_treatment_data').value = '';
    }
}

treatmentPlanRadios.forEach(r => r.addEventListener('change', updateTreatmentButtons));
updateTreatmentButtons();

// Open schedule generator
scheduleGeneratorBtn.addEventListener('click', function() {
    const appointmentId = document.querySelector('input[name="appointment_id"]').value;
    const patientId = document.querySelector('input[name="patient_id"]').value;
    const url = `/dheergayu/app/Views/Doctor/treatment_schedule_generator.php?appointment_id=${appointmentId}&patient_id=${patientId}`;
    window.open(url, 'schedule_generator', 'width=950,height=700');
});

// Open single treatment selector
singleTreatmentBtn.addEventListener('click', function() {
    const appointmentId = document.querySelector('input[name="appointment_id"]').value;
    const patientId = document.querySelector('input[name="patient_id"]').value;
    const url = `/dheergayu/app/Views/Doctor/treatment_selection.php?appointment_id=${appointmentId}&patient_id=${patientId}`;
    window.open(url, 'treatment_select', 'width=720,height=620');
});

document.getElementById('edit_single_treatment')?.addEventListener('click', function() {
    singleTreatmentBtn.click();
});

// Listen for messages from popups
window.addEventListener('message', function(event) {
    try {
        const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
        
        if (data.type === 'schedule_generated') {
            setTreatmentSchedule(data.payload);
        } else if (data.type === 'treatment_selected') {
            setSingleTreatment(data.payload);
        }
    } catch (e) {
        console.error('Invalid message data', e);
    }
});

function setTreatmentSchedule(scheduleData) {
    document.getElementById('treatment_schedule_data').value = JSON.stringify(scheduleData);
    
    const totalCost = scheduleData.sessions * 4500;
    const detailsHtml = `
        <strong>Treatment:</strong> ${scheduleData.treatmentType}<br>
        <strong>Diagnosis:</strong> ${scheduleData.diagnosis}<br>
        <strong>Sessions:</strong> ${scheduleData.sessions} sessions, ${scheduleData.sessionsPerWeek}x per week<br>
        <strong>Start Date:</strong> ${scheduleData.startDate}<br>
        <strong>Total Cost:</strong> Rs ${totalCost.toLocaleString()}
    `;
    
    // Update recommended_treatment field
    const summary = `Treatment Plan: ${scheduleData.treatmentType} | ${scheduleData.sessions} sessions | Start: ${scheduleData.startDate}`;
    document.getElementById('recommended_treatment').value = summary;
    
    document.getElementById('schedule_details').innerHTML = detailsHtml;
    scheduleSummary.style.display = 'block';
}

function setSingleTreatment(treatmentData) {
    document.getElementById('single_treatment_data').value = JSON.stringify(treatmentData);
    
    document.getElementById('single_treatment_text').innerHTML = `
        Treatment: ${treatmentData.name}<br>
        Date: ${treatmentData.date}<br>
        Time: ${treatmentData.time}
    `;
    
    // Update recommended_treatment field
    const summary = `Treatment: ${treatmentData.name} | Date: ${treatmentData.date} | Time: ${treatmentData.time}`;
    document.getElementById('recommended_treatment').value = summary;
    
    singleSummary.style.display = 'block';
}

// Form submission with validation
document.getElementById('consultationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate required fields
    const firstName = document.querySelector('input[name="first_name"]').value.trim();
    const lastName = document.querySelector('input[name="last_name"]').value.trim();
    const age = document.querySelector('input[name="age"]').value;
    const gender = document.querySelector('select[name="gender"]').value;
    const diagnosis = document.querySelector('textarea[name="diagnosis"]').value.trim();
    
    if (!firstName || !lastName || !age || !gender || !diagnosis) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Validate products
    if (selectedProducts.length === 0) {
        alert('Please add at least one prescribed product');
        return;
    }
    
    // Ensure treatment choice is set
    const treatmentChoice = document.querySelector('input[name="treatment_plan_choice"]:checked')?.value;
    if (!treatmentChoice) {
        alert('Please select a treatment plan option');
        return;
    }
    
    // Submit form
    const formData = new FormData(this);
    const submitButton = e.submitter;
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';
    
    fetch('/dheergayu/app/Controllers/ConsultationFormController.php?action=save_consultation_form', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Consultation saved successfully!');
            window.location.href = 'doctordashboard.php';
        } else {
            alert('Error: ' + (data.message || 'Failed to save'));
            submitButton.disabled = false;
            submitButton.textContent = submitButton.name === 'save_type' && submitButton.value === 'pharmacy' ? 'Send to Pharmacy' : 'Save';
        }
    })
    .catch(err => {
        console.error('Save error:', err);
        alert('Error saving consultation form');
        submitButton.disabled = false;
        submitButton.textContent = submitButton.name === 'save_type' && submitButton.value === 'pharmacy' ? 'Send to Pharmacy' : 'Save';
    });
});
    </script>
</body>
</html>