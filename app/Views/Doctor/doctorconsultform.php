<?php
require_once __DIR__ . '/../../includes/auth_doctor.php';
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
        LEFT JOIN patients p ON c.patient_id = p.id
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

    // Block until payment is recorded as completed (online PayHere or staff-recorded cash/onsite)
    $payStatus = $appointment['payment_status'] ?? '';
    if ($payStatus !== 'Completed') {
        echo "<script>alert('Cannot start consultation — patient has not completed payment yet.'); window.location.href='doctordashboard.php';</script>";
        exit;
    }

    // Load any existing consultation form for this appointment so we can prefill values for editing
    $form = null;
    $fstmt = $db->prepare("SELECT * FROM consultationforms WHERE appointment_id = ? LIMIT 1");
    if ($fstmt) {
        $fstmt->bind_param('i', $appointment_id);
        $fstmt->execute();
        $form = $fstmt->get_result()->fetch_assoc();
        $fstmt->close();
    }
    // If the consultation form exists but doesn't include a recommended treatment,
    // try to load a linked treatment plan for this appointment so the edit form
    // can show the treatment type (single or multiple) when opening the page.
    if ($form && (empty($form['recommended_treatment']) || trim($form['recommended_treatment']) === '')) {
        $pst = $db->prepare("SELECT tp.plan_id, tp.treatment_id, tp.start_date, tp.diagnosis, tl.treatment_name, tl.price
                             FROM treatment_plans tp
                             LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
                             WHERE tp.appointment_id = ? LIMIT 1");
        if ($pst) {
            $pst->bind_param('i', $appointment_id);
            $pst->execute();
            $prow = $pst->get_result()->fetch_assoc();
            $pst->close();
            if ($prow) {
                $form['recommended_treatment'] = "Treatment Plan: " . ($prow['treatment_name'] ?? '') . " | Start: " . ($prow['start_date'] ?? '');
                $form['treatment_plan'] = $prow;

                // Populate single_treatment_data so UI shows it as a selected treatment
                $single = [
                    'name' => $prow['treatment_name'] ?? '',
                    'date' => $prow['start_date'] ?? '',
                    'time' => ''
                ];
                $form['single_treatment_data'] = json_encode($single);
            }
        }
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
                <pre id="initial_form_debug" style="display:none;background:#f7f7f7;border:1px solid #eee;padding:10px;white-space:pre-wrap;max-height:200px;overflow:auto;margin-bottom:12px;font-size:12px;color:#333;"></pre>
                <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment_id) ?>">
                <input type="hidden" name="patient_id" value="<?= htmlspecialchars($appointment['patient_id'] ?? '') ?>">
                <input type="hidden" name="patient_no" value="<?= htmlspecialchars($appointment['patient_no'] ?? '') ?>">
                
                <div class="main-content">
                    <div class="left-section">
                        <h2>Consultation Form</h2>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($form['first_name'] ?? $appointment['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group half">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($form['last_name'] ?? $appointment['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label>Age</label>
                                <input type="number" name="age" value="<?= htmlspecialchars($form['age'] ?? $appointment['age'] ?? '') ?>" required>
                            </div>
                            <div class="form-group half">
                                <label>Gender</label>
                                    <select name="gender" required>
                                    <option value="">Select</option>
                                    <option value="Male" <?= ($form['gender'] ?? $appointment['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($form['gender'] ?? $appointment['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($form['gender'] ?? $appointment['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Diagnosis</label>
                            <textarea name="diagnosis" required><?= htmlspecialchars($form['diagnosis'] ?? '') ?></textarea>
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
                            <input type="hidden" name="personal_products" id="personal_products" value="<?= htmlspecialchars($form['personal_products'] ?? '[]') ?>">
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
                                <?php
                                    $saved_choice = 'no_need';
                                    if (!empty($form['single_treatment_data'])) {
                                        $saved_choice = 'single_session';
                                    }
                                ?>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="treatment_plan_choice" value="no_need" <?= $saved_choice === 'no_need' ? 'checked' : '' ?>> No treatment needed
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="treatment_plan_choice" value="single_session" <?= $saved_choice === 'single_session' ? 'checked' : '' ?>> Treatment needed
                                </label>
                                <button type="button" id="open_single_treatment"
                                        style="display:none;background:linear-gradient(135deg,#d17f1b,#e88f39);color:#fff;padding:8px 16px;border:none;border-radius:6px;font-size:14px;">
                                    Select Treatment
                                </button>
                            </div>

                            <!-- Single session display -->
                            <div id="single_treatment_summary" style="display:none;margin-top:8px;border:1px solid rgba(209,127,27,0.12);padding:8px;border-radius:6px;">
                                <strong>Selected:</strong>
                                <div id="single_treatment_text"></div>
                                <button type="button" id="edit_single_treatment" style="background:linear-gradient(135deg,#d17f1b,#e88f39);color:#fff;padding:6px 12px;border:none;border-radius:6px;font-size:13px;margin-top:6px;">Edit</button>
                            </div>

                            <!-- Recommended treatment display fallback -->
                            <div id="recommended_treatment_display" style="display:none;margin-top:12px;padding:8px;border-radius:6px;background:#fff3cd;border:1px solid #ffeeba;color:#856404;">
                                <strong>Recommended Treatment:</strong>
                                <div id="recommended_treatment_text" style="margin-top:6px;"></div>
                            </div>

                            <input type="hidden" name="single_treatment_data" id="single_treatment_data" value="<?= htmlspecialchars($form['single_treatment_data'] ?? '') ?>">
                            <input type="hidden" name="recommended_treatment" id="recommended_treatment" value="<?= htmlspecialchars($form['recommended_treatment'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes"><?= htmlspecialchars($form['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back</button>
                    <button type="submit" name="save_type" value="save" class="btn btn-secondary">Save</button>
                    <button type="button" class="btn btn-tertiary" onclick="window.print()">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Provide initial form data from server to JS
    window.initialConsultationForm = <?= json_encode($form ?? null) ?>;
    // Initialize UI from server data once the DOM is ready (independent of product API timing)
    document.addEventListener('DOMContentLoaded', function() {
        var init = window.initialConsultationForm;
        console.log('initialConsultationForm:', init);
        // If URL contains debug=1 show raw data for troubleshooting
        try {
            var params = new URLSearchParams(window.location.search);
            if (params.get('debug') === '1') {
                var pre = document.getElementById('initial_form_debug');
                pre.style.display = 'block';
                pre.textContent = JSON.stringify(init, null, 2);
            }
        } catch (e) {}
        if (!init) return;

        // Ensure hidden recommended field is set
        try { if (init.recommended_treatment) document.getElementById('recommended_treatment').value = init.recommended_treatment; } catch(e){}

        // Restore treatment choice and summaries
        try {
            console.log('restoring treatment data, recommended_treatment=', init.recommended_treatment);
            if (init.single_treatment_data && init.single_treatment_data !== '') {
                var single = JSON.parse(init.single_treatment_data);
                document.querySelector('input[name="treatment_plan_choice"][value="single_session"]').checked = true;
                if (typeof setSingleTreatment === 'function') setSingleTreatment(single);
                // also show recommended display fallback if no detailed single text is rendered
                if (!document.getElementById('single_treatment_text').innerHTML && init.recommended_treatment) {
                    document.getElementById('recommended_treatment_text').innerText = init.recommended_treatment;
                    document.getElementById('recommended_treatment_display').style.display = 'block';
                }
            } else {
                var rec = (init.recommended_treatment || '').trim();
                if (rec && rec.toLowerCase() !== 'no treatment needed') {
                    document.getElementById('recommended_treatment_text').innerText = rec;
                    document.getElementById('recommended_treatment_display').style.display = 'block';
                }
            }
        } catch (e) {
            console.error('Error restoring treatment data', e);
        }

        // Restore selected products into the cart UI (works even if product list not loaded)
        try {
            if (init.personal_products && init.personal_products !== '') {
                selectedProducts = JSON.parse(init.personal_products || '[]');
                if (Array.isArray(selectedProducts) && selectedProducts.length > 0) {
                    const table = document.getElementById('product_list_table');
                    const cartTable = document.getElementById('cart_table');
                    cartTable.style.display = 'table';
                    table.innerHTML = '';
                    selectedProducts.forEach(function(item, idx) {
                        const row = document.createElement('tr');
                        row.innerHTML = `\n                        <td>${item.product}</td>\n                        <td>${item.qty}</td>\n                        <td><button type="button" class="remove-btn">X</button></td>\n                    `;
                        row.querySelector('.remove-btn').addEventListener('click', function() {
                            selectedProducts = selectedProducts.filter(function(p, i){ return !(p.product === item.product && p.qty == item.qty && i === idx); });
                            document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
                            row.remove();
                            if (table.children.length === 0) cartTable.style.display = 'none';
                        });
                        table.appendChild(row);
                    });
                    document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
                }
            }
        } catch (e) {
            console.error('Error restoring products', e);
        }

        // Update UI toggles
        if (typeof updateTreatmentButtons === 'function') updateTreatmentButtons();
    });

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
        let label = `${product.name} (Av: ${product.available_quantity} units)`;
        if (product.available_quantity === 0 && product.expired_quantity > 0) {
            label += ' ⚠ ALL EXPIRED';
        } else if (product.available_quantity === 0) {
            label += ' ⚠ OUT OF STOCK';
        } else if (product.expired_quantity > 0) {
            label += ` ⚠ ${product.expired_quantity} expired`;
        }
        option.value = label;
        option.setAttribute('data-product-id', product.id);
        option.setAttribute('data-available-qty', product.available_quantity);
        option.setAttribute('data-expired-qty', product.expired_quantity);
        datalist.appendChild(option);
    });

        // If the server provided an existing consultation form, populate selected products and other fields
    if (window.initialConsultationForm && window.initialConsultationForm.personal_products) {
        try {
            selectedProducts = JSON.parse(window.initialConsultationForm.personal_products || '[]');
        } catch (e) {
            selectedProducts = [];
        }

        if (Array.isArray(selectedProducts) && selectedProducts.length > 0) {
            const table = document.getElementById('product_list_table');
            const cartTable = document.getElementById('cart_table');
            cartTable.style.display = 'table';
            table.innerHTML = '';
            selectedProducts.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `\n                    <td>${item.product}</td>\n                    <td>${item.qty}</td>\n                    <td><button type="button" class="remove-btn">X</button></td>\n                `;
                row.querySelector('.remove-btn').addEventListener('click', function() {
                    selectedProducts = selectedProducts.filter(p => !(p.product === item.product && p.qty == item.qty));
                    document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
                    row.remove();
                    if (table.children.length === 0) cartTable.style.display = 'none';
                });
                table.appendChild(row);
            });
            document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
        }

        // Prefill treatment plan summaries if present
        if (window.initialConsultationForm.recommended_treatment) {
            document.getElementById('recommended_treatment').value = window.initialConsultationForm.recommended_treatment;
            if ((window.initialConsultationForm.single_treatment_data || '') !== '') {
                try {
                    const single = JSON.parse(window.initialConsultationForm.single_treatment_data);
                    setSingleTreatment(single);
                } catch (e) {}
            }
        }

        // Re-run UI toggles so the treatment buttons/summaries reflect restored choice
        if (typeof updateTreatmentButtons === 'function') updateTreatmentButtons();

        // Remove sentinel so we don't reapply
        delete window.initialConsultationForm;
    }
}

document.getElementById("add_product").addEventListener("click", function() {
    let product = document.getElementById("product_search").value.trim();
    let qty = parseInt(document.getElementById("product_qty").value.trim());

    if (!product || !qty || qty <= 0) {
        alert("Please enter product and valid quantity.");
        return;
    }

    const cleanProductName = product.split(' (Av:')[0];

    // Find product data to check stock
    const productData = availableProducts.find(p => p.name === cleanProductName);
    if (productData) {
        if (productData.available_quantity === 0 && productData.expired_quantity > 0) {
            if (!confirm(`⚠ WARNING: All stock of "${cleanProductName}" is expired.\nPrescribing this is not recommended.\n\nProceed anyway?`)) return;
        } else if (productData.available_quantity === 0) {
            if (!confirm(`⚠ WARNING: "${cleanProductName}" is out of stock.\nPrescribing this is not recommended.\n\nProceed anyway?`)) return;
        } else if (qty > productData.available_quantity) {
            if (!confirm(`⚠ WARNING: Only ${productData.available_quantity} units available for "${cleanProductName}", but you are prescribing ${qty}.\n\nProceed anyway?`)) return;
        } else if (productData.expired_quantity > 0) {
            alert(`ℹ Note: "${cleanProductName}" has ${productData.expired_quantity} expired units in stock. The pharmacist should remove them.`);
        }
    }
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
const singleTreatmentBtn = document.getElementById('open_single_treatment');
const singleSummary = document.getElementById('single_treatment_summary');

function updateTreatmentButtons() {
    const choice = document.querySelector('input[name="treatment_plan_choice"]:checked')?.value;

    singleTreatmentBtn.style.display = choice === 'single_session' ? 'inline-block' : 'none';
    singleTreatmentBtn.disabled = false;

    if (choice === 'no_need') {
        singleSummary.style.display = 'none';
        document.getElementById('single_treatment_data').value = '';
        var rec = (document.getElementById('recommended_treatment') && document.getElementById('recommended_treatment').value) || '';
        if (rec && rec.toLowerCase().trim() !== 'no treatment needed') {
            document.getElementById('recommended_treatment_text').innerText = rec;
            document.getElementById('recommended_treatment_display').style.display = 'block';
        } else {
            document.getElementById('recommended_treatment').value = 'No treatment needed';
            document.getElementById('recommended_treatment_display').style.display = 'none';
        }
    } else if (choice === 'single_session') {
        var recVal = (document.getElementById('recommended_treatment') && document.getElementById('recommended_treatment').value) || '';
        if (recVal && recVal.toLowerCase().trim() !== 'no treatment needed') {
            document.getElementById('recommended_treatment_text').innerText = recVal;
            document.getElementById('recommended_treatment_display').style.display = 'block';
        } else {
            document.getElementById('recommended_treatment_display').style.display = 'none';
        }
    }
}

treatmentPlanRadios.forEach(r => r.addEventListener('change', updateTreatmentButtons));
updateTreatmentButtons();

// IMPORTANT: Set initial recommended_treatment value
document.getElementById('recommended_treatment').value = 'No treatment needed';

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
        
        if (data.type === 'treatment_selected') {
            setSingleTreatment(data.payload);
        }
    } catch (e) {
        console.error('Invalid message data', e);
    }
});

function setSingleTreatment(treatmentData) {
    document.getElementById('single_treatment_data').value = JSON.stringify(treatmentData);

    document.getElementById('single_treatment_text').innerHTML = `
        Treatment: ${treatmentData.name}<br>
        Date: ${treatmentData.date}<br>
        Time: ${treatmentData.time}
    `;

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
        alert('Please fill in all required fields (including gender)');
        return;
    }
    
    // Make products optional (remove or comment out this validation)
    // if (selectedProducts.length === 0) {
    //     alert('Please add at least one prescribed product');
    //     return;
    // }
    
    // Ensure treatment choice is set
    const treatmentChoice = document.querySelector('input[name="treatment_plan_choice"]:checked')?.value;
    if (!treatmentChoice) {
        alert('Please select a treatment plan option');
        return;
    }
    
    // Submit form
    const formData = new FormData(this);
    const submitButton = e.submitter;
    
    // IMPORTANT: Add action parameter to URL
    const url = '/dheergayu/app/Controllers/ConsultationFormController.php?action=save_consultation_form';
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        // Try to parse JSON, but if the server returned HTML (PHP error), show it for debugging
        try {
            const data = JSON.parse(text);
            console.log('Server response (json):', data);
            if (data.status === 'success') {
                alert('Consultation saved successfully!');
                window.location.href = 'doctordashboard.php';
            } else {
                alert('Error: ' + (data.message || 'Failed to save'));
                submitButton.disabled = false;
                submitButton.textContent = 'Save';
            }
        } catch (e) {
            console.error('Save error, non-JSON response:', text);
            // Show a truncated HTML/text response so you can inspect the PHP error
            alert('Error saving consultation form: Non-JSON response received. Check console and server logs.\n\n' + text.substring(0, 1000));
            submitButton.disabled = false;
            submitButton.textContent = 'Save';
        }
    })
    .catch(err => {
        console.error('Save error (fetch):', err);
        alert('Error saving consultation form: ' + err.message);
        submitButton.disabled = false;
        submitButton.textContent = 'Save';
    });
});

</script>
</body>
</html>