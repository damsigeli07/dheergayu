<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient History - Doctor Dashboard</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/patienthistory.css" />
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
      <button class="nav-btn active">Patient History</button>
      <a href="doctorreport.php" class="nav-btn">Reports</a>
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

  <main class="main-content">
    <div class="search-section">
      <h2>Search Patient History</h2>
      <hr class="title-divider" />
      <p class="search-description">
        Search for a patient using their patient number, name, or birthday
      </p>

      
      <form id="search-form" class="search-form" onsubmit="return validateSearchForm();">  
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
  <div class="left-buttons">
    <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">
      Back to Dashboard
    </button>
  </div>
  <div class="right-buttons">
    <button type="submit" class="btn btn-search" id="btn-search">
       Search History
    </button>
    <button type="button" class="btn btn-clear" id="btn-clear">
      Clear
    </button>
  </div>
</div>
      </form>
    </div>

    <div id="result-section"></div>
  </main>


<script>
 
 function validateSearchForm() {
    let patientNo = document.getElementById("patient_no").value.trim();
    let patientName = document.getElementById("patient_name").value.trim();
    let dob = document.getElementById("birthday").value.trim(); // rename variable to dob

    // Case 1: Patient Number only
    if (patientNo) {
        if (!/^P[0-9]{3,}$/.test(patientNo)) {
            alert("Patient Number must start with 'P' and be followed by digits (e.g., P001).");
            return false;
        }
        performSearch({ patient_no: patientNo });
        return false;
    }

    // Case 2: Patient Name + DOB
    if (patientName && dob) {
        if (!/^[A-Za-z ]{2,}$/.test(patientName)) {
            alert("Patient Name should only contain letters and spaces.");
            return false;
        }
        let today = new Date();
        let enteredDate = new Date(dob);
        if (enteredDate > today) {
            alert("DOB cannot be a future date.");
            return false;
        }
        performSearch({ patient_name: patientName, dob: dob });
        return false;
    }

    alert("Please enter Patient Number OR (Patient Name and DOB).");
    return false;
}

function performSearch(params) {
    fetch("/dheergayu/app/Controllers/patienthistoryController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(params)
    })
    .then(res => res.json())
    .then(data => {
        let resultSection = document.getElementById("result-section");

        if (!data.success) {
            resultSection.innerHTML = `<p class="error-msg">${data.message}</p>`;
            return;
        }

        let p = data.patient; // patient object from consultation_form
        let history = data.history; // array of consultation records

        let html = `
        <div class="patient-info-section">
            <div class="patient-header">
                <h3>Patient Details</h3>
                <span class="patient-id">${p.patient_no}</span>
            </div>

            <div class="patient-details">
                <div class="detail-row">
                    <div class="detail-item"><strong>Name:</strong> ${p.first_name} ${p.last_name}</div>
                    <div class="detail-item"><strong>Age:</strong> ${p.age || 'N/A'}</div>
                    <div class="detail-item"><strong>Gender:</strong> ${p.gender || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-item"><strong>Contact:</strong> ${p.contact_info || 'N/A'}</div>
                </div>
            </div>
        </div>

        <div class="history-section">
            <h3>Patient History</h3>
            <div class="history-timeline">
        `;

        history.forEach(record => {
            html += `
                <div class="history-record">
                    <div><strong>Diagnosis:</strong> ${record.diagnosis || 'N/A'}</div>
                    <div><strong>Product:</strong> ${record.personal_products || 'N/A'}</div>
                    <div><strong>Treatment:</strong> ${record.recommended_treatment || 'N/A'}</div>
                    <div><strong>Visited Date:</strong> ${record.created_at || 'N/A'}</div>
                    <div><strong>Notes:</strong> ${record.notes || 'No'}</div>
                    <hr>
                </div>
            `;
        });

        html += `</div></div>`;
        resultSection.innerHTML = html;
    });
}

  // Clear button logic
  document.getElementById("btn-clear").addEventListener("click", function () {
    document.getElementById("patient_no").value = "";
    document.getElementById("patient_name").value = "";
    document.getElementById("birthday").value = "";
    document.getElementById("result-section").innerHTML = "";
  });
</script>

