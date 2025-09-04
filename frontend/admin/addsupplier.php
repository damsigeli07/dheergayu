<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - Admin Dashboard</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .main-content {
            max-width: 700px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .add-supplier-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            text-align: center;
            color: #8B7355;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-input,
        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus,
        select:focus {
            outline: none;
            border-color: #7a9b57;
            box-shadow: 0 0 0 3px rgba(122, 155, 87, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-submit,
        .btn-cancel {
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-submit {
            background-color: #7a9b57;
            color: white;
        }

        .btn-submit:hover {
            background-color: #6B8E23;
        }

        .btn-cancel {
            background-color: #DC143C;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #B91C3C;
        }

        @media (max-width: 480px) {
            .btn-submit,
            .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="add-supplier-form">
            <h2 class="form-title">Add New Supplier</h2>

            <form action="adminsuppliers.php" method="POST" id="addSupplierForm">
                <div class="form-group">
                    <label for="supplier-name" class="form-label">Supplier Name</label>
                    <input type="text" id="supplier-name" name="supplier_name" class="form-input" required placeholder="Enter supplier name">
                </div>

                <div class="form-group">
                    <label for="contact-person" class="form-label">Contact Person</label>
                    <input type="text" id="contact-person" name="contact_person" class="form-input" required placeholder="Enter contact person">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-input" required placeholder="Enter 10-digit phone number" maxlength="10">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label for="products" class="form-label">Products Supplied</label>
                    <select id="products" name="products[]" class="form-input" multiple required>
                        <option value="Paspanguwa Pack">Paspanguwa Pack</option>
                        <option value="Asamodagam Spirit">Asamodagam Spirit</option>
                        <option value="Siddhalepa Balm">Siddhalepa Balm</option>
                        <option value="Dashamoolarishta">Dashamoolarishta</option>
                        <option value="Kothalahimbutu Capsules">Kothalahimbutu Capsules</option>
                        <option value="Neem Oil">Neem Oil</option>
                        <option value="Herbal Tea">Herbal Tea</option>
                        <option value="Ayurvedic Ointment">Ayurvedic Ointment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" id="address" name="address" class="form-input" required placeholder="Enter address">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Supplier</button>
                    <a href="adminsuppliers.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Phone validation: must be exactly 10 digits
        document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone').value.trim();
            const phonePattern = /^\d{10}$/;
            if (!phonePattern.test(phoneInput)) {
                alert("Phone number must be exactly 10 digits.");
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
