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
    ]
];

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
    <nav class="navigation">
        <a href="admindashboard.php" class="nav-btn">Home</a>
        <a href="admininventory.php" class="nav-btn">Inventory</a>
        <a href="adminappointment.php" class="nav-btn">Appointments</a>
        <a href="adminusers.php" class="nav-btn active">Users</a>
        <a href="admintreatment.php" class="nav-btn">Treatment Schedule</a>
    </nav>
    <div class="header-right">
        <img src="images/dheergayu.png" class="logo" alt="Logo">
        <h1 class="header-title">Dheergayu</h1>
        <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <!-- Dropdown -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="../patient/login.php" class="logout-btn">Logout</a>
                </div>
            </div> 
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
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
