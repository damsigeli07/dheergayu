<?php
// Sample users
$users = [
    [
        'name' => 'Amesh',
        'role' => 'Admin',
        'email' => 'amesh@gmail.com',
        'phone' => '0743402455',
        'status' => 'Active',
        'reg_date' => '19-07-2025'
    ],
    [
        'name' => 'Dr. Nimal',
        'role' => 'Doctor',
        'email' => 'nimal.doctor@gmail.com',
        'phone' => '0712345678',
        'status' => 'Active',
        'reg_date' => '10-06-2025'
    ],
    [
        'name' => 'Saman Perera',
        'role' => 'Patient',
        'email' => 'saman.patient@gmail.com',
        'phone' => '0729876543',
        'status' => 'Active',
        'reg_date' => '05-08-2025'
    ],
    [
        'name' => 'Dr. Sarah',
        'role' => 'Doctor',
        'email' => 'sarah.doctor@gmail.com',
        'phone' => '0711111111',
        'status' => 'Active',
        'reg_date' => '15-06-2025'
    ],
    [
        'name' => 'Priya Silva',
        'role' => 'Patient',
        'email' => 'priya.patient@gmail.com',
        'phone' => '0722222222',
        'status' => 'Active',
        'reg_date' => '20-07-2025'
    ],
    [
        'name' => 'Kamal Perera',
        'role' => 'Pharmacist',
        'email' => 'kamal.pharmacist@gmail.com',
        'phone' => '0733333333',
        'status' => 'Active',
        'reg_date' => '12-06-2025'
    ],
    [
        'name' => 'Nimali Fernando',
        'role' => 'Staff',
        'email' => 'nimali.staff@gmail.com',
        'phone' => '0744444444',
        'status' => 'Active',
        'reg_date' => '08-07-2025'
    ]
];

// Calculate user statistics
$totalUsers = count($users);
$doctors = count(array_filter($users, fn($u) => $u['role'] === 'Doctor'));
$patients = count(array_filter($users, fn($u) => $u['role'] === 'Patient'));
$staff = count(array_filter($users, fn($u) => in_array($u['role'], ['Admin', 'Staff', 'Pharmacist'])));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Users</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" type="text/css" href="css/adminusers.css?v=1.2">
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
        <img src="images/dheergayu.png" class="logo" alt="Logo">
        <h1 class="header-title">Dheergayu</h1>
        <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="adminprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
            </div> 
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <!-- User Overview Cards -->
    <div class="user-overview">
        <div class="overview-card total">
            <div class="overview-icon">👥</div>
            <div class="overview-content">
                <h3>Total Users</h3>
                <div class="overview-number"><?= $totalUsers ?></div>
                <div class="overview-desc">All registered users</div>
            </div>
        </div>
        
        <div class="overview-card staff">
            <div class="overview-icon">👨‍💼</div>
            <div class="overview-content">
                <h3>Staff Members</h3>
                <div class="overview-number"><?= $staff ?></div>
                <div class="overview-desc">Admin, Staff & Pharmacists</div>
            </div>
        </div>
        
        <div class="overview-card doctors">
            <div class="overview-icon">👨‍⚕️</div>
            <div class="overview-content">
                <h3>Doctors</h3>
                <div class="overview-number"><?= $doctors ?></div>
                <div class="overview-desc">Medical professionals</div>
            </div>
        </div>
        
        <div class="overview-card patients">
            <div class="overview-icon">🏥</div>
            <div class="overview-content">
                <h3>Patients</h3>
                <div class="overview-number"><?= $patients ?></div>
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
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td class="status active"><?= htmlspecialchars($u['status']) ?></td>
                    <td><?= htmlspecialchars($u['reg_date']) ?></td>
                    <td>
                        <button class="edit-btn">Edit</button>
                        <button class="del-btn">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="adminaddnewuser.php"><button class="add-btn">+ Add New User</button></a>
    </div>
</main>

</body>
</html>
