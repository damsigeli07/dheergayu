<?php
// Sample data - This should come from a database(Backend)
$appointments = array(
    array(
        'appointment_no' => '100',
        'doctor' => 'Dr. John',
        'patient_ID' => 'P12352',
        'patient_name' => 'Arjun Patel',
        'date_time' => 'March 20, 2026 - 10:00 AM'
    ),
    array(
        'appointment_no' => '101',
        'doctor' => 'Dr. Rohan',
        'patient_ID' => 'P12341',
        'patient_name' => 'Priya Sharma',
        'date_time' => 'March 20, 2026 - 10:15 AM'
    ),
    array(
        'appointment_no' => '102',
        'doctor' => 'Dr. Raj',
        'patient_ID' => 'P12363',
        'patient_name' => 'Ravi Kumar',
        'date_time' => 'March 20, 2026 - 10:30 AM'
    ),
    array(
        'appointment_no' => '103',
        'doctor' => 'Dr. Ilma',
        'patient_ID' => 'P12361',
        'patient_name' => 'Maya Singh',
        'date_time' => 'March 20, 2026 - 10:45 AM'
    ),
    array(
        'appointment_no' => '104',
        'doctor' => 'Dr. Chan',
        'patient_ID' => 'P12368',
        'patient_name' => 'Deepa Nair',
        'date_time' => 'March 20, 2026 - 11:00 AM'
    )
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Ayurvedic System</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffappointment.css?v=1.1">
    <script>
        function searchTable() {
            const input = document.querySelector('.search-input');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.appointments-table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }

        function editAppointment(appointmentNo) {
            alert('Edit appointment ' + appointmentNo + ' functionality would be implemented here');
        }

        function deleteAppointment(appointmentNo) {
            if (confirm('Are you sure you want to delete appointment ' + appointmentNo + '?')) {
                alert('Delete appointment ' + appointmentNo + ' functionality would be implemented here');
            }
        }
    </script>
</head>
<body>
    <!-- Header with ribbon style -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="staffhome.php" class="nav-btn">Home</a>
                <a href="stafftreatment.php" class="nav-btn">Treatment Schedule</a>
                <button class="nav-btn active">Appointment</button>
                <a href="staffhomeTreatmentSuggestion.php" class="nav-btn">Treatment Suggestion</a>
                <a href="staffhomeReports.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Staff</span>
                <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="staffprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
            </div> 

        </div>
    </header>

    <main class="main-content">
        <div class="content">
            <div class="top-section">
                <div class="page-title">
                    <h2>Appointments</h2>
                </div>
                <div class="action-section">
                    <div class="search-section">
                        <input type="text" class="search-input" placeholder="Search" onkeyup="searchTable()">
                        <button class="search-btn" onclick="searchTable()">🔍</button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Appointment No.</th>
                            <th>Doctor</th>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Date and Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['appointment_no']); ?></td>
                            <td class="doctor-name"><?php echo htmlspecialchars($appointment['doctor']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['patient_ID']); ?></td>
                            <td class="patient-name"><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td class="date-time"><?php echo htmlspecialchars($appointment['date_time']); ?></td>
                            <td class="actions">
                                <button class="action-btn edit-btn" onclick="editAppointment('<?php echo $appointment['appointment_no']; ?>')">Edit</button>
                                <button class="action-btn delete-btn" onclick="deleteAppointment('<?php echo $appointment['appointment_no']; ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button class="page-btn">« Previous</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn">Next »</button>
            </div>
        </div>
    </main>
</body>
</html>
