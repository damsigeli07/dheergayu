<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    die('Access denied');
}

$plan_id = intval($_GET['plan_id'] ?? 0);
$user_role = strtolower($_SESSION['role'] ?? '');

if (!$plan_id) {
    die('Invalid plan ID');
}

// Get treatment plan
$stmt = $conn->prepare("
    SELECT tp.*, tl.treatment_name, p.first_name, p.last_name
    FROM treatment_plans tp
    LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
    LEFT JOIN patients p ON tp.patient_id = p.id
    WHERE tp.plan_id = ?
");
$stmt->bind_param('i', $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    die('Plan not found');
}

// Get sessions
$stmt = $conn->prepare("SELECT * FROM treatment_sessions WHERE plan_id = ? ORDER BY session_number");
$stmt->bind_param('i', $plan_id);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reschedule Treatment Sessions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        h2 { color: #1f2937; margin-bottom: 10px; }
        .subtitle { color: #6b7280; margin-bottom: 25px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea; }
        .info-box strong { color: #1f2937; display: block; margin-bottom: 8px; }
        .info-box p { color: #4b5563; font-size: 14px; margin: 4px 0; }
        .session-list { margin: 20px 0; }
        .session-item { display: flex; align-items: center; padding: 12px; background: #f9fafb; margin-bottom: 8px; border-radius: 6px; }
        .session-item input[type="checkbox"] { margin-right: 12px; width: 18px; height: 18px; }
        .session-item label { flex: 1; cursor: pointer; }
        .session-item.completed { opacity: 0.5; pointer-events: none; }
        .controls { margin: 25px 0; }
        .controls label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
        .controls input, .controls select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .btn-group { display: flex; gap: 10px; margin-top: 25px; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; flex: 1; }
        .btn-primary { background: #667eea; color: white; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn:hover { opacity: 0.9; }
        .preview { background: #ecfdf5; border: 1px solid #10b981; padding: 15px; border-radius: 8px; margin-top: 20px; display: none; }
        .preview-title { font-weight: 600; color: #065f46; margin-bottom: 10px; }
        .preview-list { list-style: none; }
        .preview-list li { padding: 8px; background: white; margin: 4px 0; border-radius: 4px; font-size: 13px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 6px; margin: 15px 0; font-size: 13px; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reschedule Treatment Sessions</h2>
        <p class="subtitle">Plan #<?= $plan_id ?> - <?= htmlspecialchars($plan['treatment_name']) ?></p>

        <div class="info-box">
            <strong>Patient: <?= htmlspecialchars($plan['first_name'] . ' ' . $plan['last_name']) ?></strong>
            <p>Total Sessions: <?= $plan['total_sessions'] ?></p>
            <p>Sessions per Week: <?= $plan['sessions_per_week'] ?></p>
            <p>Original Start Date: <?= date('M d, Y', strtotime($plan['start_date'])) ?></p>
        </div>

        <div class="warning">
            ⚠️ Note: You can only reschedule sessions that haven't been completed yet.
            The system will automatically find the next available slots.
        </div>

        <form id="rescheduleForm">
            <input type="hidden" name="plan_id" value="<?= $plan_id ?>">
            
            <div class="session-list">
                <strong style="display:block;margin-bottom:10px;color:#374151;">Select sessions to reschedule:</strong>
                <?php foreach ($sessions as $session): ?>
                    <div class="session-item <?= $session['status'] === 'Completed' ? 'completed' : '' ?>">
                        <input 
                            type="checkbox" 
                            name="sessions[]" 
                            value="<?= $session['session_id'] ?>"
                            id="session_<?= $session['session_id'] ?>"
                            <?= $session['status'] === 'Completed' ? 'disabled' : '' ?>
                        >
                        <label for="session_<?= $session['session_id'] ?>">
                            Session <?= $session['session_number'] ?>: 
                            <?= date('M d, Y - g:i A', strtotime($session['session_date'] . ' ' . $session['session_time'])) ?>
                            <span style="margin-left:10px;color:#6b7280;font-size:12px;">[<?= $session['status'] ?>]</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="controls">
                <label>New Start Date (for rescheduled sessions):</label>
                <input type="date" name="new_start_date" id="new_start_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>

            <button type="button" class="btn btn-primary" onclick="generatePreview()">Preview New Schedule</button>

            <div id="preview" class="preview">
                <div class="preview-title">📅 Proposed New Schedule:</div>
                <ul id="preview-list" class="preview-list"></ul>
            </div>

            <div class="btn-group" id="action-buttons" style="display:none;">
                <button type="button" class="btn btn-secondary" onclick="window.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm & Send to Patient</button>
            </div>
        </form>
    </div>

    <script>
    const plan = <?= json_encode($plan) ?>;
    const sessions = <?= json_encode($sessions) ?>;

    function generatePreview() {
        const selectedIds = Array.from(document.querySelectorAll('input[name="sessions[]"]:checked')).map(cb => parseInt(cb.value));
        const newStartDate = document.getElementById('new_start_date').value;
        
        if (selectedIds.length === 0) {
            alert('Please select at least one session to reschedule');
            return;
        }
        
        if (!newStartDate) {
            alert('Please select a new start date');
            return;
        }

        // Generate new schedule
        const selectedSessions = sessions.filter(s => selectedIds.includes(s.session_id));
        const newSchedule = calculateNewSchedule(selectedSessions, newStartDate, plan.sessions_per_week);
        
        // Display preview
        const previewList = document.getElementById('preview-list');
        previewList.innerHTML = '';
        
        newSchedule.forEach((item, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <strong>Session ${item.session_number}</strong>: 
                ${formatDate(item.new_date)} at ${item.new_time}
                <span style="float:right;color:#10b981;">✓ Available</span>
            `;
            previewList.appendChild(li);
        });
        
        document.getElementById('preview').style.display = 'block';
        document.getElementById('action-buttons').style.display = 'flex';
    }

    function calculateNewSchedule(selectedSessions, startDate, sessionsPerWeek) {
        const schedule = [];
        let currentDate = new Date(startDate);
        const daysGap = sessionsPerWeek === 2 ? 3 : 2; // 3 days for 2x/week, 2 days for 3x/week
        
        selectedSessions.forEach(session => {
            // Skip Sundays
            while (currentDate.getDay() === 0) {
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            schedule.push({
                session_id: session.session_id,
                session_number: session.session_number,
                old_date: session.session_date,
                new_date: formatDateForDB(currentDate),
                new_time: '09:00:00' // Default time
            });
            
            // Move to next session date
            currentDate.setDate(currentDate.getDate() + daysGap);
        });
        
        return schedule;
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${days[d.getDay()]}, ${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
    }

    function formatDateForDB(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedIds = Array.from(document.querySelectorAll('input[name="sessions[]"]:checked')).map(cb => parseInt(cb.value));
        const newStartDate = document.getElementById('new_start_date').value;
        const selectedSessions = sessions.filter(s => selectedIds.includes(s.session_id));
        const newSchedule = calculateNewSchedule(selectedSessions, newStartDate, plan.sessions_per_week);
        
        // Send to server
        fetch('/dheergayu/public/api/reschedule-treatment-sessions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                plan_id: <?= $plan_id ?>,
                new_schedule: newSchedule
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Schedule updated! Patient will be notified to confirm the new dates.');
                window.opener.location.reload();
                window.close();
            } else {
                alert('Error: ' + (data.message || 'Failed to update schedule'));
            }
        })
        .catch(err => {
            alert('Network error: ' + err.message);
        });
    });
    </script>
</body>
</html>