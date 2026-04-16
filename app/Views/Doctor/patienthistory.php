<?php require_once __DIR__ . '/../../includes/auth_doctor.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient History - Doctor Dashboard</title>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
  <script src="/dheergayu/public/assets/js/header.js"></script>
  <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/patienthistory.css" />
  <style>
    /* Accordion / Card styles for patient history and treatment plans */
    .card { border: 1px solid #e5e7eb; border-radius: 8px; background:#fff; margin-bottom:12px; box-shadow:0 1px 4px rgba(16,24,40,0.03); }
    .card-header { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; cursor:pointer; }
    .card-header .left { display:flex; gap:12px; align-items:center; }
    .card-header .title { font-weight:700; color:#111827; }
    .card-header .meta { color:#6b7280; font-size:13px; }
    .card-body { padding:12px 16px; border-top:1px solid #f3f4f6; display:none; }
    .card-body.open { display:block; }
    .badge { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:12px; }
    .badge.confirmed { background:#d1fae5; color:#065f46; }
    .badge.pending { background:#fef3c7; color:#92400e; }
    .badge.cancelled { background:#fed7d7; color:#991b1b; }
    .sessions-list { margin-top:8px; }
    .session-row { padding:8px 0; border-bottom:1px dashed #eef2f7; }
    .session-row:last-child { border-bottom:none; }
    .session-meta { color:#6b7280; font-size:13px; }
    .icon { width:18px; height:18px; display:inline-block; text-align:center; }
    .check { color:#10b981; font-weight:800; margin-right:8px; }
    .pending-ic { color:#f59e0b; margin-right:8px; }
    .progress-track { background:#e6eef6; height:10px; border-radius:6px; overflow:hidden; margin-top:8px; }
    .progress-fill { height:100%; background:linear-gradient(90deg,#10b981,#34d399); width:0; }
    .small { font-size:13px; color:#374151; }
    .collapsed-list { display: none; }
    .see-more-btn { background:transparent; border:none; color:#2563eb; cursor:pointer; padding:6px 0; font-weight:600; }
    .title-divider { border:0; height:3px; background:linear-gradient(90deg,#f6b26b,#f3b865); margin:8px 0 16px; }
    .search-section { padding:20px; }
    .search-fields { display:flex; gap:12px; flex-wrap:wrap; }
    .search-field { display:flex; flex-direction:column; gap:6px; min-width:200px; }
    .search-buttons { display:flex; justify-content:space-between; margin-top:12px; }
    .btn { padding:8px 12px; border-radius:6px; border:none; cursor:pointer; }
    .btn-search { background:#f3a648; color:#fff; }
    .btn-clear { background:#6b7280; color:#fff; }
    .btn-back { background:#6b7280; color:#fff; }
  </style>
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
      <a href="doctordashboard.php?view=treatment-plans" class="nav-btn">Treatment Plans</a>
      <a href="patienthistory.php" class="nav-btn active">Patient History</a>
      <a href="doctorreport.php" class="nav-btn">Reports</a>
    </nav>
    
    <div class="user-section">
      <div class="user-icon" id="user-icon">👤</div>
      <span class="user-role">Doctor</span>
      <!-- Dropdown -->
      <div class="user-dropdown" id="user-dropdown">
        <a href="doctorprofile.php" class="profile-btn">Profile</a>
        <a href="/dheergayu/app/Views/logout.php" class="logout-btn">Logout</a>
      </div>
    </div>
  </header>

  <main class="main-content">
    <div class="search-section">
      <h2>Search Patient History</h2>
      <hr class="title-divider" />
      <p class="search-description">Search for a patient using their patient number, name, or birthday</p>

      <form id="search-form" class="search-form" onsubmit="return validateSearchForm();">  
        <div class="search-fields">
          <div class="search-field">
            <label for="patient_no">Patient Number</label>
            <input type="text" id="patient_no" name="patient_no" placeholder="e.g., P0001" />
          </div>

          <div class="search-field">
            <label for="patient_name">Patient Name</label>
            <input type="text" id="patient_name" name="patient_name" placeholder="e.g., John Smith" />
          </div>

          <div class="search-field">
            <label for="birthday">Birthday</label>
            <input type="date" id="birthday" name="birthday" />
          </div>
        </div>

        <div class="search-buttons">
          <div class="left-buttons">
            <button type="button" class="btn btn-back" onclick="window.location.href='doctordashboard.php'">Back to Dashboard</button>
          </div>
          <div class="right-buttons">
            <button type="submit" class="btn btn-search" id="btn-search">Search History</button>
            <button type="button" class="btn btn-clear" id="btn-clear">Clear</button>
          </div>
        </div>
      </form>
    </div>

    <div id="result-section"></div>
  </main>

<script>
// Helper to escape HTML
function esc(s){ return String(s === undefined || s === null ? '' : s)
  .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

function validateSearchForm() {
  let patientNo = document.getElementById("patient_no").value.trim();
  let patientName = document.getElementById("patient_name").value.trim();
  let dob = document.getElementById("birthday").value.trim();

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
      resultSection.innerHTML = `<p class="error-msg">${esc(data.message)}</p>`;
      return;
    }

    let p = data.patient; // patient object
    let history = data.history || [];

    // Build HTML
    let html = `
      <div class="patient-info-section">
        <div class="patient-header">
          <h3>Patient Details</h3>
          <span class="patient-id">${esc(p.patient_no)}</span>
        </div>
        <div class="patient-details small">
          <div><strong>Name:</strong> ${esc(p.first_name)} ${esc(p.last_name)} &nbsp; <strong>Age:</strong> ${esc(p.age||'N/A')} &nbsp; <strong>Gender:</strong> ${esc(p.gender||'N/A')}</div>
          <div style="margin-top:6px"><strong>Contact:</strong> ${esc(p.contact_info||'N/A')}</div>
        </div>
      </div>

      <div class="history-section">
        <h3>Patient Visits</h3>
    `;

    const previewCount = 3;
    const totalHistory = history.length;
    history.forEach((record, idx) => {
      let headerTime = esc(record.created_at || 'Unknown');
      const hidden = idx >= previewCount;
      const cardClass = hidden ? 'collapsed-list' : '';
      html += `
        <div class="card ${cardClass}">
          <div class="card-header" data-toggle="visit-${idx}">
            <div class="left">
              <div class="icon">📅</div>
              <div>
                <div class="title">Visit</div>
                <div class="meta">${headerTime}</div>
              </div>
            </div>
            <div class="meta">Click to expand</div>
          </div>
          <div class="card-body" id="visit-${idx}">
            <div><strong>Diagnosis:</strong> ${esc(record.diagnosis || '') || 'No'}</div>
            <div><strong>Product:</strong> ${esc((record.personal_products && record.personal_products !== 'None') ? record.personal_products : 'No')}</div>
            <div><strong>Treatment:</strong> ${esc((record.treatment_plan_name && record.treatment_plan_name !== '') ? record.treatment_plan_name : ((record.recommended_treatment && record.recommended_treatment !== 'None') ? record.recommended_treatment : 'No'))}</div>
            <div><strong>Notes:</strong> ${esc(record.notes || 'No')}</div>
          </div>
        </div>
      `;
    });

    if (totalHistory > previewCount) {
      html += `<div style="text-align:center;margin:8px 0;"><button class="see-more-btn" id="show-more-visits">See more visits (${totalHistory - previewCount} more)</button></div>`;
    }

    // Treatment Plans
    if (data.treatments && data.treatments.length > 0) {
      html += `<h3 style="margin-top:12px">Treatment Plans</h3>`;
      const totalPlans = data.treatments.length;
      data.treatments.forEach((tp, tindex) => {
        const total = (tp.total_sessions && !isNaN(tp.total_sessions)) ? parseInt(tp.total_sessions) : (tp.sessions ? tp.sessions.length : 0);
        let completed = 0;
        if (tp.sessions && tp.sessions.length>0) tp.sessions.forEach(s=>{ if ((s.status||'').toLowerCase()==='completed') completed++; });
        let pct = total>0 ? Math.round((completed/total)*100) : 0;
        const planHidden = tindex >= previewCount;

        html += `
          <div class="card ${planHidden ? 'collapsed-list' : ''}">
            <div class="card-header" data-toggle="plan-${tindex}">
              <div class="left">
                <div class="icon">📝</div>
                <div>
                  <div class="title">Plan #${esc(tp.plan_id)} — ${esc(tp.treatment_name)}</div>
                  <div class="meta">${esc(tp.start_date)} • ${(tp.sessions ? tp.sessions.length : 1)} session(s)</div>
                </div>
              </div>
              <div style="text-align:right">
                <div><span class="badge ${esc((tp.status||'').toLowerCase())}">${esc(tp.status)}</span></div>
                <div class="meta">Rs ${esc(tp.total_cost)}</div>
              </div>
            </div>
            <div class="card-body" id="plan-${tindex}">
              <div class="small"><strong>Created:</strong> ${esc(tp.created_at)}</div>
              <div class="progress-track"><div class="progress-fill" style="width:${pct}%;"></div></div>
              <div class="small">${pct}% complete (${completed}/${total})</div>
              <div class="sessions-list">
        `;

        if (tp.sessions && tp.sessions.length>0) {
          tp.sessions.forEach(s => {
            html += `<div class="session-row">`;
            if ((s.status||'').toLowerCase()==='completed') {
              html += `<div><span class="check">✓</span><strong>Session ${esc(s.session_number)}</strong> — ${esc(s.session_date)} ${esc(s.session_time)} <span class="session-meta">by ${esc(s.note_staff||s.assigned_staff||'Staff')} on ${esc(s.note_created_at||'')}</span></div>`;
              html += `<div class="small">Note: ${esc(s.note||'No note')}</div>`;
            } else {
              html += `<div><span class="pending-ic">⏳</span><strong>Session ${esc(s.session_number)}</strong> — ${esc(s.session_date)} ${esc(s.session_time)} <span class="session-meta">Assigned: ${esc(s.assigned_staff||'Unassigned')}</span></div>`;
            }
            html += `</div>`;
          });
        } else {
          html += `<div class="small">No session information available</div>`;
        }

        html += `</div></div></div>`;
      });
      if (typeof totalPlans !== 'undefined' && totalPlans > previewCount) {
        html += `<div style="text-align:center;margin:8px 0;"><button class="see-more-btn" id="show-more-plans">See more plans (${totalPlans - previewCount} more)</button></div>`;
      }
    }

    html += `</div>`;
    resultSection.innerHTML = html;

    // Wire up accordion toggles
    document.querySelectorAll('.card-header').forEach(h => {
      h.addEventListener('click', function(){
        const id = h.getAttribute('data-toggle');
        const body = document.getElementById(id);
        if (!body) return;
        body.classList.toggle('open');
      });
    });
    // See more buttons
    const showMoreVisitsBtn = document.getElementById('show-more-visits');
    if (showMoreVisitsBtn) {
      showMoreVisitsBtn.addEventListener('click', function(){
        document.querySelectorAll('.card').forEach(c=> c.classList.remove('collapsed-list'));
        showMoreVisitsBtn.style.display = 'none';
      });
    }
    const showMorePlansBtn = document.getElementById('show-more-plans');
    if (showMorePlansBtn) {
      showMorePlansBtn.addEventListener('click', function(){
        document.querySelectorAll('.card.collapsed-list').forEach(c=> c.classList.remove('collapsed-list'));
        showMorePlansBtn.style.display = 'none';
      });
    }
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
</body>
</html>
