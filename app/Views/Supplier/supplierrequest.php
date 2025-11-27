<?php
session_start();

require_once __DIR__ . '/../../../core/bootloader.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/ProductRequestModel.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

$supplierId = $_SESSION['user_id'] ?? null;
$requests = [];
$requestsError = '';

if ($supplierId) {
    try {
        $productRequestModel = new ProductRequestModel($conn);
        $requests = $productRequestModel->getRequestsBySupplier($supplierId);
    } catch (Throwable $e) {
        $requestsError = 'Failed to load requests. Please try again later.';
    }
} else {
    $requestsError = 'Unable to detect supplier account. Please log in again.';
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Request - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/supplierrequest.css">
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <header class="header">
      <div class="header-top">
          <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
          <h1 class="header-title">Dheergayu</h1>
      </div>
      
      <nav class="navigation">
          <a href="supplierdashboard.php" class="nav-btn">Home</a>
          <button class="nav-btn active">Request</button>
      </nav>
      
      <div class="user-section">
          <div class="user-icon" id="user-icon">👤</div>
          <span class="user-role">Supplier</span>
          <!-- Dropdown -->
          <div class="user-dropdown" id="user-dropdown">
              <a href="supplierprofile.php" class="profile-btn">Profile</a>
              <a href="../patient/login.php" class="logout-btn">Logout</a>
          </div>
      </div>
  </header>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Requested Product Summary</h1>
            <p class="page-subtitle">Review and manage product requests</p>
        </div>

        <div class="table-box">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($requestsError): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px; color: #c0392b;">
                                <?= htmlspecialchars($requestsError) ?>
                            </td>
                        </tr>
                    <?php elseif (empty($requests)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">
                                No product requests yet. Pharmacists can send requests from their dashboard.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $row): 
                            $status = strtolower($row['status'] ?? 'pending');
                            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                            $displayName = $fullName !== '' ? $fullName : 'Pharmacist #' . ($row['pharmacist_id'] ?? 'N/A');
                            $disabledAttr = $status !== 'pending' ? 'disabled' : '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row["product_name"] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars((string)($row["quantity"] ?? '0')) ?></td>
                            <td><?= htmlspecialchars($row["request_date"] ?? '-') ?></td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars($status) ?>">
                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($displayName) ?></td>
                            <td>
                                <button class="btn accept" data-request-id="<?= (int)($row['id'] ?? 0) ?>" <?= $disabledAttr ?>>Accept</button>
                                <button class="btn reject" data-request-id="<?= (int)($row['id'] ?? 0) ?>" <?= $disabledAttr ?>>Reject</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
const supplierId = <?= json_encode($_SESSION['user_id']) ?>;

function loadSupplierRequests() {
    fetch('/dheergayu/public/api/get-supplier-requests.php?supplier_id=' + supplierId)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('supplierRequestsBody');
            if (data.success && data.requests.length > 0) {
                tbody.innerHTML = data.requests.map(req => `
                    <tr>
                        <td>${req.product_name}</td>
                        <td>${req.quantity}</td>
                        <td>${req.request_date}</td>
                        <td><span class="status-badge status-${req.status}">${req.status}</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr><td colspan="4" style="text-align:center; padding:15px;">No requests found.</td></tr>
                `;
            }
        })
        .catch(err => console.error(err));
}

loadSupplierRequests();
</script>

</body>
</html>
