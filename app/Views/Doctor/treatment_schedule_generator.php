<?php
$appointment_id = $_GET['appointment_id'] ?? '';
$patient_id = $_GET['patient_id'] ?? '';

require_once __DIR__ . '/../../../config/config.php';

// Get treatments from database
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
        input, select { width:100%; padding:10px; border:1px solid var(--primary); border-radius:6px; font-size:14px; }
        .row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px; }
        .btn { padding:10px 20px; border:none; border-radius:6px; font-size:15px; cursor:pointer; font-weight:600; }
        .btn-primary { background:linear-gradient(135deg, var(--primary), #d17f1b); color:#fff; width:100%; }
        .btn-success { background:var(--success); color:#fff; width:100%; }
        .schedule-table { width:100%; border-collapse:collapse; margin-top:15px; }
        .schedule-table th { background:#f8f9fa; padding:10px; text-align:left; border-bottom:2px solid var(--primary); }
        .schedule-table td { padding:10px; border-bottom:1px solid #eee; }
        .summary { background:#d4edda; border:1px solid var(--success); border-radius:8px; padding:15px; margin-top:15px; }
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
            <input type="number" id="sessions" min="2" max="20" value="5">
        </div>
        <div>
            <label>Sessions per Week</label>
            <select id="sessions_per_week">
                <option value="2">2 per week</option>
                <option value="3">3 per week</option>
            </select>
        </div>
        <div>
            <label>Start Date</label>
            <input type="date" id="start_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        </div>
    </div>

    <button class="btn btn-primary" onclick="generateSchedule()">Generate Schedule</button>

    <div id="schedule_display" style="display:none; margin-top:20px;">
        <h3 style="color:var(--primary);">Generated Schedule</h3>
        <div id="schedule_info"></div>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Session</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="schedule_body"></tbody>
        </table>
        <div class="summary">
            <strong style="color:#155724;">Ready to send to patient</strong>
            <p style="margin:8px 0; font-size:14px;">Patient will receive SMS and can confirm all sessions with payment.</p>
        </div>
        <button class="btn btn-success" style="margin-top:15px;" onclick="sendToPatient()">Send to Patient</button>
    </div>
</div>

<script>
const appointmentId = '<?= $appointment_id ?>';
const patientId = '<?= $patient_id ?>';
let generatedSchedule = [];

function generateSchedule() {
    const diagnosis = document.getElementById('diagnosis').value.trim();
    const treatmentSelect = document.getElementById('treatment_id');
    const treatmentId = treatmentSelect.value;
    const treatmentName = treatmentSelect.selectedOptions[0]?.dataset.name || '';
    const price = parseFloat(treatmentSelect.selectedOptions[0]?.dataset.price || 4500);
    const sessions = parseInt(document.getElementById('sessions').value);
    const sessionsPerWeek = parseInt(document.getElementById('sessions_per_week').value);
    const startDate = document.getElementById('start_date').value;

    if (!diagnosis || !treatmentId || !startDate) {
        alert('Please fill all fields');
        return;
    }

    // Generate schedule
    const schedule = [];
    let currentDate = new Date(startDate);
    let sessionCount = 0;

    while (sessionCount < sessions) {
        // Skip Sundays
        if (currentDate.getDay() !== 0) {
            schedule.push({
                sessionNumber: sessionCount + 1,
                date: currentDate.toISOString().split('T')[0],
                time: '09:00:00',
                status: 'Pending'
            });
            sessionCount++;
        }
        // For 2 sessions per week: 3-day gap (Mon-Thu, Tue-Fri pattern)
        currentDate.setDate(currentDate.getDate() + (sessionsPerWeek === 2 ? 3 : 2));
    }

    generatedSchedule = schedule;

    // Display
    const totalCost = sessions * price;
    document.getElementById('schedule_info').innerHTML = `
        <strong>Treatment:</strong> ${treatmentName}<br>
        <strong>Diagnosis:</strong> ${diagnosis}<br>
        <strong>Total Sessions:</strong> ${sessions} (${sessionsPerWeek}x per week)<br>
        <strong>Total Cost:</strong> Rs ${totalCost.toLocaleString()}
    `;

    const tbody = document.getElementById('schedule_body');
    tbody.innerHTML = schedule.map(s => {
        const d = new Date(s.date);
        const day = d.toLocaleDateString('en-US', {weekday: 'short'});
        return `
            <tr>
                <td>Session ${s.sessionNumber}</td>
                <td>${s.date}</td>
                <td>${day}</td>
                <td>9:00 AM</td>
                <td><span style="background:#fff3cd;color:#856404;padding:4px 8px;border-radius:4px;font-size:12px;">${s.status}</span></td>
            </tr>
        `;
    }).join('');

    document.getElementById('schedule_display').style.display = 'block';
}

function sendToPatient() {
    const data = {
        type: 'schedule_generated',
        payload: {
            diagnosis: document.getElementById('diagnosis').value,
            treatmentId: document.getElementById('treatment_id').value,
            treatmentType: document.getElementById('treatment_id').selectedOptions[0]?.dataset.name,
            sessions: parseInt(document.getElementById('sessions').value),
            sessionsPerWeek: parseInt(document.getElementById('sessions_per_week').value),
            startDate: document.getElementById('start_date').value,
            schedule: generatedSchedule,
            appointmentId: appointmentId,
            patientId: patientId
        }
    };

    if (window.opener) {
        window.opener.postMessage(JSON.stringify(data), '*');
    }
    
    alert('Schedule sent to consultation form!');
    window.close();
}

// Add this to the existing treatment_schedule_generator.php JavaScript
// Replace the "Send to Patient" button click handler with this:

document.getElementById('send_to_patient_btn')?.addEventListener('click', function() {
    if (!generatedSchedule || generatedSchedule.schedule.length === 0) {
        alert('Please generate schedule first');
        return;
    }
    
    // Disable button to prevent double-click
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Sending...';
    
    const formData = new FormData();
    formData.append('schedule_data', JSON.stringify(generatedSchedule));
    formData.append('appointment_id', appointmentId);
    formData.append('patient_id', patientId);
    
    fetch('/dheergayu/public/api/send-treatment-plan.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Treatment plan sent to patient successfully!\nPatient can now view and confirm the schedule.');
            
            // Also send back to main consultation form
            if (window.opener && !window.opener.closed) {
                const message = {
                    type: 'schedule_generated',
                    payload: generatedSchedule
                };
                window.opener.postMessage(JSON.stringify(message), '*');
            }
            
            // Close popup after short delay
            setTimeout(() => window.close(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Failed to send plan'));
            btn.disabled = false;
            btn.textContent = 'Send to Patient';
        }
    })
    .catch(err => {
        console.error('Send error:', err);
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Send to Patient';
    });
});

</script>
</body>
</html>