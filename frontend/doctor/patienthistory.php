<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient History - Doctor Dashboard</title>
  <link rel="stylesheet" href="css/patienthistory.css?v=1.5" />
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
