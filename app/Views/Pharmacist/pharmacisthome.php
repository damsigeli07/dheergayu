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
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <button class="nav-btn active">Home</button>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
            <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            <a href="pharmacistrequest.php" class="nav-btn">Request</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Pharmacist</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <!-- Welcome Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pharmacist Dashboard</h1>
                <p class="dashboard-subtitle">Welcome back! Here's your overview for today.</p>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card pending-card">
                    <div class="card-icon">📋</div>
                    <div class="card-content">
                        <h3 class="card-label">Pending Orders</h3>
                        <p class="card-value">12</p>
                        <span class="card-change">+3 from yesterday</span>
                    </div>
                </div>
                
                <div class="summary-card dispatched-card">
                    <div class="card-icon">✅</div>
                    <div class="card-content">
                        <h3 class="card-label">Dispatched Today</h3>
                        <p class="card-value">8</p>
                        <span class="card-change">Ready for pickup</span>
                    </div>
                </div>
                
                <div class="summary-card low-stock-card">
                    <div class="card-icon">⚠️</div>
                    <div class="card-content">
                        <h3 class="card-label">Low Stock Items</h3>
                        <p class="card-value">5</p>
                        <span class="card-change">Requires attention</span>
                    </div>
                </div>
                
                <div class="summary-card expiring-card">
                    <div class="card-icon">⏰</div>
                    <div class="card-content">
                        <h3 class="card-label">Expiring Soon</h3>
                        <p class="card-value">7</p>
                        <span class="card-change">Within 10 days</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Orders Section -->
                <div class="orders-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Orders</h2>
                        <a href="pharmacistorders.php" class="view-all-link">View All →</a>
                    </div>
                    
                    <!-- Pending Orders -->
                    <div class="order-group">
                        <h3 class="order-group-title">
                            <span class="status-badge pending-badge">Pending</span>
                            <span class="order-count">5 orders</span>
                        </h3>
                        
                        <div class="order-list">
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Order #ORD-2024-001</span>
                                    <span class="order-date">Today, 10:30 AM</span>
                                </div>
                                <div class="order-patient">
                                    <strong>Patient:</strong> John Doe
                                </div>
                                <div class="order-medicines">
                                    <span class="medicine-tag">Ashwagandha Capsules x2</span>
                                    <span class="medicine-tag">Arawindasawaya x1</span>
                                    <span class="medicine-tag">Arjunarishtaya x1</span>
                                </div>
                                <div class="order-footer">
                                    <span class="consultation-ref">Consultation #101</span>
                                    <button class="action-button dispatch-btn">Mark as Dispatched</button>
                                </div>
                            </div>
                            
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Order #ORD-2024-002</span>
                                    <span class="order-date">Today, 09:15 AM</span>
                                </div>
                                <div class="order-patient">
                                    <strong>Patient:</strong> Jane Smith
                                </div>
                                <div class="order-medicines">
                                    <span class="medicine-tag">Kothalahimbutu Capsules x2</span>
                                    <span class="medicine-tag">Abayarishtaya x1</span>
                                    <span class="medicine-tag">Kanakasawaya x1</span>
                                </div>
                                <div class="order-footer">
                                    <span class="consultation-ref">Consultation #102</span>
                                    <button class="action-button dispatch-btn">Mark as Dispatched</button>
                                </div>
                            </div>
                            
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Order #ORD-2024-003</span>
                                    <span class="order-date">Today, 08:45 AM</span>
                                </div>
                                <div class="order-patient">
                                    <strong>Patient:</strong> Robert Johnson
                                </div>
                                <div class="order-medicines">
                                    <span class="medicine-tag">Chandanasawaya x1</span>
                                    <span class="medicine-tag">Amurtharishtaya x2</span>
                                    <span class="medicine-tag">Arawindasawaya x1</span>
                                </div>
                                <div class="order-footer">
                                    <span class="consultation-ref">Consultation #103</span>
                                    <button class="action-button dispatch-btn">Mark as Dispatched</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dispatched Orders -->
                    <div class="order-group">
                        <h3 class="order-group-title">
                            <span class="status-badge dispatched-badge">Dispatched</span>
                            <span class="order-count">3 orders</span>
                        </h3>
                        
                        <div class="order-list">
                            <div class="order-card dispatched">
                                <div class="order-header">
                                    <span class="order-id">Order #ORD-2024-004</span>
                                    <span class="order-date">Today, 11:00 AM</span>
                                </div>
                                <div class="order-patient">
                                    <strong>Patient:</strong> Sarah Williams
                                </div>
                                <div class="order-medicines">
                                    <span class="medicine-tag">Ashwagandha Capsules x2</span>
                                    <span class="medicine-tag">Arjunarishtaya x1</span>
                                </div>
                                <div class="order-footer">
                                    <span class="consultation-ref">Consultation #104</span>
                                    <span class="dispatched-label">✓ Dispatched</span>
                                </div>
                            </div>
                            
                            <div class="order-card dispatched">
                                <div class="order-header">
                                    <span class="order-id">Order #ORD-2024-005</span>
                                    <span class="order-date">Today, 10:00 AM</span>
                                </div>
                                <div class="order-patient">
                                    <strong>Patient:</strong> Michael Brown
                                </div>
                                <div class="order-medicines">
                                    <span class="medicine-tag">Kothalahimbutu Capsules x1</span>
                                    <span class="medicine-tag">Kanakasawaya x2</span>
                                </div>
                                <div class="order-footer">
                                    <span class="consultation-ref">Consultation #105</span>
                                    <span class="dispatched-label">✓ Dispatched</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div class="alerts-section">
                    <!-- Low Stock Alert -->
                    <div class="alert-card low-stock-alert">
                        <div class="alert-header">
                            <div class="alert-icon-wrapper">
                                <span class="alert-icon">⚠️</span>
                            </div>
                            <h3 class="alert-title">Low Stock Items</h3>
                        </div>
                        <div class="alert-content">
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Turmeric Powder</span>
                                    <span class="item-detail">2 kg remaining</span>
                                </div>
                                <span class="item-status critical">Critical</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Ayurvedic Massage Oil</span>
                                    <span class="item-detail">1 bottle left</span>
                                </div>
                                <span class="item-status critical">Critical</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Herbal Steam Herbs</span>
                                    <span class="item-detail">3 packets</span>
                                </div>
                                <span class="item-status warning">Low</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Neem Paste</span>
                                    <span class="item-detail">5 units</span>
                                </div>
                                <span class="item-status warning">Low</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Medicated Oil</span>
                                    <span class="item-detail">4 bottles</span>
                                </div>
                                <span class="item-status warning">Low</span>
                            </div>
                        </div>
                        <a href="pharmacistinventory.php" class="alert-action-link">View Inventory →</a>
                    </div>

                    <!-- Expiring Items Alert -->
                    <div class="alert-card expiring-alert">
                        <div class="alert-header">
                            <div class="alert-icon-wrapper">
                                <span class="alert-icon">⏰</span>
                            </div>
                            <h3 class="alert-title">Expiring Soon</h3>
                        </div>
                        <div class="alert-content">
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Neem Paste</span>
                                    <span class="item-detail">Expires in 5 days</span>
                                </div>
                                <span class="item-status urgent">Urgent</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Herbal Tea Mix</span>
                                    <span class="item-detail">Expires in 7 days</span>
                                </div>
                                <span class="item-status urgent">Urgent</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Medicated Oil</span>
                                    <span class="item-detail">Expires in 10 days</span>
                                </div>
                                <span class="item-status warning">Soon</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Dashamoolarishta</span>
                                    <span class="item-detail">Expires in 12 days</span>
                                </div>
                                <span class="item-status warning">Soon</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Paspanguwa Pack</span>
                                    <span class="item-detail">Expires in 15 days</span>
                                </div>
                                <span class="item-status warning">Soon</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Siddhalepa Balm</span>
                                    <span class="item-detail">Expires in 18 days</span>
                                </div>
                                <span class="item-status warning">Soon</span>
                            </div>
                            <div class="alert-item">
                                <div class="alert-item-info">
                                    <span class="item-name">Ayurvedic Massage Oil</span>
                                    <span class="item-detail">Expires in 20 days</span>
                                </div>
                                <span class="item-status warning">Soon</span>
                            </div>
                        </div>
                        <a href="pharmacistinventory.php" class="alert-action-link">View Inventory →</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
