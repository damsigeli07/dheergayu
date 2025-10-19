<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Doctor Report</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctorreport.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
      <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo" />
      <h1 class="header-title">Dheergayu</h1>
      <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Doctor</span>
            <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="doctorprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </div>
    </div>
  </header>

  <div class="report-container">

    <header>
      <h1>Doctor Report</h1>

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

      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#8B7355;">Monthly Appointments</h2>
  <canvas id="appointmentsChart" style="max-width:100%; height:250px;"></canvas>
</section>
      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#8B7355;">Monthly Income</h2>
  <canvas id="incomeChart" style="max-width:100%; height:250px;"></canvas>
</section>


      </header>

    <!-- Generate Report Button -->
    <div class="button-container">
      <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back to Dashboard</button>
      <button class="generate-btn" onclick="generatePDF()">Generate Report</button>
    </div>
  </div>

  <script>
  const ctx = document.getElementById('appointmentsChart').getContext('2d');

  const appointmentsChart = new Chart(ctx, {
    type: 'bar', // Bar chart
    data: {
      labels: ['Aug 1', 'Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7', 'Aug 8', 'Aug 9', 'Aug 10'],
      datasets: [{
        label: 'Appointments',
        data: [18, 22, 15, 20, 19, 25, 21, 23, 17, 30],
        backgroundColor: '#84a939ff',
        barPercentage: 0.6
    }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Number of Appointments' }
        },
        x: {
          title: { display: true, text: 'Date' }
        }
      }
    }
  });

  const incomeCtx = document.getElementById('incomeChart').getContext('2d');

const incomeChart = new Chart(incomeCtx, {
  type: 'bar',
  data: {
    labels: ['Aug 1', 'Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7', 'Aug 8', 'Aug 9', 'Aug 10'],
    datasets: [{
      label: 'Income (Rs.)',
      data: [10000, 13000, 10500, 12750, 11100, 14200, 12900, 13500, 11900, 15750],
      backgroundColor: '#84a939ff',
      barPercentage: 0.6
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Income (Rs.)' }
      },
      x: {
        title: { display: true, text: 'Date' }
      }
    }
  }
});

</script>

<script>
function generatePDF() {
  window.print();
}
</script>

</body>
</html>
