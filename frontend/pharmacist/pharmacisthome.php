<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard</title>
    <link rel="stylesheet" href="css/pharmacisthome.css?v=1.1">
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
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" fill="currentColor"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
                    </svg>
                </div>
                <span class="user-role">Pharmacist</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Left Column -->
        <div class="left-column">
            <h2 class="section-title">Products to prepare for:</h2>
            
            <div class="popular-treatment">
                <h3 class="subsection-title">Today's In-Popular Treatment (From Staff)</h3>
            </div>

            <!-- Orders Section -->
            <div class="orders-section">
                <h3 class="orders-title">Orders to prepare today: 8</h3>
                <ul class="orders-list">
                    <li>• Herbal Pain Relief Oil - 3 bottles</li>
                    <li>• Anti-inflammatory Tablets - 5 packs</li>
                    <li>• Neem Paste - 2 containers</li>
                </ul>
            </div>

            <!-- Low Stock Alert -->
            <div class="alert-section">
                <div class="alert-header">
                    <span class="alert-icon">⚠</span>
                    <h3 class="alert-title">Low Stock Alerts!</h3>
                </div>
                <div class="alert-content">
                    <p class="critical-items-label">Critical Items:</p>
                    <ul class="critical-items-list">
                        <li>• Turmeric Powder - Only 2 kg remaining</li>
                        <li>• Ayurvedic Massage Oil - 1 bottle left</li>
                        <li>• Herbal Steam Herbs - 3 packets remaining</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- Today's Summary -->
            <div class="summary-section">
                <h3 class="summary-title">Today's Summary</h3>
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Orders:</span>
                        <span class="stat-value">8</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed:</span>
                        <span class="stat-value">3</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">In Progress:</span>
                        <span class="stat-value">2</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Pending:</span>
                        <span class="stat-value">3</span>
                    </div>
                </div>

                <div class="next-priority">
                    <h4 class="priority-title">Next Priority:</h4>
                    <p class="priority-text">Oil Massage preparations for 2:00 PM session</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="actions-section">
                <h3 class="actions-title">Quick Actions</h3>
                <div class="actions-grid">
                    <button class="action-btn primary">View Inventory</button>
                    <button class="action-btn secondary">Manage Orders</button>
                    <button class="action-btn tertiary">Stock Reports</button>
                    <button class="action-btn tertiary">Supplier Info</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>