<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient History - Doctor Dashboard</title>
  <link rel="stylesheet" href="../css_common/header.css">
  <script src="../js_common/header.js"></script>
  <link rel="stylesheet" href="css/patienthistory.css" />
</head>
<body>
  <header class="header">
    <div class="header-left">
      <nav class="navigation">
        <a href="doctordashboard.php" class="nav-btn">Appointments</a>
        <button class="nav-btn active">Patient History</button>
        <a href="doctorreport.php" class="nav-btn">Reports</a>
      </nav>
    </div>
    <div class="header-right">
      <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
      <h1 class="header-title">Dheergayu</h1>
      <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Doctor</span>
            <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
    </div>
  </header>

  <main class="main-content">
    <div class="search-section">
      <h2>Search Patient History</h2>
      <p class="search-description">
        Search for a patient using their patient number, name, or birthday
      </p>

      <form id="search-form" class="search-form" onsubmit="return false;">
        <div class="search-fields">
          <div class="search-field">
            <label for="patient_no">Patient Number</label>
            <input
              type="text"
              id="patient_no"
              name="patient_no"
              placeholder="e.g., P001"
            />
          </div>

          <div class="search-field">
            <label for="patient_name">Patient Name</label>
            <input
              type="text"
              id="patient_name"
              name="patient_name"
              placeholder="e.g., John Smith"
            />
          </div>

          <div class="search-field">
            <label for="birthday">Birthday</label>
            <input type="date" id="birthday" name="birthday" />
          </div>
        </div>

        <div class="search-buttons">
          <button type="button" class="btn btn-search" id="btn-search">
             Search History
          </button>
          <button type="button" class="btn btn-clear" id="btn-clear">
            Clear
          </button>
        </div>
      </form>
    </div>

    <div id="result-section"></div>
  </main>

  <script src="patienthistory.js"></script>
</body>
</html>
