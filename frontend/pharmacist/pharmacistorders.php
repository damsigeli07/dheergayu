<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="css/pharmacistorders.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="pharmacisthome.php" class="nav-btn">Home</a>
                <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
                <button class="nav-btn active">Orders</button>
                <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 7.76142 9.23858 12 12 12Z" fill="currentColor"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z" fill="currentColor"/>
                    </svg>
                </div>
                <span class="user-role">Pharmacist</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Orders Table -->
        <div class="orders-container">
            <div class="table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Appt Id</th>
                            <th>Products</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>28 Jul</td>
                            <td>Arjun Patel</td>
                            <td>APT-32</td>
                            <td>Herbal Pain Oil - 2 bottles<br>Anti-inflammatory - 1 pack</td>
                            <td>Rs. 1250</td>
                            <td><span class="status-btn pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>27 Jul</td>
                            <td>Priya Sharma</td>
                            <td>APT-35</td>
                            <td>Neem Paste - 1 container<br>Steam Herbs - 2 packets</td>
                            <td>Rs. 980</td>
                            <td><span class="status-btn paid">Paid</span></td>
                        </tr>
                        <tr>
                            <td>26 Jul</td>
                            <td>Ravi Kumar</td>
                            <td>APT-28</td>
                            <td>Turmeric Powder - 500g<br>Massage Oil - 1 bottle</td>
                            <td>Rs. 750</td>
                            <td><span class="status-btn preparing">Preparing</span></td>
                        </tr>
                        <tr>
                            <td>25 Jul</td>
                            <td>Maya Singh</td>
                            <td>APT-41</td>
                            <td>Herbal Steam Mix - 3 packets<br>Pain Relief Gel - 2 tubes</td>
                            <td>Rs. 1180</td>
                            <td><span class="status-btn paid">Paid</span></td>
                        </tr>
                        <tr>
                            <td>24 Jul</td>
                            <td>Suresh Reddy</td>
                            <td>APT-19</td>
                            <td>Ayurvedic Tablets - 2 packs<br>Medicated Oil - 1 bottle</td>
                            <td>Rs. 890</td>
                            <td><span class="status-btn cancelled">Cancelled</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Bar -->
            <div class="summary-bar">
                <div class="summary-left">
                    <span class="summary-item">Total Orders Today: 5</span>
                    <span class="summary-item">Pending: 1</span>
                    <span class="summary-item">Paid: 2</span>
                    <span class="summary-item">Preparing: 1</span>
                    <span class="summary-item">Cancelled: 1</span>
                </div>
                <div class="summary-right">
                    <span class="revenue">Total Revenue: Rs. 5,050</span>
                </div>
            </div>
        </div>
    </main>
</body>
</html> 