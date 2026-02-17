<?php
require_once __DIR__ . '/../../../core/bootloader.php';
// Popup-based treatment selector — returns selection to opener via postMessage
$treatments = [];
try {
    // Use Core Database if available
    if (class_exists('\\Core\\Database')) {
        $db = \Core\Database::connect();
        $stmt = $db->prepare("SELECT treatment_id AS id, treatment_name AS name, price FROM treatment_list WHERE status = 'Active' ORDER BY treatment_name ASC");
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $treatments[] = $row;
        }
        $stmt->close();
    } else {
        // Fallback to config DB connection for environments where Core isn't available
        require_once __DIR__ . '/../../../config/config.php';
        if (isset($conn) && $conn) {
            $stmt = $conn->prepare("SELECT treatment_id AS id, treatment_name AS name, price FROM treatment_list WHERE status = 'Active' ORDER BY treatment_name ASC");
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $treatments[] = $row;
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    $treatments = [];
}
$appointment_id = $_GET['appointment_id'] ?? '';
$patient_id = $_GET['patient_id'] ?? ''; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Select Treatment</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root { --accent: #FFB84D; }
        body { font-family: Arial, Helvetica, sans-serif; background:#f6f8fa; color:#222; padding:18px; }
        .card { max-width:780px; margin:12px auto; background:#fff; border-radius:10px; padding:22px; box-shadow:0 8px 24px rgba(20,30,40,0.06); }
        .page-title { font-size:22px; font-weight:700; color:#000; text-align:center; margin-bottom:14px; }
        .section { margin-top:12px; }
        .section-title { font-weight:600; color:#000; margin-bottom:10px; }
        select, input[type=date], textarea { width:100%; padding:12px; border-radius:8px; border:1px solid #e6e6e6; font-size:14px; background:#fff; }
        .field { margin-top:12px; }
        .slots { margin-top:12px; min-height:54px; }
        .slot-btn { display:inline-block; margin:6px 8px 0 0; padding:10px 14px; border-radius:10px; border:1px solid #e1e1e1; background:#fff; cursor:pointer; color:#000; }
        .slot-btn.selected { background:var(--accent); color:#fff; border-color:rgba(0,0,0,0.06); }
        .slot-btn.unavailable { background:#f5f5f5; color:#888; cursor:not-allowed; border-style:dashed; }
        .actions { margin-top:18px; text-align:right; }
        .btn-save { background:var(--accent); color:#000; padding:10px 16px; border:none; border-radius:8px; font-size:15px; cursor:pointer; }
        .btn-save:hover { filter:brightness(0.95); }
        .btn-cancel { margin-left:8px; padding:10px 14px; border-radius:8px; border:1px solid #e6e6e6; background:#fff; cursor:pointer; }
        .hint { color:#666; margin-bottom:6px; }
    </style>
</head>
<body>

<div class="card">
    <div class="page-title">Select Treatment Details</div>

    <div class="section">
        <div class="section-title">Select Treatment</div>
        <div class="field">
            <select id="treatment_id">
                <option value="">-- Choose Treatment Type --</option>
                <?php foreach ($treatments as $t): ?>
                    <option value="<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['name']) ?>" data-price="<?= htmlspecialchars($t['price']) ?>"><?= htmlspecialchars($t['name']) ?> - Rs <?= number_format($t['price'], 2) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="hint" style="margin-top:6px;">Price: <strong id="treatment_price_display">Rs 0.00</strong></div>
        </div>

        <div class="field">
            <label class="hint">Treatment Date</label>
            <input type="date" id="treatment_date" />
        </div>

        <div class="field">
            <label class="hint">Description (optional)</label>
            <textarea id="treatment_description" rows="3" placeholder="Optional notes (patient notes, requirements)"></textarea>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Available Slots</div>
        <div class="hint" id="slots_hint">Select a date first</div>
        <div class="slots" id="time_slots_container"></div>
    </div>

    <div class="actions">
        <button type="button" id="save_selection" class="btn-save">Save selection</button>
        <button type="button" id="cancel_btn" class="btn-cancel">Cancel</button>
    </div>
</div>

<script>
    const appointmentId = '<?= addslashes($appointment_id) ?>';
    const patientId = '<?= addslashes($patient_id) ?>';
    const treatmentSelect = document.getElementById('treatment_id');
    const dateInput = document.getElementById('treatment_date');
    const slotsContainer = document.getElementById('time_slots_container');
    const slotsHint = document.getElementById('slots_hint');

    function updatePriceDisplay(){
        const price = parseFloat(treatmentSelect.selectedOptions[0]?.dataset.price || 0);
        document.getElementById('treatment_price_display').textContent = 'Rs ' + price.toFixed(2);
    }

    // min date = tomorrow
    (function setMinDate(){
        const today = new Date();
        const tomorrow = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];
        if (!dateInput.value) {
            dateInput.value = dateInput.min;
        }
    })();

    treatmentSelect.addEventListener('change', function(){ updatePriceDisplay(); loadAvailableSlots(); });
    dateInput.addEventListener('change', loadAvailableSlots);
    // initialize display
    updatePriceDisplay();

    function loadAvailableSlots(){
    const tId = treatmentSelect.value;
    const date = dateInput.value;
    slotsContainer.innerHTML = ''; // clear old buttons

    if (!tId || !date) {
        slotsHint.style.display = 'block';
        slotsHint.textContent = 'Select treatment and date first.';
        return;
    }

    slotsHint.textContent = 'Loading slots...';

    const form = new FormData();
    form.append('treatment_id', tId);
    form.append('date', date);

    fetch('/dheergayu/public/api/treatment_selection.php?action=loadSlots', { method: 'POST', body: form })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) {
                slotsHint.style.display = 'block';
                slotsHint.textContent = resp.message || 'Failed to load slots';
                return;
            }

            const seen = new Set();
            const slots = (resp.slots || []).filter(s => {
                if (seen.has(s.slot_time)) return false; // filter duplicate times
                seen.add(s.slot_time);
                return true;
            }).sort((a, b) => {
                // Convert time strings to comparable format (HH:MM:SS)
                const timeA = a.slot_time.split(':').map(Number);
                const timeB = b.slot_time.split(':').map(Number);
                
                // Compare hours first, then minutes, then seconds
                if (timeA[0] !== timeB[0]) return timeA[0] - timeB[0];
                if (timeA[1] !== timeB[1]) return timeA[1] - timeB[1];
                return (timeA[2] || 0) - (timeB[2] || 0);
            });

            if (slots.length === 0) {
                slotsHint.style.display = 'block';
                slotsHint.textContent = 'No slots defined for this treatment';
                return;
            }

            slotsHint.style.display = 'none';
            slots.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn';
                btn.textContent = s.slot_time;
                btn.dataset.slotId = s.slot_id;

                let disableSlot = false;

            // Check if slot is booked
            if (parseInt(s.booked, 10)) {
                disableSlot = true;
            }

            // Check if date is today and slot time has passed
            const today = new Date();
            const slotDateTime = new Date(date + ' ' + s.slot_time);
            if (slotDateTime <= today) {
                disableSlot = true;
            }

            if (disableSlot) {
                btn.classList.add('unavailable');
                btn.disabled = true;
                btn.textContent = s.slot_time + ' — Unavailable';
            } else {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                });
            }

                slotsContainer.appendChild(btn);
            });
        })
        .catch(() => {
            slotsHint.style.display = 'block';
            slotsHint.textContent = 'Network error while loading slots';
        });
}


    document.getElementById('cancel_btn').addEventListener('click', () => window.close());

    document.getElementById('save_selection').addEventListener('click', () => {
        const tEl = treatmentSelect;
        const tId = tEl.value;
        const tName = tEl.selectedOptions[0]?.getAttribute('data-name') || tEl.selectedOptions[0]?.textContent || '';
        const date = dateInput.value;
        const selectedBtn = document.querySelector('.slot-btn.selected');
        const description = document.getElementById('treatment_description').value.trim();
        if (!tId || !date || !selectedBtn) { alert('Please select treatment, date and time slot.'); return; }
        const time = selectedBtn.textContent.replace(' — Unavailable','').trim();

        const form = new FormData();
        form.append('treatment_id', tId);
        form.append('slot_id', selectedBtn.dataset.slotId);
        form.append('date', date);
        form.append('description', description);
        if (patientId) {
            form.append('patient_id', patientId);
        }

        fetch('/dheergayu/public/api/treatment_selection.php?action=save', {
            method: 'POST',
            body: form
        }).then(r => r.json()).then(resp => {
            if (resp && resp.success) {
                const message = { type: 'treatment_selected', payload: { id: tId, name: tName, date: date, time: time, description: description, price: parseFloat(tEl.selectedOptions[0]?.dataset.price || 0), booking_id: resp.booking_id || null } };
                if (window.opener) window.opener.postMessage(JSON.stringify(message), '*');
                window.close();
            } else {
                alert('Error saving selection: ' + (resp.message || ''));
                if (resp.message && resp.message.toLowerCase().includes('booked')) {
                    loadAvailableSlots();
                }
            }
        }).catch(() => { alert('Network error saving selection.'); });
    });
</script>

</body>
</html>
