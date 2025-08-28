<?php
$appointments = [
    ['appointment_no'=>'100','doctor'=>'Dr. John','patient_no'=>'1008','patient_name'=>'Ravi Kumar','date_time'=>'March 20, 2026 - 10:00 AM'],
    ['appointment_no'=>'101','doctor'=>'Dr. Rohan','patient_no'=>'1002','patient_name'=>'Priya Sharma','date_time'=>'March 20, 2026 - 10:15 AM'],
    ['appointment_no'=>'102','doctor'=>'Dr. Raj', 'patient_no'=>'1005','patient_name'=>'Arjun Patel','date_time'=>'March 20, 2026 - 10:30 AM'],
    ['appointment_no'=>'103','doctor'=>'Dr. Ilma','patient_no'=>'1006','patient_name'=>'Maya Singh','date_time'=>'March 20, 2026 - 10:45 AM'],
    ['appointment_no'=>'104','doctor'=>'Dr. Chan','patient_no'=>'1012','patient_name'=>'Deepa Nair','date_time'=>'March 20, 2026 - 11:00 AM']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Appointments - Administrative</title>
  <link rel="stylesheet" href="../css_common/header.css">
  <script src="../js_common/header.js"></script>
  <link rel="stylesheet" href="css/adminappointment.css?v=1.1" />
</head>
<body>

  <!-- Unified Header (same as dashboard) -->
  <header class="header">
    <nav class="navigation">
      <a href="admindashboard.php" class="nav-btn">Home</a>
      <a href="admininventory.php" class="nav-btn">Inventory</a>
      <button class="nav-btn active">Appointments</button>
      <a href="adminusers.php" class="nav-btn">Users</a>
      <a href="admintreatment.php" class="nav-btn">Treatment Schedule</a>
    </nav>
    <div class="header-right">
      <img src="../staff/images/dheergayu.png" class="logo" alt="Logo" />
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
              <th>Appointment No.</th>
              <th>Doctor</th>
              <th>Patient No.</th>
              <th>Patient Name</th>
              <th>Date and Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($appointments as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['appointment_no']) ?></td>
            <td class="doctor-name"><?= htmlspecialchars($a['doctor']) ?></td>
            <td><?= htmlspecialchars($a['patient_no']) ?></td>
            <td class="patient-name"><?= htmlspecialchars($a['patient_name']) ?></td>
            <td class="date-time"><?= htmlspecialchars($a['date_time']) ?></td>
            <td class="actions">
              <button class="action-btn reschedule-btn" onclick="rescheduleAppointment('<?= $a['appointment_no'] ?>')">Reschedule</button>
              <button class="action-btn edit-btn" onclick="editAppointment('<?= $a['appointment_no'] ?>')">Edit</button>
              <button class="action-btn delete-btn" onclick="deleteAppointment('<?= $a['appointment_no'] ?>')">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
