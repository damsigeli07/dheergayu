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
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="patient_first_name">First Name</label>
                                <input type="text" id="patient_first_name" name="patient_first_name" placeholder="Enter first name">
                            </div>
                            <div class="form-group half">
                                <label for="patient_last_name">Last Name</label>
                                <input type="text" id="patient_last_name" name="patient_last_name" placeholder="Enter last name">
                            </div>
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
                                <div class="info-value">P018</div>
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
                    <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back to Dashboard</button>
                    <button type="submit" class="btn btn-secondary">Save</button>
                    <button type="submit" class="btn btn-primary">Send to Pharmacy</button>
                    <button type="submit" class="btn btn-tertiary">Print</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        let row = document.createElement("tr");
        row.innerHTML = `
            <td>${product}</td>
            <td>${qty}</td>
            <td><button type="button" class="remove-btn"> X </button></td>
        `;

        row.querySelector(".remove-btn").addEventListener("click", function() {
            row.remove();
            if (table.children.length === 0) {
                cartTable.style.display = "none"; // hide if empty
            }
        });

        table.appendChild(row);

        document.getElementById("product_search").value = "";
        document.getElementById("product_qty").value = "";
    });

    // Simple form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        let errors = [];

        const patientFirstName = document.getElementById('patient_first_name').value.trim();
        const patientLastName = document.getElementById('patient_last_name').value.trim();
        const age = document.getElementById('age').value.trim();
        const gender = document.getElementById('gender').value;
        const diagnosis = document.getElementById('diagnosis').value.trim();
        const treatment = document.getElementById('recommended_treatment').value.trim();
        const products = document.querySelectorAll('#product_list_table tr');

        if (patientFirstName.length < 2) errors.push("Patient first name must be at least 2 characters.");
        if (patientLastName.length < 2) errors.push("Patient last name must be at least 2 characters.");
        if (age === "" || age <= 0) errors.push("Enter a valid age.");
        if (gender === "") errors.push("Please select gender.");
        if (diagnosis === "") errors.push("Diagnosis is required.");
        if (products.length < 1) errors.push("Add at least one prescribed product.");
        if (treatment === "") errors.push("Recommended treatment is required.");

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        alert("Saved Successfully");

        this.submit();
    });
    </script>
</body>
</html>
