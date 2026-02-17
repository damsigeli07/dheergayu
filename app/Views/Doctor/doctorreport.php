<?php
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../Patient/login.php');
    exit;
}
?>
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
  <a href="doctordashboard.php?view=treatment-plans" class="nav-btn">Treatment Plans</a>
  <a href="patienthistory.php" class="nav-btn">Patient History</a>
  <a href="doctorreport.php" class="nav-btn active">Reports</a>
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
    <p id="appointmentsCount">Loading...</p>
    <span>Current month</span>
  </div>
  <div class="card yellow">
    <h3>Total Patients</h3>
    <p id="patientsCount">Loading...</p>
    <span>Unique patients this month</span>
  </div>
  <div class="card purple">
    <h3>Today's Income</h3>
    <p id="todayIncome">Loading...</p>
    <span>From completed appointments</span>
  </div>
</section>

      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#000;">Monthly Appointments</h2>
  <div class="chart-wrapper">
    <canvas id="appointmentsChart"></canvas>
  </div>
</section>
      <section class="graph-section" style="margin-top: 40px;">
  <h2 style="text-align:center; color:#000;">Monthly Income</h2>
  <div class="chart-wrapper">
    <canvas id="incomeChart"></canvas>
  </div>
</section>


      </header>

    <!-- Generate Report Button -->
    <div class="button-container">
      <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back to Dashboard</button>
      <button class="generate-btn" onclick="generatePDF()">Generate Report</button>
    </div>
  </div>

  <script>
  // Global variables for charts
  let appointmentsChart;
  let incomeChart;

  // Set to true for sample 30-day charts (set false to use real data only)
  const USE_SAMPLE_CHART_DATA = true;

  // Build sample data for last N days
  function buildSampleLastNDays(days) {
    const data = [];
    const today = new Date();
    for (let i = days - 1; i >= 0; i--) {
      const d = new Date(today);
      d.setDate(today.getDate() - i);
      const iso = d.toISOString().slice(0, 10);
      const count = (i % 7) + 6; // sample 6-12 appointments
      data.push({ date: iso, count });
    }
    return data;
  }

  function buildSampleIncomeFromAppointments(appointments) {
    return appointments.map(item => ({
      date: item.date,
      income: item.count * 2000
    }));
  }

  // Fetch report data from API
  async function fetchReportData() {
    try {
      const response = await fetch('/dheergayu/public/api/get-doctor-report.php');
      const result = await response.json();
      
      if (result.success) {
        const data = result.data;
        
        // Update summary cards
        document.getElementById('appointmentsCount').textContent = data.appointmentsThisMonth;
        document.getElementById('patientsCount').textContent = data.totalPatientsThisMonth;
        document.getElementById('todayIncome').textContent = 'Rs. ' + data.todayIncome.toLocaleString();
        
        // Update charts (use sample 30-day data when enabled)
        if (USE_SAMPLE_CHART_DATA) {
          const sampleAppointments = buildSampleLastNDays(30);
          const sampleIncome = buildSampleIncomeFromAppointments(sampleAppointments);
          updateAppointmentsChart(sampleAppointments);
          updateIncomeChart(sampleIncome);
        } else {
          updateAppointmentsChart(data.monthlyAppointments);
          updateIncomeChart(data.monthlyIncome);
        }
      } else {
        console.error('Error fetching report data:', result.message);
        document.getElementById('appointmentsCount').textContent = 'Error';
        document.getElementById('patientsCount').textContent = 'Error';
        document.getElementById('todayIncome').textContent = 'Error';
      }
    } catch (error) {
      console.error('Error:', error);
      document.getElementById('appointmentsCount').textContent = 'Error';
      document.getElementById('patientsCount').textContent = 'Error';
      document.getElementById('todayIncome').textContent = 'Error';
    }
  }

  // Update appointments chart
  function updateAppointmentsChart(monthlyData) {
    const ctx = document.getElementById('appointmentsChart').getContext('2d');
    
    // Format dates and get counts
    const labels = monthlyData.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const counts = monthlyData.map(item => item.count);
    
    // Destroy existing chart if it exists
    if (appointmentsChart) {
      appointmentsChart.destroy();
    }
    
    appointmentsChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Appointments',
          data: counts,
          backgroundColor: 'rgba(209, 127, 27, 0.8)',
          borderColor: '#d17f1b',
          borderWidth: 2,
          borderRadius: 5,
          hoverBackgroundColor: '#d17f1b',
          hoverBorderColor: '#b86115',
          hoverBorderWidth: 2,
          barPercentage: 0.7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14 },
            bodyFont: { size: 13 }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: false },
            ticks: {
              stepSize: 1,
              maxTicksLimit: 6,
              font: { size: 12 },
              color: '#666'
            },
            grid: {
              color: 'rgba(0,0,0,0.08)',
              drawBorder: false
            }
          },
          x: {
            title: { display: false },
            ticks: {
              autoSkip: true,
              maxTicksLimit: 10,
              font: { size: 12 },
              color: '#666'
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Update income chart
  function updateIncomeChart(incomeData) {
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    
    // Format dates and get income
    const labels = incomeData.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const incomes = incomeData.map(item => item.income);
    
    // Destroy existing chart if it exists
    if (incomeChart) {
      incomeChart.destroy();
    }
    
    incomeChart = new Chart(incomeCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Income (Rs.)',
          data: incomes,
          backgroundColor: 'rgba(209, 127, 27, 0.8)',
          borderColor: '#d17f1b',
          borderWidth: 2,
          borderRadius: 5,
          hoverBackgroundColor: '#d17f1b',
          hoverBorderColor: '#b86115',
          hoverBorderWidth: 2,
          barPercentage: 0.7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14 },
            bodyFont: { size: 13 },
            callbacks: {
              label: function(context) {
                return 'Rs. ' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: false },
            ticks: {
              maxTicksLimit: 6,
              font: { size: 12 },
              color: '#666',
              callback: function(value) {
                return 'Rs. ' + value.toLocaleString();
              }
            },
            grid: {
              color: 'rgba(0,0,0,0.08)',
              drawBorder: false
            }
          },
          x: {
            title: { display: false },
            ticks: {
              autoSkip: true,
              maxTicksLimit: 10,
              font: { size: 12 },
              color: '#666'
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Load data when page loads
  document.addEventListener('DOMContentLoaded', fetchReportData);
</script>

<script>
function generatePDF() {
  window.print();
}
</script>

</body>
</html>
