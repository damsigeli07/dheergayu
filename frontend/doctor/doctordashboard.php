<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/doctordashboard.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <button class="nav-btn active">Appointments</button>
                <a href="patienthistory.php" class="nav-btn">Patient History</a>
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
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>

        </div>
    </header>

    <main class="main-content">
        <!-- Remove the old tabs since navigation is now in the header -->
        <div class="search-container">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search" class="search-input">
            </div>
        </div>

        <div class="table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Patient No.</th>
                        <th>Patient Name</th>
                        <th>Appointment No.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1001</td>
                        <td>John Smith</td>
                        <td>1</td>
                        <td class="actions">
                            <a href="doctorconsultform.php">
                            <button class="btn-start">Start Consultation</button></a>
                            <a href="appointmentview.php">
                            <button class="btn-view">View</button></a>
                            <a href="appointmentedit.php">
                            <button class="btn-edit">Edit</button></a>
                        </td>
                    </tr>
                    <tr>
                        <td>1002</td>
                        <td>Sarah Johnson</td>
                        <td>2</td>
                        <td class="actions">
                            <a href="doctorconsultform.php">
                            <button class="btn-start">Start Consultation</button></a>
                            <a href="appointmentview.php">
                            <button class="btn-view">View</button></a>
                            <a href="appointmentedit.php">
                            <button class="btn-edit">Edit</button></a>
                        </td>
                    </tr>
                    <tr>
                        <td>1003</td>
                        <td>Mike Davis</td>
                        <td>3</td>
                        <td class="actions">
                            <a href="doctorconsultform.php">
                            <button class="btn-start">Start Consultation</button></a>
                            <a href="appointmentview.php">
                            <button class="btn-view">View</button></a>
                            <a href="appointmentedit.php">
                            <button class="btn-edit">Edit</button></a>
                        </td>
                    </tr>
                    <tr>
                        <td>1009</td>
                        <td>Emma Wilson</td>
                        <td>4</td>
                        <td class="actions">
                            <a href="doctorconsultform.php">
                            <button class="btn-start">Start Consultation</button>
                            <a href="appointmentview.php">
                            <button class="btn-view">View</button></a>
                            <a href="appointmentedit.php">
                            <button class="btn-edit">Edit</button></a>
                        </td>
                    </tr>
                    <tr>
                        <td>1010</td>
                        <td>David Brown</td>
                        <td>5</td>
                        <td class="actions">
                            <a href="doctorconsultform.php">
                            <button class="btn-start">Start Consultation</button>
                            <a href="appointmentview.php">
                            <button class="btn-view">View</button></a>
                            <a href="appointmentedit.php">
                            <button class="btn-edit">Edit</button></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>