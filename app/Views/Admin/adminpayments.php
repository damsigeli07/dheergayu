<?php
require_once __DIR__ . '/../../includes/auth_admin.php';
require_once __DIR__ . '/../../../config/config.php';

$ordersQuery = "SELECT * FROM orders ORDER BY created_at DESC";
$ordersResult = $conn->query($ordersQuery);
$orders = [];
if ($ordersResult) {
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }
}

$totalRevenue = 0;
$productCount = 0;
$appointmentCount = 0;
$treatmentPlanCount = 0;
$dispensedCount = 0;

function classifyOrder($items) {
    if (strpos($items, 'Dispensed:') === 0)      return 'dispensed';
    if (strpos($items, 'Treatment Plan #') === 0) return 'treatment-plan';
    if (preg_match('/^(Consultation|Treatment) #\d+/i', $items)) return 'appointment';
    return 'product';
}

foreach ($orders as $order) {
    $totalRevenue += $order['amount'];
    $type = classifyOrder($order['order_items'] ?? '');
    if ($type === 'product') $productCount++;
    elseif ($type === 'appointment') $appointmentCount++;
    elseif ($type === 'treatment-plan') $treatmentPlanCount++;
    else $dispensedCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payments</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/adminorders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="has-sidebar">

    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
            <h1 class="header-title">Dheergayu</h1>
        </div>

        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn">Products</a>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn">Users</a>
            <a href="adminpatients.php" class="nav-btn">Patients</a>
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <button class="nav-btn active">Payments</button>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
                <a href="adminreports.php" class="nav-btn">Reports</a>
        </nav>

        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h1>Payments</h1>
            <p>All payments across product orders, appointments, and treatment plans</p>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Rs. <?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg,#4CAF50,#45a049)">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $productCount; ?></div>
                    <div class="stat-label">Product Orders</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg,#ff9800,#f57c00)">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $appointmentCount; ?></div>
                    <div class="stat-label">Appointment Payments</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg,#9c27b0,#7b1fa2)">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $treatmentPlanCount; ?></div>
                    <div class="stat-label">Treatment Plan Payments</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg,#2e7d32,#43a047)">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $dispensedCount; ?></div>
                    <div class="stat-label">Dispensed</div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('all', this)">All</button>
            <button class="tab-btn" onclick="showTab('product', this)">Product Orders</button>
            <button class="tab-btn" onclick="showTab('appointment', this)">Appointment Payments</button>
            <button class="tab-btn" onclick="showTab('treatment-plan', this)">Treatment Plan Payments</button>
            <button class="tab-btn" onclick="showTab('dispensed', this)">Dispensed</button>
        </div>

        <!-- Payments Table -->
        <div class="orders-section">
            <div class="section-header">
                <h2 id="tabTitle">All Payments</h2>
                <div class="filters">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="orders-table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="no-orders">
                                <i class="fas fa-receipt"></i>
                                <p>No payments found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order):
                                $type = classifyOrder($order['order_items'] ?? '');
                            ?>
                            <tr class="order-row"
                                data-status="<?php echo htmlspecialchars($order['status']); ?>"
                                data-type="<?php echo $type; ?>">
                                <td class="order-id"><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td class="customer-info">
                                    <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($order['order_items'] ?? '-'); ?></td>
                                <td class="amount">Rs. <?php echo number_format($order['amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="payment-method"><?php echo ucfirst($order['payment_method']); ?></td>
                                <td class="date"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const tabTitles = {
            all: 'All Payments',
            product: 'Product Orders',
            appointment: 'Appointment Payments',
            'treatment-plan': 'Treatment Plan Payments',
            dispensed: 'Dispensed'
        };

        function showTab(type, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tabTitle').textContent = tabTitles[type];
            applyFilters(type, document.getElementById('statusFilter').value);
        }

        function applyFilters(type, status) {
            document.querySelectorAll('.order-row').forEach(row => {
                const typeMatch = type === 'all' || row.dataset.type === type;
                const statusMatch = status === '' || row.dataset.status === status;
                row.style.display = typeMatch && statusMatch ? '' : 'none';
            });
        }

        document.getElementById('statusFilter').addEventListener('change', function () {
            const activeTab = document.querySelector('.tab-btn.active');
            const labelMap = {
                'All': 'all',
                'Product Orders': 'product',
                'Appointment Payments': 'appointment',
                'Treatment Plan Payments': 'treatment-plan',
                'Dispensed': 'dispensed'
            };
            const type = activeTab ? (labelMap[activeTab.textContent.trim()] || 'all') : 'all';
            applyFilters(type, this.value);
        });
    </script>

</body>
</html>
