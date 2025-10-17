<?php
// public/test-api.php
// Test your API endpoint directly

session_start();

// Simulate logged-in user
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test Patient';
    $_SESSION['user_email'] = 'test@example.com';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #4ec9b0; }
        .test-section { background: #252526; border: 1px solid #3e3e42; padding: 15px; margin: 15px 0; border-radius: 4px; }
        input, button { padding: 8px; margin: 5px; font-family: monospace; }
        button { background: #0e639c; color: white; border: none; cursor: pointer; }
        button:hover { background: #1177bb; }
        .response { background: #1e1e1e; border: 1px solid #3e3e42; padding: 10px; margin-top: 10px; border-radius: 3px; max-height: 400px; overflow-y: auto; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        label { display: block; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 API Endpoint Test</h1>
        
        <div class="test-section">
            <h2>Book Consultation Test</h2>
            
            <label>Doctor ID:</label>
            <input type="number" id="doctorId" value="1" min="1">
            
            <label>Appointment Date:</label>
            <input type="date" id="appointDate" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            
            <label>Appointment Time:</label>
            <input type="text" id="appointTime" value="10:00" placeholder="HH:MM">
            
            <label>Patient Name:</label>
            <input type="text" id="patientName" value="Test Patient">
            
            <label>Email:</label>
            <input type="email" id="email" value="test@example.com">
            
            <label>Phone:</label>
            <input type="text" id="phone" value="1234567890">
            
            <label>Age:</label>
            <input type="number" id="age" value="30" min="1" max="150">
            
            <label>Gender:</label>
            <select id="gender">
                <option value="Male">Male</option>
                <option value="Female" selected>Female</option>
                <option value="Other">Other</option>
            </select>
            
            <button onclick="testBooking()">🚀 Test Booking</button>
        </div>

        <div class="test-section">
            <h2>Raw Response:</h2>
            <div id="response" class="response">Click "Test Booking" to see response...</div>
        </div>

        <div class="test-section">
            <h2>Parsed Response:</h2>
            <div id="parsed" class="response">Waiting for response...</div>
        </div>

        <div class="test-section">
            <h2>Debug Info:</h2>
            <p>Session User ID: <strong><?php echo $_SESSION['user_id'] ?? 'NOT SET'; ?></strong></p>
            <p>API Endpoint: <code>/dheergayu/public/api/book-consultation.php</code></p>
        </div>
    </div>

    <script>
        async function testBooking() {
            const formData = new FormData();
            formData.append('doctor_id', document.getElementById('doctorId').value);
            formData.append('appointment_date', document.getElementById('appointDate').value);
            formData.append('appointment_time', document.getElementById('appointTime').value);
            formData.append('patient_name', document.getElementById('patientName').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('age', document.getElementById('age').value);
            formData.append('gender', document.getElementById('gender').value);
            formData.append('payment_method', 'onsite');

            try {
                const response = await fetch('/dheergayu/public/api/book-consultation.php', {
                    method: 'POST',
                    body: formData
                });

                // Get raw text first
                const rawText = await response.text();
                document.getElementById('response').innerHTML = 
                    '<span class="' + (response.ok ? 'success' : 'error') + '">' +
                    'Status: ' + response.status + ' ' + response.statusText + '\n\n' +
                    'Raw Response:\n' + 
                    escapeHtml(rawText) +
                    '</span>';

                // Try to parse as JSON
                try {
                    const data = JSON.parse(rawText);
                    document.getElementById('parsed').innerHTML = 
                        '<span class="' + (data.success ? 'success' : 'error') + '">' +
                        'Parsed JSON:\n' + 
                        JSON.stringify(data, null, 2) +
                        '</span>';
                } catch (e) {
                    document.getElementById('parsed').innerHTML = 
                        '<span class="error">Failed to parse JSON:\n' + e.message + '</span>';
                }
            } catch (err) {
                document.getElementById('response').innerHTML = '<span class="error">Fetch Error:\n' + err.message + '</span>';
                document.getElementById('parsed').innerHTML = '<span class="error">No response</span>';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>