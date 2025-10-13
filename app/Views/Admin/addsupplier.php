<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
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
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <a href="admininventory.php" class="nav-btn">Products</a>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatments</a>
                <a href="adminsuppliers.php" class="nav-btn active">Supplier Info</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="adminprofile.php" class="profile-btn">Profile</a>
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

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
