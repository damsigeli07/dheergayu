<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacisthome.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <button class="nav-btn active">Home</button>
                <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
                <a href="pharmacistorders.php" class="nav-btn">Orders</a>
                <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Pharmacist</span>

        <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>

            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Main Content -->
        <div class="main-dashboard">
            <h2 class="section-title">Patient Orders Summary</h2>
            
            <div class="orders-overview">
                <div class="overview-card">
                    <h3 class="card-title">Current Orders</h3>
                    <div class="order-item">
                        <span class="consultation-id">Consultation #101</span>
                        <span class="patient-name">John Doe</span>
                        <div class="medicines">
                            <span class="medicine">Paspanguwa Pack x2</span>
                            <span class="medicine">Asamodagam Spirit x1</span>
                        </div>
                        <span class="status pending">Pending</span>
                    </div>
                    <div class="order-item">
                        <span class="consultation-id">Consultation #102</span>
                        <span class="patient-name">Jane Smith</span>
                        <div class="medicines">
                            <span class="medicine">Siddhalepa Balm x1</span>
                            <span class="medicine">Dashamoolarishta x2</span>
                        </div>
                        <span class="status pending">Pending</span>
                    </div>
                </div>
                
                <div class="inventory-alerts">
                    <div class="alert-item">
                        <h4 class="alert-title">⚠️ Low Stock Items</h4>
                        <ul class="alert-list">
                            <li>Turmeric Powder - 2 kg remaining</li>
                            <li>Ayurvedic Massage Oil - 1 bottle left</li>
                            <li>Herbal Steam Herbs - 3 packets</li>
                        </ul>
                    </div>
                    <div class="alert-item">
                        <h4 class="alert-title">⏰ Expiring Soon</h4>
                        <ul class="alert-list">
                            <li>Neem Paste - Expires in 5 days</li>
                            <li>Herbal Tea Mix - Expires in 7 days</li>
                            <li>Medicated Oil - Expires in 10 days</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
