<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="../css_common/header.css">
    <script src="../js_common/header.js"></script>
    <link rel="stylesheet" href="css/pharmacistreports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="pharmacisthome.php" class="nav-btn">Home</a>
                <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
                <a href="pharmacistorders.php" class="nav-btn">Orders</a>
                <button class="nav-btn active">Reports</button>
                <a href="pharmacistsuppliers.php" class="nav-btn">Supplier Info</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
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
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="reports-container">
            <!-- Reports Header -->
            <div class="reports-header">
                <h2 class="reports-title">Analytics & Reports</h2>
                <p class="reports-subtitle">Monitor your inventory performance and stock levels</p>
            </div>


            <!-- Expiring Stock Alerts -->
            <div class="expiring-alerts">
                <h3>⚠️ Expiring Stock Alerts</h3>
                <div class="alert-item">
                    <span class="alert-product">Siddhalepa Balm</span>
                    <span class="alert-date">Expires: Nov 2025 (3 items)</span>
                </div>
                <div class="alert-item">
                    <span class="alert-product">Dashamoolarishta</span>
                    <span class="alert-date">Expires: Dec 2025 (2 items)</span>
                </div>
                <div class="alert-item">
                    <span class="alert-product">Kothalahimbutu Capsules</span>
                    <span class="alert-date">Expires: Jan 2026 (1 item)</span>
                </div>
            </div>

            <!-- Main Chart -->
            <div class="report-card full-width">
                <h3>Stock Levels by Product</h3>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>

            <!-- Generate Report Button -->
            <div class="generate-report-section">
                <button class="btn-generate-report" onclick="generateReport()">📊 Generate Detailed Report</button>
            </div>
        </div>
    </main>

    <script>
        // Stock Levels Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: ['Paspanguwa', 'Asamodagam', 'Siddhalepa', 'Dashamoolarishta', 'Kothalahimbutu', 'Neem Oil', 'Pinda Thailaya', 'Nirgundi Oil'],
                datasets: [{
                    label: 'Current Stock',
                    data: [12, 8, 3, 2, 1, 15, 3, 7],
                    backgroundColor: [
                        '#7a9b57',
                        '#8B7355',
                        '#D2691E',
                        '#D2691E',
                        '#D2691E',
                        '#7a9b57',
                        '#D2691E',
                        '#7a9b57'
                    ],
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                }
            }
        });

        // Generate Report Function
        function generateReport() {
            // Show loading state
            const button = document.querySelector('.btn-generate-report');
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating Report...';
            button.disabled = true;

            // Simulate report generation (replace with actual backend call)
            setTimeout(() => {
                // Here you would typically make an AJAX call to your backend
                // For now, we'll just show a success message
                button.innerHTML = '✅ Report Generated!';
                button.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.style.background = 'linear-gradient(135deg, #7a9b57, #6B8E23)';
                }, 3000);
            }, 2000);
        }
    </script>
</body>
</html>
