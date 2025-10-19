<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Consultation Form</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctorconsultform.css">
</head>
<body>
<?php
require_once __DIR__ . '/../../Controllers/ConsultationFormController.php';
?>
    <div class="container">
        <header class="header">
            <h1>DOCTOR'S CONSULTATION FORM</h1>
        </header>

        <div class="form-container">
            <form method="POST" action="/dheergayu/app/Controllers/ConsultationFormController.php">
                <div class="main-content">
                    <div class="left-section">
                        <h2>Consultation Form</h2>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" placeholder="Enter first name" value="<?= isset($appointment['patient_name']) ? htmlspecialchars(explode(' ', $appointment['patient_name'])[0]) : '' ?>">
                            </div>
                            <div class="form-group half">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" placeholder="Enter last name" value="<?= isset($appointment['patient_name']) ? htmlspecialchars(explode(' ', $appointment['patient_name'])[1] ?? '') : '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label for="age">Age</label>
                                <input type="number" id="age" name="age" placeholder="Enter age" value="<?= isset($appointment['age']) ? htmlspecialchars($appointment['age']) : '' ?>">
                            </div>
                            <div class="form-group half">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select gender</option>
                                    <option value="male" <?= (isset($appointment['gender']) && strtolower($appointment['gender'])=='male') ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= (isset($appointment['gender']) && strtolower($appointment['gender'])=='female') ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= (isset($appointment['gender']) && strtolower($appointment['gender'])=='other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diagnosis">Diagnosis</label>
                            <textarea id="diagnosis" name="diagnosis" placeholder="Enter diagnosis..."></textarea>
                        </div>

                        <!-- Prescribed Products -->
                        <div class="form-group">
                            <label for="product_search">Prescribed Products</label>
                            
                            <div class="product-selector">
                                <input type="text" id="product_search" placeholder="Search product..." list="product_list">
                                <datalist id="product_list">
                                    <option value="Neem Oil (Av: 45 bottles)">
                                    <option value="Triphala Tablets (Av: 100 bottles)">
                                    <option value="Ashwagandha Powder (Av: 25 packets)">
                                    <option value="Brahmi Powder (Av: 12 packets)">
                                    <option value="Chyawanprash (Av: 8 jars)">
                                    <option value="Nirgundi Oil (Av: 120 bottles)">
                                    <option value="Oinda Thailaya (Av: 80 bottles)">
                                    <option value="Herbal Pain Oil (Av: 67 bottles)">
                                    <option value="Steam Hurbs (Av: 276 packets)">                
                                </datalist>
                                
                                <input type="number" id="product_qty" min="1" placeholder="Qty">
                                <button type="button" id="add_product">Add</button>
                            </div>

                            <!-- Hidden field to submit selected products -->
                            <input type="hidden" id="personal_products" name="personal_products" value="[]">

                            <!-- Hidden initially -->
                            <table class="product-table" id="cart_table" style="display:none;">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="product_list_table"></tbody>
                            </table>
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
                                <div class="info-value">
                                    <input type="text" name="patient_no" value="<?= isset($appointment['patient_no']) ? htmlspecialchars($appointment['patient_no']) : '' ?>" readonly>
                                </div>
                            </div>

                            <div class="info-group">
                                <strong>Previous Visits:</strong>
                                <div class="info-value">
                                    <input type="date" name="last_visit_date" value="<?= isset($appointment['last_visit_date']) ? htmlspecialchars($appointment['last_visit_date']) : '' ?>">
                                </div>
                                <div class="info-value">
                                    <input type="number" name="total_visits" value="<?= isset($appointment['total_visits']) ? htmlspecialchars($appointment['total_visits']) : '0' ?>">
                                </div>
                            </div>

                            <div class="info-group">
                                <strong>Contact Info:</strong>
                                <div class="info-value">
                                    <input type="text" name="contact_info" value="<?= isset($appointment['contact_info']) ? htmlspecialchars($appointment['contact_info']) : '' ?>">
                                </div>
                <input type="hidden" name="appointment_id" value="<?= isset($appointment['appointment_id']) ? htmlspecialchars($appointment['appointment_id']) : '' ?>">
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
                    <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back to Dashboard</button>
                    <button type="submit" class="btn btn-secondary">Save</button>
                    <button type="submit" class="btn btn-primary">Send to Pharmacy</button>
                    <button type="submit" class="btn btn-tertiary">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Maintain selected products as an array and sync to hidden input
    var selectedProducts = [];
    function syncProductsField() {
        try {
            document.getElementById('personal_products').value = JSON.stringify(selectedProducts);
        } catch (e) {
            document.getElementById('personal_products').value = '[]';
        }
    }

    // Add products to cart table
    document.getElementById("add_product").addEventListener("click", function() {
        let product = document.getElementById("product_search").value.trim();
        let qty = document.getElementById("product_qty").value.trim();

        if (product === "" || qty === "" || qty <= 0) {
            alert("Please enter product and valid quantity.");
            return;
        }

        let table = document.getElementById("product_list_table");
        let cartTable = document.getElementById("cart_table");

        // Show table if first item
        cartTable.style.display = "table";

        // Push into array and render row
        var item = { product: product, qty: parseInt(qty, 10) };
        selectedProducts.push(item);
        syncProductsField();

        let row = document.createElement("tr");
        row.innerHTML = `
            <td>${product}</td>
            <td>${qty}</td>
            <td><button type="button" class="remove-btn"> X </button></td>
        `;

        row.querySelector(".remove-btn").addEventListener("click", function() {
            // Remove from array
            selectedProducts = selectedProducts.filter(function(p){ return !(p.product === product && p.qty === parseInt(qty,10)); });
            syncProductsField();
            // Remove row
            row.remove();
            if (table.children.length === 0) {
                cartTable.style.display = "none"; // hide if empty
            }
        });

        table.appendChild(row);

        document.getElementById("product_search").value = "";
        document.getElementById("product_qty").value = "";
    });

    // Simple form validation + AJAX submit with success dialog + redirect
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        let errors = [];

        const patientFirstName = document.getElementById('first_name').value.trim();
        const patientLastName = document.getElementById('last_name').value.trim();
        const age = document.getElementById('age').value.trim();
        const gender = document.getElementById('gender').value;
        const diagnosis = document.getElementById('diagnosis').value.trim();
        const treatment = document.getElementById('recommended_treatment').value.trim();
        // ensure latest sync
        syncProductsField();
        const products = selectedProducts;

        if (patientFirstName.length < 2) errors.push("Patient first name must be at least 2 characters.");
        if (patientLastName.length < 2) errors.push("Patient last name must be at least 2 characters.");
        if (age === "" || age <= 0) errors.push("Enter a valid age.");
        if (gender === "") errors.push("Please select gender.");
        if (diagnosis === "") errors.push("Diagnosis is required.");
        if (!products || products.length < 1) errors.push("Add at least one prescribed product.");
        if (treatment === "") errors.push("Recommended treatment is required.");

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        // Build FormData and submit via fetch to get JSON response
        var formEl = this;
        var formData = new FormData(formEl);
        fetch('/dheergayu/app/Controllers/ConsultationFormController.php', {
            method: 'POST',
            body: formData
        }).then(function(res){ return res.json(); })
        .then(function(resp){
            if (resp && resp.status === 'success') {
                showSuccessDialog('Saved successfully! Redirecting to dashboard...');
                setTimeout(function(){ window.location.href = 'doctordashboard.php'; }, 1200);
            } else {
                alert('Error saving consultation form.');
            }
        }).catch(function(){
            alert('Network error while saving consultation form.');
        });
    });

    function showSuccessDialog(message) {
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.background = 'rgba(0,0,0,0.3)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '9999';
        overlay.innerHTML = `
            <div style="background:#ffffff;padding:24px 28px;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,0.2);text-align:center;max-width:380px;">
                <div style="font-size:18px;color:#2e7d32;margin-bottom:10px;font-weight:600;">Success</div>
                <div style="font-size:14px;color:#333;margin-bottom:16px;">${message}</div>
                <button id="success-ok" style="background:#43a047;color:#fff;border:none;border-radius:6px;padding:8px 16px;font-size:14px;cursor:pointer;">OK</button>
            </div>`;
        document.body.appendChild(overlay);
        document.getElementById('success-ok').addEventListener('click', function(){ window.location.href = 'doctordashboard.php'; });
    }
    </script>
</body>
</html>
