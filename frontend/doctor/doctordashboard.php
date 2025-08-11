<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Dashboard</title>
    <link rel="stylesheet" href="css/doctordashboard.css?v=1.3">
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
                <div class="user-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
                    </svg>
                </div>
                <span class="user-role">Doctor</span>
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