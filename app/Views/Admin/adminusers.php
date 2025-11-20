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
<body class="has-sidebar">

<!-- Sidebar -->
<header class="header">
    <div class="header-top">
        <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo">
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

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('users')">Staff & Doctors</button>
        <button class="tab-btn" onclick="showTab('patients')">Patients</button>
    </div>

    <!-- Users Tab -->
    <div id="usersTab" class="tab-content active">
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
    </div>

    <!-- Patients Tab -->
    <div id="patientsTab" class="tab-content">
        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>NIC</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody">
                    <!-- Patients will load here dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Tab switching function
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + 'Tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Fetch users from backend
fetch('/dheergayu/app/Controllers/admin_users.php')
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            throw new Error(text || ('HTTP ' + response.status));
        }
        return response.json();
    })
    .then(data => {
        // Separate users and patients
        const users = data.filter(u => u.role?.toLowerCase() !== 'patient');
        const patients = data.filter(u => u.role?.toLowerCase() === 'patient');
        
        // Display users in users tab
        displayUsers(users);
        
        // Display patients in patients tab
        displayPatients(patients);
        
        // Update overview counts
        updateOverviewCounts(users, patients);
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        document.getElementById('userTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Error loading users</td></tr>';
        document.getElementById('patientTableBody').innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading patients</td></tr>';
    });

function displayUsers(users) {
    const tbody = document.getElementById('userTableBody');
    tbody.innerHTML = '';
    
    users.forEach(u => {
        // Capitalize role for display
        const displayRole = u.role?.charAt(0).toUpperCase() + u.role?.slice(1).toLowerCase() || 'Unknown';

        const tr = document.createElement('tr');
        const isActive = (u.status?.toLowerCase() === 'active');
        const statusClass = isActive ? 'active' : 'inactive';
        const buttonText = isActive ? 'Deactivate' : 'Activate';
        const buttonClass = isActive ? 'deactivate-btn' : 'activate-btn';
        
        tr.innerHTML = `
            <td>${u.name || 'N/A'}</td>
            <td>${displayRole}</td>
            <td>${u.email || 'N/A'}</td>
            <td>${u.phone || 'N/A'}</td>
            <td class="status ${statusClass}">${u.status || 'Active'}</td>
            <td>${u.reg_date || '-'}</td>
            <td>
                <button class="${buttonClass}" onclick="toggleUserStatus(${u.id}, '${u.status || 'Active'}')">${buttonText}</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function displayPatients(patients) {
    const tbody = document.getElementById('patientTableBody');
    tbody.innerHTML = '';
    
    patients.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.name || 'N/A'}</td>
            <td>${p.dob || 'N/A'}</td>
            <td>${p.nic || 'N/A'}</td>
            <td>${p.email || 'N/A'}</td>
            <td>${p.reg_date || '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateOverviewCounts(users, patients) {
    let doctors = 0, staff = 0;
    
    users.forEach(u => {
        const role = u.role?.toLowerCase();
        if (role === 'doctor') doctors++;
        else if (['admin', 'staff', 'pharmacist'].includes(role)) staff++;
    });
    
    const total = users.length + patients.length;
    
    document.getElementById('totalUsers').textContent = total;
    document.getElementById('doctorCount').textContent = doctors;
    document.getElementById('patientCount').textContent = patients.length;
    document.getElementById('staffCount').textContent = staff;
}

// Toggle user status (activate/deactivate)
async function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus.toLowerCase() === 'active' ? 'Inactive' : 'Active';
    const action = newStatus === 'Active' ? 'activate' : 'deactivate';
    
    if (!confirm(`Are you sure you want to ${action} this user?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('status', newStatus);
        formData.append('action', 'update_status');
        
        const response = await fetch('/dheergayu/app/Controllers/admin_users.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ User ${action}d successfully`);
            // Reload the page to show updated status
            location.reload();
        } else {
            alert(`❌ Failed to ${action} user: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert(`❌ Failed to ${action} user: ${error.message}`);
    }
}
</script>

</body>
</html>
