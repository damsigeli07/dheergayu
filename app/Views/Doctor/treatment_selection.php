<?php
// Minimal treatment selection popup view.
$treatments = [
    ['id'=>1,'name'=>'Abhyanga','price'=>1500],
    ['id'=>2,'name'=>'Shirodhara','price'=>2000],
    ['id'=>3,'name'=>'Panchakarma','price'=>5000],
];
$appointment_id = $_GET['appointment_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Book Ayurvedic Treatment</title>
    <style>
    :root{--accent:#8b5e3c;--card:#ffffff;--muted:#9aa3a9}
    body{font-family:Arial,Helvetica,sans-serif;background:#f2f6f8;padding:30px;color:#333}
    .page-title{font-size:36px;color:var(--accent);text-align:center;margin-bottom:22px;font-weight:700}
    .card{max-width:920px;margin:0 auto;background:var(--card);border-radius:12px;padding:26px 30px;box-shadow:0 8px 28px rgba(28,44,56,0.06)}
    .section-title{color:var(--accent);font-weight:600;margin-bottom:12px;border-bottom:1px solid #eee;padding-bottom:10px}
    select,input[type=date]{width:100%;padding:14px;border-radius:10px;border:1px solid #e6e6e6;background:#fff;font-size:15px}
    .field{margin-top:14px}
    .slots{margin-top:18px;min-height:60px}
    .slot-btn{display:inline-block;margin:8px 8px 0 0;padding:10px 14px;border-radius:10px;border:1px solid #e1e1e1;background:#fff;cursor:pointer;color:#444}
    .slot-btn.selected{background:var(--accent);color:#fff;border-color:rgba(0,0,0,0.08)}
    .actions{margin-top:18px;text-align:right}
    .btn-save{background:var(--accent);color:#fff;padding:10px 16px;border:none;border-radius:8px;font-size:15px}
    .hint{color:var(--muted);padding:20px 0}
    </style>
</head>
<body>
  <div class="page-title">Select Treatment Details</div>
  <div class="card">
    <div class="section">
      <div class="section-title">Select Treatment</div>
      <div class="field">
        <select id="treatment_id" name="treatment_id">
          <option value="">-- Choose Treatment Type --</option>
          <?php foreach($treatments as $t): ?>
            <option value="<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['name']) ?>"><?= htmlspecialchars($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label style="display:block;margin-bottom:6px;color:#666">Treatment Date</label>
        <input type="date" id="treatment_date" name="treatment_date" />
      </div>

      <div class="field">
        <label style="display:block;margin-bottom:6px;color:#666">Description (optional)</label>
        <textarea id="treatment_description" name="treatment_description" rows="3" style="width:100%;padding:12px;border-radius:8px;border:1px solid #e6e6e6;resize:vertical" placeholder="Add optional notes for this booking (e.g. patient notes, requirements)"></textarea>
      </div>
    </div>

    <div class="section" style="margin-top:18px">
      <div class="section-title">Available Slots</div>
      <div class="hint" id="slots_hint">Select a date first</div>
      <div class="slots" id="time_slots_container"></div>
    </div>

    <div class="actions">
      <button type="button" id="save_selection" class="btn-save">Save selection</button>
      <button type="button" style="margin-left:8px;padding:10px 14px;border-radius:8px;border:1px solid #e6e6e6;background:#fff;" onclick="window.close();">Cancel</button>
    </div>
  </div>

  <script>
  const appointmentId = '<?= addslashes($appointment_id) ?>';
  const treatmentSelect = document.getElementById('treatment_id');
  const dateInput = document.getElementById('treatment_date');
  const slotsContainer = document.getElementById('time_slots_container');
  const slotsHint = document.getElementById('slots_hint');

  // Prevent past date selection: set min to tomorrow (only future dates allowed)
  (function setMinDateToTomorrow(){
    const today = new Date();
    const tomorrow = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
    const iso = tomorrow.toISOString().split('T')[0];
    dateInput.setAttribute('min', iso);
  })();

  // When date or treatment changes, generate frontend slots (5 slots per day)
  dateInput.addEventListener('change', () => { loadAvailableSlots(); });
  treatmentSelect.addEventListener('change', () => { loadAvailableSlots(); });

  function loadAvailableSlots(){
    const tId = treatmentSelect.value;
    const date = dateInput.value;
    slotsContainer.innerHTML = '';
    if (!tId || !date) { slotsHint.style.display = 'block'; slotsHint.textContent = 'Select a date first'; return; }
    // Ensure selected date is not in the past (extra guard)
    const selected = new Date(date + 'T00:00:00');
    const minDate = new Date();
    minDate.setHours(0,0,0,0);
    // require > today (future dates only)
    if (selected <= minDate) {
      slotsHint.style.display = 'block';
      slotsHint.textContent = 'Please select a future date.';
      return;
    }
    slotsHint.style.display = 'none';

    // Front-end: create 5 fixed slots for every day. Adjust times as needed.
    const dailySlots = generateFiveSlots();

    dailySlots.forEach(s => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = s;
      btn.addEventListener('click', () => {
        document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('selected'));
        btn.classList.add('selected');
      });
      btn.className = 'slot-btn';
      slotsContainer.appendChild(btn);
    });
  }

  function generateFiveSlots(){
    // Example: five slots evenly spread through the day
    // You can adjust these times to match clinic schedule
    return ['09:00','10:30','12:00','14:30','16:00'];
  }

  document.getElementById('save_selection').addEventListener('click', () => {
    const tEl = treatmentSelect;
    const tId = tEl.value;
    const tName = tEl.selectedOptions[0]?.getAttribute('data-name') || tEl.selectedOptions[0]?.textContent || '';
    const date = dateInput.value;
    const selectedBtn = document.querySelector('.slot-btn.selected');
    const description = document.getElementById('treatment_description').value.trim();
    if (!tId || !date || !selectedBtn) { alert('Please select treatment, date and time slot.'); return; }
    const time = selectedBtn.textContent;

    // Still attempt to save on the server (optional). Front-end selection is generated locally.
    fetch('/dheergayu/app/Controllers/TreatmentController.php?action=save_selection', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ appointment_id: appointmentId, treatment_id: tId, treatment_name: tName, date: date, time: time, description: description })
    }).then(r=>r.json()).then(resp=>{
      if (resp && resp.success) {
        const message = { type: 'treatment_selected', payload: { id: tId, name: tName, date: date, time: time, description: description } };
        if (window.opener) { window.opener.postMessage(JSON.stringify(message), '*'); }
        window.close();
      } else {
        alert('Error saving selection: ' + (resp.message||''));
      }
    }).catch(err => { alert('Network error saving selection.'); });
  });
  </script>
</body>
</html>
