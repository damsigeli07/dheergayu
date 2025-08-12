<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Doctor Report</title>
  <link rel="stylesheet" href="css/doctorreport.css?v=1.5" />
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
      <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
      <h1 class="header-title">Dheergayu</h1>
      <div class="user-section">
        <div class="user-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
            <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
          </svg>
        </div>
        <span class="user-role">Doctor</span>
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