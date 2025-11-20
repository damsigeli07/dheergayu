<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Details</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/appointmentview.css">
</head>
<body class="has-sidebar">
  <!-- Sidebar -->
  <header class="header">
    <div class="header-top">
      <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
      <h1 class="header-title">Dheergayu</h1>
    </div>
    
    <nav class="navigation">
      <a href="doctordashboard.php" class="nav-btn">Appointments</a>
      <a href="patienthistory.php" class="nav-btn">Patient History</a>
      <a href="doctorreport.php" class="nav-btn">Reports</a>
    </nav>
    
    <div class="user-section">
      <div class="user-icon" id="user-icon">👤</div>
      <span class="user-role">Doctor</span>
      <div class="user-dropdown" id="user-dropdown">
        <a href="doctorprofile.php" class="profile-btn">Profile</a>
        <a href="../patient/login.php" class="logout-btn">Logout</a>
      </div>
    </div>
  </header>
  <div class="container">
    <h1>Patient Details</h1>

    <div class="card">
      <h2>👤 John Smith</h2>
      <p><strong>Age:</strong> 42</p>
      <p><strong>Gender:</strong> Male</p>
      <p><strong>Contact:</strong> +94 77 123 4567</p>
      <p><strong>Email:</strong> john@example.com</p>
    </div>

    
    <div class="actions">
      <a href = "doctordashboard.php">
      <button class="btn btn-secondary">Back to Dashboard</button></a>
    </div>
  </div>
</body>
</html>