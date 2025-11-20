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
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
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

            <label for="password">Password <span>*</span></label>
            <input type="password" id="password" name="password" required autocomplete="new-password" value="">

            <label for="email">Email <span>*</span></label>
            <input type="email" id="email" name="email" required autocomplete="off" value="">

            <label for="phone">Phone <span>*</span></label>
            <input type="text" id="phone" name="phone" required autocomplete="off" value="">

            <label for="role">Role <span>*</span></label>
            <select id="role" name="role" required autocomplete="off">
                <option value="" disabled selected>-- Select Role --</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Doctor">Doctor</option>
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
            </select>

            <label class="checkbox-label">
                <input type="checkbox" id="verified" name="verified" required>
                Certification is verified
            </label>

            <div class="form-buttons">
                <button type="button" onclick="window.history.back();" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Submit</button>
            </div>
        </form>
        </div>
    </main>
</body>
</html>
