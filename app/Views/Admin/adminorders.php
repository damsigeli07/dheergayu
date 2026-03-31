<?php
require_once __DIR__ . '/../../../config/config.php';

// Fetch orders from database
$ordersQuery = "SELECT * FROM orders ORDER BY created_at DESC";
$ordersResult = $conn->query($ordersQuery);
$orders = [];
if ($ordersResult) {
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Calculate summary statistics
$totalOrders = count($orders);
$totalRevenue = 0;
$paidOrders = 0;
$pendingOrders = 0;

foreach ($orders as $order) {
    $totalRevenue += $order['amount'];
    if ($order['status'] == 'paid') {
        $paidOrders++;
    } elseif ($order['status'] == 'pending') {
        $pendingOrders++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Management</title>
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
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <button class="nav-btn active">Orders</button>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
        </nav>

        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="/dheergayu/app/Views/logout.php" class="dropdown-item">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h1>Orders Management</h1>
            <p>View and manage all customer orders</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $paidOrders; ?></div>
                    <div class="stat-label">Paid Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $pendingOrders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Rs. <?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-section">
            <div class="section-header">
                <h2>All Orders</h2>
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
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="no-orders">
                                <i class="fas fa-shopping-cart"></i>
                                <p>No orders found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr class="order-row" data-status="<?php echo $order['status']; ?>">
                                <td class="order-id"><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td class="customer-info">
                                    <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </td>
                                <td class="amount">Rs. <?php echo number_format($order['amount'], 2); ?></td>
                                <td class="status">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="payment-method"><?php echo ucfirst($order['payment_method']); ?></td>
                                <td class="date"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="actions">
                                    <button class="action-btn view-btn" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Order Details</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="orderDetails" class="modal-body">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Filter orders by status
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');

            rows.forEach(row => {
                const status = row.dataset.status;
                if (filterValue === '' || status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // View order details
        function viewOrderDetails(orderId) {
            // For now, just show a placeholder
            // In a real implementation, you'd fetch order details via AJAX
            const modal = document.getElementById('orderModal');
            const details = document.getElementById('orderDetails');

            details.innerHTML = `
                <div class="order-detail-section">
                    <h4>Order Information</h4>
                    <p><strong>Order ID:</strong> Loading...</p>
                    <p><strong>Status:</strong> Loading...</p>
                    <p><strong>Amount:</strong> Loading...</p>
                </div>
                <div class="order-detail-section">
                    <h4>Customer Information</h4>
                    <p><strong>Name:</strong> Loading...</p>
                    <p><strong>Email:</strong> Loading...</p>
                    <p><strong>Phone:</strong> Loading...</p>
                </div>
                <div class="order-detail-section">
                    <h4>Delivery Information</h4>
                    <p><strong>Address:</strong> Loading...</p>
                    <p><strong>City:</strong> Loading...</p>
                </div>
            `;

            modal.style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

</body>
</html>