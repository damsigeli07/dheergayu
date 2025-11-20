<?php
// Dummy data for room allocation
$rooms = [
    [
        'room_number' => 1,
        'treatment_type' => 'Udwarthana',
        'staff' => [
            ['name' => 'M.H.Gunarathne', 'role' => 'Therapist'],
            ['name' => 'K.A.Perera', 'role' => 'Assistant'],
            ['name' => 'S.M.Wickramasinghe', 'role' => 'Therapist'],
            ['name' => 'N.D.Fernando', 'role' => 'Assistant'],
            ['name' => 'R.P.Jayawardena', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 2,
        'treatment_type' => 'Nasya Karma',
        'staff' => [
            ['name' => 'A.B.Silva', 'role' => 'Therapist'],
            ['name' => 'C.D.Mendis', 'role' => 'Assistant'],
            ['name' => 'E.F.Ratnayake', 'role' => 'Therapist'],
            ['name' => 'G.H.Amarasinghe', 'role' => 'Assistant'],
            ['name' => 'I.J.Karunaratne', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 3,
        'treatment_type' => 'Shirodhara',
        'staff' => [
            ['name' => 'L.M.Dissanayake', 'role' => 'Therapist'],
            ['name' => 'N.O.Peiris', 'role' => 'Assistant'],
            ['name' => 'P.Q.Rajapaksa', 'role' => 'Therapist'],
            ['name' => 'R.S.Tennakoon', 'role' => 'Assistant'],
            ['name' => 'T.U.Vithanage', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 4,
        'treatment_type' => 'Basti',
        'staff' => [
            ['name' => 'W.X.Yapa', 'role' => 'Therapist'],
            ['name' => 'Z.A.Bandara', 'role' => 'Assistant'],
            ['name' => 'B.C.Dharmasena', 'role' => 'Therapist'],
            ['name' => 'D.E.Fonseka', 'role' => 'Assistant'],
            ['name' => 'F.G.Gunasekara', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 5,
        'treatment_type' => 'Panchakarma Detox',
        'staff' => [
            ['name' => 'H.I.Jayasinghe', 'role' => 'Therapist'],
            ['name' => 'K.L.Munasinghe', 'role' => 'Assistant'],
            ['name' => 'M.N.Opatha', 'role' => 'Therapist'],
            ['name' => 'O.P.Qadir', 'role' => 'Assistant'],
            ['name' => 'Q.R.Seneviratne', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 6,
        'treatment_type' => 'Vashpa Sweda',
        'staff' => [
            ['name' => 'S.T.Udawatta', 'role' => 'Therapist'],
            ['name' => 'U.V.Wijesekera', 'role' => 'Assistant'],
            ['name' => 'W.X.Yapa', 'role' => 'Therapist'],
            ['name' => 'Y.Z.Abeysekera', 'role' => 'Assistant'],
            ['name' => 'A.B.Cooray', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 7,
        'treatment_type' => 'Abhyanga Massage',
        'staff' => [
            ['name' => 'C.D.Edirisinghe', 'role' => 'Therapist'],
            ['name' => 'E.F.Gunawardena', 'role' => 'Assistant'],
            ['name' => 'G.H.Ihalagama', 'role' => 'Therapist'],
            ['name' => 'I.J.Kulatunga', 'role' => 'Assistant'],
            ['name' => 'K.L.Mahinda', 'role' => 'Therapist']
        ]
    ],
    [
        'room_number' => 8,
        'treatment_type' => 'Elakizhi',
        'staff' => [
            ['name' => 'M.N.Nanayakkara', 'role' => 'Therapist'],
            ['name' => 'O.P.Premaratne', 'role' => 'Assistant'],
            ['name' => 'Q.R.Ranasinghe', 'role' => 'Therapist'],
            ['name' => 'S.T.Samarasinghe', 'role' => 'Assistant'],
            ['name' => 'U.V.Thilakarathne', 'role' => 'Therapist']
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Allocation - Staff Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffroomallocation.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="staffhome.php" class="nav-btn">Home</a>
            <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
            <a href="staffappointment.php" class="nav-btn">Appointment</a>
            <a href="staffhomeReports.php" class="nav-btn">Reports</a>
            <button class="nav-btn active">Room Allocation</button>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Staff</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="staffprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Room Allocation</h1>
            <p class="page-subtitle">View staff allocation for treatment rooms</p>
        </div>

        <div class="rooms-container">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-header">
                        <h2 class="room-title">Room <?= $room['room_number'] ?></h2>
                        <span class="treatment-badge"><?= $room['treatment_type'] ?></span>
                    </div>
                    
                    <div class="staff-list">
                        <h3 class="staff-list-title">Allocated Staff (<?= count($room['staff']) ?>)</h3>
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($room['staff'] as $staff): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($staff['name']) ?></td>
                                        <td>
                                            <span class="role-badge role-<?= strtolower($staff['role']) ?>">
                                                <?= htmlspecialchars($staff['role']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>

