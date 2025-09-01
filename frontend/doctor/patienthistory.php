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
            <a href="doctorprofile.php" class="profile-btn">Profile</a>
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
          <button type="submit" class="btn btn-search" id="btn-search">
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


<script>
  function validateSearchForm() {
    let patientNo = document.getElementById("patient_no").value.trim();
    let patientName = document.getElementById("patient_name").value.trim();
    let birthday = document.getElementById("birthday").value.trim();

    // Case 1: Patient Number only
    if (patientNo) {
      if (!/^P[0-9]{3,}$/.test(patientNo)) {
        alert("Patient Number must start with 'P' and be followed by digits (e.g., P001).");
        return false;
      }
      performSearch({ patient_no: patientNo });
      return false;
    }

    // Case 2: Patient Name + Birthday
    if (patientName && birthday) {
      if (!/^[A-Za-z ]{2,}$/.test(patientName)) {
        alert("Patient Name should only contain letters and spaces.");
        return false;
      }
      let today = new Date();
      let enteredDate = new Date(birthday);
      if (enteredDate > today) {
        alert("Birthday cannot be a future date.");
        return false;
      }
      performSearch({ patient_name: patientName, birthday: birthday });
      return false;
    }

    alert("Please enter Patient Number OR (Patient Name and Birthday).");
    return false;
  }

  function performSearch(params) {
    let resultSection = document.getElementById("result-section");

    
    let dummyPatient = {
      id: "    " , 
      name: params.patient_name || "Saman",
      birthday: params.birthday || "1985-03-15",
      gender: "Male",
      phone: "+94 77 123 4567",
      address: "123 Ayurvedic Lane, Colombo",
      history: [
        {
          visit: 1,
          date: "2024-12-10",
          doctor: "Dr. Perera",
          diagnosis: "Back Pain",
          treatment: "Herbal Oil Therapy",
          prescription: "Neem Oil, Triphala Tablets"
        },
        {
          visit: 2,
          date: "2025-01-20",
          doctor: "Dr. Silva",
          diagnosis: "Migraine",
          treatment: "Shirodhara",
          prescription: "Brahmi Powder"
        }
      ]
    };

    // Build HTML
    let html = `
      <div class="patient-info-section">
        <div class="patient-header">
          <h3>Patient Details</h3>
          <span class="patient-id">${dummyPatient.id}</span>
        </div>
        <div class="patient-details">
          <div class="detail-row">
            <div class="detail-item"><strong>Name:</strong> ${dummyPatient.name}</div>
            <div class="detail-item"><strong>Birthday:</strong> ${dummyPatient.birthday}</div>
            <div class="detail-item"><strong>Gender:</strong> ${dummyPatient.gender}</div>
          </div>
          <div class="detail-row">
            <div class="detail-item"><strong>Phone:</strong> ${dummyPatient.phone}</div>
            <div class="detail-item full-width"><strong>Address:</strong> ${dummyPatient.address}</div>
          </div>
        </div>
      </div>

      <div class="history-section">
        <h3>Patient History</h3>
        <div class="history-timeline">
    `;

    dummyPatient.history.forEach((record) => {
      html += `
        <div class="history-record">
          <div><strong>Visit:</strong> ${record.visit}</div>
          <div><strong>Date:</strong> ${record.date}</div>
          <div><strong>Doctor:</strong> ${record.doctor}</div>
          <div><strong>Diagnosis:</strong> ${record.diagnosis}</div>
          <div><strong>Treatment:</strong> ${record.treatment}</div>
          <div><strong>Prescription:</strong> ${record.prescription}</div>
          <hr>
        </div>
      `;
    });

    html += `</div></div>`;
    resultSection.innerHTML = html;
  }

  // Clear button logic
  document.getElementById("btn-clear").addEventListener("click", function () {
    document.getElementById("patient_no").value = "";
    document.getElementById("patient_name").value = "";
    document.getElementById("birthday").value = "";
    document.getElementById("result-section").innerHTML = "";
  });
</script>

