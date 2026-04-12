<?php
$appointment_id = $_GET['appointment_id'] ?? '';
$patient_id = $_GET['patient_id'] ?? '';

require_once __DIR__ . '/../../../config/config.php';

$treatments_query = "SELECT treatment_id, treatment_name, price FROM treatment_list WHERE status = 'Active'";
$treatments_result = $conn->query($treatments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Generate Treatment Schedule</title>
    <style>
        :root { --primary: #E6A85A; --success: #28a745; }
        body { font-family: Arial, sans-serif; background:#f6f8fa; padding:20px; margin:0; }
        .container { max-width:900px; margin:0 auto; background:#fff; border-radius:12px; padding:25px; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
        h2 { color:var(--primary); margin-bottom:20px; border-bottom:3px solid var(--primary); padding-bottom:10px; }
        label { display:block; margin-bottom:6px; font-weight:500; color:#333; }
        input, select { width:100%; padding:10px; border:1px solid var(--primary); border-radius:6px; font-size:14px; box-sizing:border-box; }
        .row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px; }
        .btn { padding:10px 20px; border:none; border-radius:6px; font-size:15px; cursor:pointer; font-weight:600; }
        .btn-primary { background:linear-gradient(135deg, var(--primary), #d17f1b); color:#fff; width:100%; }
        .btn-success { background:var(--success); color:#fff; width:100%; }
        .schedule-table { width:100%; border-collapse:collapse; margin-top:15px; }
        .schedule-table th { background:#f8f9fa; padding:10px; text-align:left; border-bottom:2px solid var(--primary); font-size:13px; }
        .schedule-table td { padding:8px 10px; border-bottom:1px solid #eee; font-size:14px; }
        .schedule-table input[type="date"] { padding:6px 8px; font-size:13px; border:1px solid #ccc; border-radius:4px; width:150px; }
        .slot-select { padding:6px 8px; font-size:13px; border:1px solid #ccc; border-radius:4px; min-width:130px; }
        .slot-booked { color:#dc3545; font-size:11px; margin-left:4px; }
        .slot-loading { font-size:12px; color:#888; font-style:italic; }
        .summary { background:#d4edda; border:1px solid var(--success); border-radius:8px; padding:15px; margin-top:15px; }
        .hint { font-size:12px; color:#888; margin-top:4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Generate Treatment Schedule</h2>

    <div style="margin-bottom:15px;">
        <label>Diagnosis</label>
        <input type="text" id="diagnosis" placeholder="e.g., Pitta imbalance">
    </div>

    <div style="margin-bottom:15px;">
        <label>Treatment Type</label>
        <select id="treatment_id">
            <option value="">Select Treatment</option>
            <?php while($row = $treatments_result->fetch_assoc()): ?>
                <option value="<?= $row['treatment_id'] ?>" data-name="<?= htmlspecialchars($row['treatment_name']) ?>" data-price="<?= $row['price'] ?>">
                    <?= htmlspecialchars($row['treatment_name']) ?> - Rs <?= number_format($row['price'], 2) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="row">
        <div>
            <label>Number of Sessions</label>
            <input type="number" id="sessions" min="1" max="10" value="5">
            <div class="hint">Maximum 10 sessions</div>
        </div>
        <div>
            <label>Sessions per Week</label>
            <select id="sessions_per_week">
                <option value="1">1 per week</option>
                <option value="2" selected>2 per week</option>
                <option value="3">3 per week</option>
            </select>
        </div>
        <div>
            <label>Start Date</label>
            <input type="date" id="start_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        </div>
    </div>

    <button class="btn btn-primary" onclick="generateSchedule()">Generate Schedule</button>

    <div id="schedule_display" style="display:none; margin-top:25px;">
        <h3 style="color:var(--primary); margin-bottom:5px;">Generated Schedule</h3>
        <p style="font-size:13px; color:#666; margin-top:0 0 10px 0;">You can edit the date and time for each session below before sending.</p>
        <div id="schedule_info" style="margin-bottom:12px; font-size:14px; line-height:1.8;"></div>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody id="schedule_body"></tbody>
        </table>
        <div class="summary" style="margin-top:15px;">
            <strong style="color:#155724;">Review and confirm the schedule</strong>
            <p style="margin:6px 0 0 0; font-size:13px; color:#155724;">Edit dates or times above if needed, then click "Confirm Schedule".</p>
        </div>
        <button class="btn btn-success" style="margin-top:15px;" onclick="sendToPatient()">Confirm Schedule</button>
    </div>
</div>

<script>
const appointmentId = '<?= $appointment_id ?>';
const patientId     = '<?= $patient_id ?>';
const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

// Cache: slotCache[treatmentId][date] = [{slot_time, booked}, ...]
const slotCache = {};

function getSlots(treatmentId, date, callback) {
    if (!treatmentId || !date) { callback([]); return; }
    if (slotCache[treatmentId] && slotCache[treatmentId][date]) {
        callback(slotCache[treatmentId][date]);
        return;
    }
    fetch('/dheergayu/public/api/treatment-slot-availability.php?treatment_id=' + treatmentId + '&date=' + date)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!slotCache[treatmentId]) slotCache[treatmentId] = {};
            slotCache[treatmentId][date] = data.slots || [];
            callback(slotCache[treatmentId][date]);
        })
        .catch(function() { callback([]); });
}

function buildSlotSelect(idx, slots, currentTime) {
    // currentTime in 'HH:MM' or 'HH:MM:SS'
    const cur = (currentTime || '09:00').substring(0, 5);

    if (!slots || slots.length === 0) {
        // No slot config for this treatment — fall back to free time input
        return '<input type="time" class="session-time" data-idx="' + idx + '" value="' + cur + '">';
    }

    let html = '<select class="slot-select session-time" data-idx="' + idx + '">';
    let anyAvail = false;
    slots.forEach(function(s) {
        const t = s.slot_time.substring(0, 5); // HH:MM
        const booked = s.booked === 1;
        const label = formatTime(s.slot_time) + (booked ? ' — Booked' : ' — Available');
        const sel = (t === cur) ? ' selected' : '';
        const dis = booked ? ' disabled' : '';
        html += '<option value="' + t + '"' + sel + dis + ' style="color:' + (booked ? '#dc3545' : '#155724') + ';">' + label + '</option>';
        if (!booked) anyAvail = true;
    });
    html += '</select>';
    if (!anyAvail) {
        html += ' <span class="slot-booked">All slots booked on this date!</span>';
    }
    return html;
}

function refreshSlotCell(idx, treatmentId, date, currentTime) {
    const cell = document.getElementById('slot-cell-' + idx);
    if (!cell) return;
    cell.innerHTML = '<span class="slot-loading">Checking availability...</span>';
    getSlots(treatmentId, date, function(slots) {
        cell.innerHTML = buildSlotSelect(idx, slots, currentTime);
    });
}

function generateSchedule() {
    const diagnosis       = document.getElementById('diagnosis').value.trim();
    const treatmentSelect = document.getElementById('treatment_id');
    const treatmentId     = treatmentSelect.value;
    const treatmentName   = treatmentSelect.selectedOptions[0]?.dataset.name || '';
    const price           = parseFloat(treatmentSelect.selectedOptions[0]?.dataset.price || 4500);
    const sessions        = Math.min(10, Math.max(1, parseInt(document.getElementById('sessions').value) || 5));
    const sessionsPerWeek = parseInt(document.getElementById('sessions_per_week').value);
    const startDate       = document.getElementById('start_date').value;

    if (!diagnosis)   { alert('Please enter a diagnosis.'); return; }
    if (!treatmentId) { alert('Please select a treatment type.'); return; }
    if (!startDate)   { alert('Please select a start date.'); return; }

    document.getElementById('sessions').value = sessions;

    const gapDays = sessionsPerWeek === 1 ? 7 : (sessionsPerWeek === 3 ? 2 : 3);

    const schedule = [];
    let current = new Date(startDate + 'T12:00:00');
    let count = 0;
    while (count < sessions) {
        if (current.getDay() !== 0) {
            schedule.push({
                sessionNumber: count + 1,
                date: current.toISOString().split('T')[0],
                time: '09:00'
            });
            count++;
        }
        if (count < sessions) current.setDate(current.getDate() + gapDays);
    }

    const totalCost = sessions * price;
    document.getElementById('schedule_info').innerHTML =
        '<strong>Treatment:</strong> ' + escHtml(treatmentName) +
        ' &nbsp;|&nbsp; <strong>Diagnosis:</strong> ' + escHtml(diagnosis) +
        ' &nbsp;|&nbsp; <strong>Sessions:</strong> ' + sessions + ' (' + sessionsPerWeek + 'x per week)' +
        ' &nbsp;|&nbsp; <strong>Total Cost:</strong> Rs ' + totalCost.toLocaleString();

    const tbody = document.getElementById('schedule_body');
    tbody.innerHTML = schedule.map(function(s) {
        const d = new Date(s.date + 'T12:00:00');
        return '<tr>' +
            '<td><strong>Session ' + s.sessionNumber + '</strong></td>' +
            '<td><input type="date" class="session-date" data-idx="' + (s.sessionNumber - 1) + '" value="' + s.date + '" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"></td>' +
            '<td id="day-' + (s.sessionNumber - 1) + '" style="color:#555;">' + DAYS[d.getDay()] + '</td>' +
            '<td id="slot-cell-' + (s.sessionNumber - 1) + '"><span class="slot-loading">Checking...</span></td>' +
        '</tr>';
    }).join('');

    // Load slots for each row
    schedule.forEach(function(s) {
        const idx = s.sessionNumber - 1;
        refreshSlotCell(idx, treatmentId, s.date, s.time);
    });

    // When date changes: update day label + reload slots
    tbody.querySelectorAll('.session-date').forEach(function(inp) {
        inp.addEventListener('change', function() {
            const idx = this.dataset.idx;
            const d = new Date(this.value + 'T12:00:00');
            document.getElementById('day-' + idx).textContent = isNaN(d) ? '' : DAYS[d.getDay()];
            refreshSlotCell(idx, treatmentId, this.value, '09:00');
        });
    });

    document.getElementById('schedule_display').style.display = 'block';
}

function sendToPatient() {
    const diagnosis       = document.getElementById('diagnosis').value.trim();
    const treatmentSelect = document.getElementById('treatment_id');
    const treatmentName   = treatmentSelect.selectedOptions[0]?.dataset.name || '';
    const pricePerSession = parseFloat(treatmentSelect.selectedOptions[0]?.dataset.price || 4500);
    const sessions        = parseInt(document.getElementById('sessions').value);
    const sessionsPerWeek = parseInt(document.getElementById('sessions_per_week').value);
    const startDate       = document.getElementById('start_date').value;

    const dateInputs = document.querySelectorAll('.session-date');
    const timeInputs = document.querySelectorAll('.session-time');

    if (!dateInputs.length) { alert('Please generate the schedule first.'); return; }

    const schedule = [];
    for (let i = 0; i < dateInputs.length; i++) {
        const timeVal = timeInputs[i].value;
        if (!dateInputs[i].value || !timeVal) {
            alert('Please fill in the date and time for all sessions.');
            return;
        }
        // Check if chosen slot is booked (select element will be disabled for booked options,
        // but a free-text time input won't be — so just warn)
        if (timeInputs[i].tagName === 'SELECT') {
            const opt = timeInputs[i].selectedOptions[0];
            if (opt && opt.disabled) {
                alert('Session ' + (i + 1) + ': the selected time is already booked. Please choose another slot.');
                return;
            }
        }
        schedule.push({
            sessionNumber: i + 1,
            date: dateInputs[i].value,
            time: timeVal.length === 5 ? timeVal + ':00' : timeVal,
            status: 'Pending'
        });
    }

    const data = {
        type: 'schedule_generated',
        payload: {
            diagnosis: diagnosis,
            treatmentId: treatmentSelect.value,
            treatmentType: treatmentName,
            pricePerSession: pricePerSession,
            sessions: sessions,
            sessionsPerWeek: sessionsPerWeek,
            startDate: schedule[0].date,
            schedule: schedule,
            appointmentId: appointmentId,
            patientId: patientId
        }
    };

    if (window.opener) {
        window.opener.postMessage(JSON.stringify(data), '*');
    }
    alert('Schedule confirmed and sent to the consultation form!');
    window.close();
}

function formatTime(t) {
    const parts = t.split(':');
    let h = parseInt(parts[0]), m = parts[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return h + ':' + m + ' ' + ampm;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</body>
</html>
