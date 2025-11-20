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
<body class="has-sidebar">
  <!-- Sidebar -->
  <header class="header">
    <div class="header-top">
      <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo" />
      <h1 class="header-title">Dheergayu</h1>
    </div>
    
    <nav class="navigation">
      <a href="doctordashboard.php" class="nav-btn">Appointments</a>
      <a href="patienthistory.php" class="nav-btn">Patient History</a>
      <button class="nav-btn active">Reports</button>
    </nav>
    
    <div class="user-section">
      <div class="user-icon" id="user-icon">👤</div>
      <span class="user-role">Doctor</span>
      <!-- Dropdown -->
      <div class="user-dropdown" id="user-dropdown">
        <a href="doctorprofile.php" class="profile-btn">Profile</a>
        <a href="../patient/login.php" class="logout-btn">Logout</a>
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
    <span>Up to October 24</span>
  </div>
  <div class="card yellow">
    <h3>Total Patients</h3>
    <p>230</p>
    <span>Unique patients this month</span>
  </div>
  <div class="card purple">
    <h3>Today's Income</h3>
    <p>Rs. 9,050</p>
    <span>From completed appointments</span>
  </div>
</section>

      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#000;">Monthly Appointments</h2>
  <canvas id="appointmentsChart" style="max-width:100%; height:250px;"></canvas>
</section>
      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#000;">Monthly Income</h2>
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
      labels: ['Oct 15', 'Oct 16', 'Oct 17', 'Oct 18', 'Oct 19', 'Oct 20', 'Oct 21', 'Oct 22', 'Oct 23', 'Oct 24'],
      datasets: [{
      label: 'Appointments',
      data: [18, 22, 15, 20, 19, 25, 21, 23, 17, 9],
      backgroundColor: '#d17f1b',
      borderColor: '#b86115',
      borderWidth: 1,
      hoverBackgroundColor: '#d17f1b',
      hoverBorderColor: '#9f5a12',
      hoverBorderWidth: 2,
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
    labels: ['Oct 15', 'Oct 16', 'Oct 17', 'Oct 18', 'Oct 19', 'Oct 20', 'Oct 21', 'Oct 22', 'Oct 23', 'Oct 24'],
    datasets: [{
      label: 'Income (Rs.)',
      data: [67500, 83000, 50500, 72750, 70800, 110200, 72900, 83500, 61900, 9050],
      backgroundColor: '#d17f1b',
      borderColor: '#b86115',
      borderWidth: 1,
      hoverBackgroundColor: '#d17f1b',
      hoverBorderColor: '#9f5a12',
      hoverBorderWidth: 2,
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
