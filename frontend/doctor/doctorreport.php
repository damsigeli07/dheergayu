<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Doctor Report</title>
  <link rel="stylesheet" href="../css_common/header.css">
  <script src="../js_common/header.js"></script>
  <link rel="stylesheet" href="css/doctorreport.css" />
</head>
<body>
  <header class="header">
    <div class="header-left">
      <nav class="navigation">
        <a href="doctordashboard.php" class="nav-btn">Appointments</a>
        <a href="patienthistory.php" class="nav-btn">Patient History</a>
        <button class="nav-btn active">Reports</button>
      </nav>
    </div>

    <div class="header-right">
      <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo" />
      <h1 class="header-title">Dheergayu</h1>
      <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Doctor</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
    </div>
    </div>
  </header>

  <div class="report-container">
    <header>
      <h1>Doctor Report</h1>
      <p>Report generated on August 10, 2025</p>
    </header>

    <!-- Summary Cards -->
    <section class="summary-grid">
      <div class="card green">
        <h3>Appointments This Month</h3>
        <p>185</p>
        <span>Up to August 10</span>
      </div>
      <div class="card yellow">
        <h3>Total Patients</h3>
        <p>230</p>
        <span>Unique patients this month</span>
      </div>
      <div class="card purple">
        <h3>Today's Income</h3>
        <p>Rs. 15,750</p>
        <span>From completed appointments</span>
      </div>
    </section>

    <!-- Generate Report Button -->
    <div class="button-container">
      <button class="generate-btn">Generate Report</button>
    </div>
  </div>
</body>
</html>
