<!DOCTYPE html>
require_once __DIR__ . '/../../includes/auth_admin.php';
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Patients</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/adminusers.css?v=1.0">
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
        <a href="adminusers.php" class="nav-btn">Users</a>
        <button class="nav-btn active">Patients</button>
        <a href="admintreatment.php" class="nav-btn">Treatments</a>
        <a href="adminpayments.php" class="nav-btn">Payments</a>
        <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
            <a href="adminreports.php" class="nav-btn">Reports</a>
        </nav>

    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role">Admin</span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="adminprofile.php" class="profile-btn">Profile</a>
            <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <div class="user-overview" id="overview">
        <div class="overview-card total">
            <div class="overview-icon">👥</div>
            <div class="overview-content">
                <h3>Total Patients</h3>
                <div class="overview-number" id="totalCount">0</div>
                <div class="overview-desc">All registered patients</div>
            </div>
        </div>

        <div class="overview-card staff">
            <div class="overview-icon">✅</div>
            <div class="overview-content">
                <h3>Active</h3>
                <div class="overview-number" id="activeCount">0</div>
                <div class="overview-desc">Active accounts</div>
            </div>
        </div>

        <div class="overview-card doctors">
            <div class="overview-icon">🚫</div>
            <div class="overview-content">
                <h3>Deactivated</h3>
                <div class="overview-number" id="deactivatedCount">0</div>
                <div class="overview-desc">Deactivated accounts</div>
            </div>
        </div>

        <div class="overview-card patients">
            <div class="overview-icon">🆕</div>
            <div class="overview-content">
                <h3>New This Month</h3>
                <div class="overview-number" id="newCount">0</div>
                <div class="overview-desc">Registered this month</div>
            </div>
        </div>
    </div>

    <div class="content-box">
        <table>
            <thead>
                <tr>
                    <th>Patient #</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>NIC</th>
                    <th>Date of Birth</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Reg Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="patientTableBody">
                <!-- Loaded dynamically -->
            </tbody>
        </table>
    </div>
</main>

<script>
fetch('/dheergayu/app/Controllers/admin_patients.php')
    .then(async response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        displayPatients(data);
        updateCounts(data);
    })
    .catch(error => {
        document.getElementById('patientTableBody').innerHTML =
            '<tr><td colspan="9" style="text-align:center;color:red;">Error loading patients</td></tr>';
    });

function displayPatients(patients) {
    const tbody = document.getElementById('patientTableBody');
    tbody.innerHTML = '';

    if (patients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No patients found.</td></tr>';
        return;
    }

    patients.forEach(p => {
        const isActive = p.account_status === 'active';
        const statusClass = isActive ? 'active' : 'inactive';
        const statusLabel = isActive ? 'Active' : 'Deactivated';
        const buttonText = isActive ? 'Deactivate' : 'Activate';
        const buttonClass = isActive ? 'deactivate-btn' : 'activate-btn';
        const newStatus = isActive ? 'deactivated' : 'active';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.patient_number || '-'}</td>
            <td>${p.name || 'N/A'}</td>
            <td>${p.email || 'N/A'}</td>
            <td>${p.nic || '-'}</td>
            <td>${p.dob || '-'}</td>
            <td>${p.phone || '-'}</td>
            <td><span class="status ${statusClass}">${statusLabel}</span></td>
            <td>${p.reg_date ? p.reg_date.split(' ')[0] : '-'}</td>
            <td>
                <button class="${buttonClass}" onclick="toggleStatus(${p.id}, '${newStatus}', this)">
                    ${buttonText}
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function updateCounts(patients) {
    const now = new Date();
    const thisMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');

    let active = 0, deactivated = 0, newThisMonth = 0;
    patients.forEach(p => {
        if (p.account_status === 'active') active++;
        else deactivated++;
        if (p.reg_date && p.reg_date.startsWith(thisMonth)) newThisMonth++;
    });

    document.getElementById('totalCount').textContent = patients.length;
    document.getElementById('activeCount').textContent = active;
    document.getElementById('deactivatedCount').textContent = deactivated;
    document.getElementById('newCount').textContent = newThisMonth;
}

async function toggleStatus(patientId, newStatus, btn) {
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    if (!confirm(`Are you sure you want to ${action} this patient?`)) return;

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('patient_id', patientId);
    formData.append('status', newStatus);

    try {
        const response = await fetch('/dheergayu/app/Controllers/admin_patients.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Failed: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

</body>
</html>
