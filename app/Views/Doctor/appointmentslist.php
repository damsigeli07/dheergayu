<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointments List</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctordashboard.css">
</head>
<body>
    <main class="main-content">
        <div class="table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient No.</th>
                        <th>Patient Name</th>
                        <th>Appointment No.</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)) : ?>
                        <?php foreach ($appointments as $apt) : ?>
                            <tr>
                                <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_no']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_no']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                <td><?= htmlspecialchars($apt['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No appointments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
