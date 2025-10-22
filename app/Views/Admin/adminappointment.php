<?php
// Fetch appointments from database
require_once __DIR__ . '/../../Models/AppointmentModel.php';

$db = new mysqli('localhost', 'root', '', 'dheergayu_db');
$appointmentModel = new AppointmentModel($db);
$appointments = $appointmentModel->getAllDoctorAppointments();

// If no appointments found, use empty array
if (!$appointments) {
    $appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Appointments - Administrative</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/adminappointment.css?v=1.1" />
  <style>
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-badge.completed {
        background-color: #d4edda;
        color: #155724;
    }
    .status-badge.cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }
  </style>
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
  </script>
</head>
<body>

  <!-- Unified Header (same as dashboard) -->
  <header class="header">
    <div class="header-left">
      <nav class="navigation">
        <a href="admindashboard.php" class="nav-btn">Home</a>
        <a href="admininventory.php" class="nav-btn">Products</a>
        <a href="admininventoryview.php" class="nav-btn">Inventory</a>
        <button class="nav-btn active">Appointments</button>
        <a href="adminusers.php" class="nav-btn">Users</a>
        <a href="admintreatment.php" class="nav-btn">Treatments</a>
        <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
      </nav>
    </div>
    <div class="header-right">
      <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
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

  <div class="container">

    <div class="content">
      <div class="top-section">
        <div class="page-title"></div>
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
              <th>Appointment ID</th>
              <th>Patient No.</th>
              <th>Patient Name</th>
              <th>Date and Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($appointments)): ?>
            <?php foreach($appointments as $appointment): ?>
            <tr>
              <td><?= htmlspecialchars($appointment['appointment_id'] ?? '') ?></td>
              <td><?= htmlspecialchars($appointment['patient_no'] ?? '') ?></td>
              <td class="patient-name"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></td>
              <td class="date-time"><?= htmlspecialchars($appointment['appointment_datetime'] ?? '') ?></td>
              <td>
                <span class="status-badge <?= strtolower($appointment['status'] ?? '') ?>">
                  <?= htmlspecialchars($appointment['status'] ?? '') ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 20px;">No appointments found.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
