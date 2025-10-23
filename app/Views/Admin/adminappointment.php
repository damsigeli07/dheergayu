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

// Fetch treatments for treatment schedule tab
$treatmentQuery = "SELECT * FROM treatments WHERE treatment_type != 'General Consultation' ORDER BY appointment_date DESC, appointment_time DESC";
$treatmentResult = $db->query($treatmentQuery);

$treatments = [];
if ($treatmentResult && $treatmentResult->num_rows > 0) {
    while ($row = $treatmentResult->fetch_assoc()) {
        $treatments[] = [
            'id' => $row['id'],
            'patient_id' => $row['patient_id'],
            'patient_name' => $row['patient_name'],
            'treatment_type' => $row['treatment_type'],
            'appointment_date' => $row['appointment_date'],
            'appointment_time' => $row['appointment_time'],
            'status' => $row['status'],
            'treatment_fee' => $row['treatment_fee'],
            'duration' => $row['duration']
        ];
    }
}

function getStatusClass($status) {
    switch(strtolower($status)) {
        case 'completed':
            return 'status-completed';
        case 'in progress':
            return 'status-in-progress';
        case 'pending':
            return 'status-pending';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
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

    /* Tab Navigation Styles */
    .tab-navigation {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #666;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        color: #1976D2;
        background-color: #f5f5f5;
    }

    .tab-btn.active {
        color: #1976D2;
        border-bottom-color: #1976D2;
        background-color: #f8f9fa;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Treatment Table Styles */
    .treatment-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .treatment-table th,
    .treatment-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .treatment-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }

    .treatment-table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .action-btn {
        background: #5d9b57;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
    }
    
    .action-btn:hover {
        background: #4a7c47;
    }
    
    .complete-btn {
        background: #28a745;
    }
    
    .complete-btn:hover {
        background: #218838;
    }
    
    .completed-text {
        color: #28a745;
        font-weight: 500;
        font-size: 12px;
    }
    
    .status-text {
        color: #6c757d;
        font-size: 12px;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-in-progress {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-pending {
        background-color: #cce5ff;
        color: #004085;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }
  </style>
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

    function searchTable() {
        const input = document.querySelector('#appointmentsTab .search-input');
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

    function searchTreatmentTable() {
        const input = document.querySelector('#treatmentsTab .search-input');
        const searchTerm = input.value.toLowerCase();
        const rows = document.querySelectorAll('.treatment-table tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let found = false;
            
            for (let i = 0; i < cells.length; i++) {
                if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                    break;
                }
            }
            
            row.style.display = found ? '' : 'none';
        });
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
    <!-- Tab Navigation -->
    <div class="tab-navigation">
      <button class="tab-btn active" onclick="showTab('appointments')">Appointments</button>
      <button class="tab-btn" onclick="showTab('treatments')">Treatment Schedule</button>
    </div>

    <!-- Appointments Tab -->
    <div id="appointmentsTab" class="tab-content active">
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

    <!-- Treatment Schedule Tab -->
    <div id="treatmentsTab" class="tab-content">
      <div class="content">
        <div class="top-section">
          <div class="page-title"></div>
          <div class="action-section">
            <div class="search-section">
              <input type="text" class="search-input" placeholder="Search treatments" onkeyup="searchTreatmentTable()">
              <button class="search-btn" onclick="searchTreatmentTable()">🔍</button>
            </div>
          </div>
        </div>

        <div class="table-container">
          <table class="treatment-table">
            <thead>
              <tr>
                <th>Patient ID</th>
                <th>Patient Name</th>
                <th>Treatment Type</th>
                <th>Date and Time</th>
                <th>Duration</th>
                <th>Fee</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($treatments)): ?>
                <?php foreach ($treatments as $treatment): ?>
                <tr>
                  <td><?php echo htmlspecialchars($treatment['patient_id']); ?></td>
                  <td><?php echo htmlspecialchars($treatment['patient_name']); ?></td>
                  <td><?php echo htmlspecialchars($treatment['treatment_type']); ?></td>
                  <td><?php echo htmlspecialchars($treatment['appointment_date'] . ' - ' . $treatment['appointment_time']); ?></td>
                  <td><?php echo htmlspecialchars($treatment['duration'] . ' min'); ?></td>
                  <td>Rs. <?php echo number_format($treatment['treatment_fee'], 2); ?></td>
                  <td>
                    <span class="status-badge <?php echo getStatusClass($treatment['status']); ?>">
                      <?php echo htmlspecialchars($treatment['status']); ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align: center; padding: 20px;">No treatments found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
