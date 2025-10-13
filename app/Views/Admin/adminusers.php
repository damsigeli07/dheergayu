<?php
// frontend/admin/adminusers.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Users</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" type="text/css" href="/dheergayu/public/assets/css/Admin/adminusers.css?v=1.2">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-left">
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn">Products</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn active">Users</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        </nav>
    </div>
    <div class="header-right">
        <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo">
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

<!-- Main Content -->
<main class="main-content">
    <div class="user-overview" id="overview">
        <div class="overview-card total">
            <div class="overview-icon">👥</div>
            <div class="overview-content">
                <h3>Total Users</h3>
                <div class="overview-number" id="totalUsers">0</div>
                <div class="overview-desc">All registered users</div>
            </div>
        </div>
        
        <div class="overview-card staff">
            <div class="overview-icon">👨‍💼</div>
            <div class="overview-content">
                <h3>Staff Members</h3>
                <div class="overview-number" id="staffCount">0</div>
                <div class="overview-desc">Admin, Staff & Pharmacists</div>
            </div>
        </div>
        
        <div class="overview-card doctors">
            <div class="overview-icon">👨‍⚕️</div>
            <div class="overview-content">
                <h3>Doctors</h3>
                <div class="overview-number" id="doctorCount">0</div>
                <div class="overview-desc">Medical professionals</div>
            </div>
        </div>
        
        <div class="overview-card patients">
            <div class="overview-icon">🏥</div>
            <div class="overview-content">
                <h3>Patients</h3>
                <div class="overview-number" id="patientCount">0</div>
                <div class="overview-desc">Registered patients</div>
            </div>
        </div>
    </div>

    <div class="content-box">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Reg Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <!-- Users will load here dynamically -->
            </tbody>
        </table>
        <a href="adminaddnewuser.php"><button class="add-btn">+ Add New User</button></a>
    </div>
</main>

<script>
// Fetch users from backend
fetch('/dheergayu/app/Controllers/admin_users.php')
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            throw new Error(text || ('HTTP ' + response.status));
        }
        return response.json();
    })
    .then(users => {
        const tbody = document.getElementById('userTableBody');
        let total = users.length;
        let doctors = 0, patients = 0, staff = 0;

        users.forEach(u => {
            if (u.role === 'Doctor') doctors++;
            else if (u.role === 'Patient') patients++;
            else if (['Admin', 'Staff', 'Pharmacist'].includes(u.role)) staff++;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${u.name}</td>
                <td>${u.role}</td>
                <td>${u.email}</td>
                <td>${u.phone}</td>
                <td class="status ${u.status?.toLowerCase() || 'active'}">${u.status || 'Active'}</td>
                <td>${u.reg_date || '-'}</td>
                <td>
                    <button class="edit-btn">Edit</button>
                    <button class="del-btn">Delete</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('totalUsers').textContent = total;
        document.getElementById('doctorCount').textContent = doctors;
        document.getElementById('patientCount').textContent = patients;
        document.getElementById('staffCount').textContent = staff;
    })
    .catch(error => {
        console.error('Error fetching users:', error);
        alert('Error loading users from database! ' + (error.message || ''));
    });
</script>

</body>
</html>
