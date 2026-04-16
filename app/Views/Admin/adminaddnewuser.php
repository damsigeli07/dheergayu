<?php require_once __DIR__ . '/../../includes/auth_admin.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/adminaddnewuser.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn">Products</a>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn active">Users</a>
            <a href="adminpatients.php" class="nav-btn">Patients</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminpayments.php" class="nav-btn">Payments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
        </nav>

        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="main-container">
            <h2>Add New User</h2>

        <form id="addUserForm" method="POST" action="/dheergayu/app/Controllers/add_user.php" autocomplete="off">
            <label for="firstName">First Name <span>*</span></label>
            <input type="text" id="firstName" name="first_name" required autocomplete="off" value="">

            <label for="lastName">Last Name <span>*</span></label>
            <input type="text" id="lastName" name="last_name" required autocomplete="off" value="">

            <label for="password">Default Password <span>*</span></label>
            <input type="password" id="password" name="password" required autocomplete="new-password" value="1234">

            <label for="role">Role <span>*</span></label>
            <select id="role" name="role" required autocomplete="off">
                <option value="" disabled selected>-- Select Role --</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Doctor">Doctor</option>
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
            </select>

            <label for="email">Email <span>*</span></label>
            <input type="email" id="email" name="email" required autocomplete="off" placeholder="Select a role to auto-generate" readonly style="background:#f8f8f8;color:#555;cursor:default;">

            <label for="phone">Phone <span>*</span></label>
            <input type="text" id="phone" name="phone" required autocomplete="off" value="">

            <label class="checkbox-label" id="certLabel" style="display:none;">
                <input type="checkbox" id="verified" name="verified">
                Certification is verified
            </label>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Submit</button>
            </div>
        </form>

        <script>
        (function() {
            const roleSelect = document.getElementById('role');
            const emailInput = document.getElementById('email');
            const certLabel = document.getElementById('certLabel');
            const certCheckbox = document.getElementById('verified');

            roleSelect.addEventListener('change', function() {
                const role = this.value.toLowerCase();

                // Show/hide certification checkbox
                if (role === 'doctor' || role === 'pharmacist') {
                    certLabel.style.display = '';
                } else {
                    certLabel.style.display = 'none';
                    certCheckbox.checked = false;
                }

                // Fetch next email suggestion
                fetch('/dheergayu/public/api/next-email.php?role=' + encodeURIComponent(role))
                    .then(r => r.json())
                    .then(data => {
                        if (data.email) emailInput.value = data.email;
                    });
            });

            document.getElementById('addUserForm').addEventListener('submit', function(e) {
                const role = roleSelect.value.toLowerCase();
                if ((role === 'doctor' || role === 'pharmacist') && !certCheckbox.checked) {
                    e.preventDefault();
                    alert('Certification must be verified before adding a Doctor or Pharmacist.');
                }
            });
        })();
        </script>
        </div>
    </main>
</body>
</html>
