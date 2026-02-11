<?php
session_start();

require_once __DIR__ . '/../../../core/bootloader.php';
require_once __DIR__ . '/../../../app/Models/ProductRequestModel.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'supplier') {
    header("Location: ../patient/login.php");
    exit();
}

$supplierId = $_SESSION['user_id'] ?? null;
if (!$supplierId) {
    header("Location: ../patient/login.php");
    exit();
}

$productRequestModel = new ProductRequestModel($conn);
$initialRequests = $productRequestModel->getRequestsBySupplier($supplierId);

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
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="supplierRequestsBody">
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">
                            Loading product requests...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
const supplierId = <?= json_encode($supplierId) ?>;
const requestsBody = document.getElementById('supplierRequestsBody');
const initialRequests = <?= json_encode($initialRequests) ?>;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function buildActionButtons(request) {
    const disabled = request.status !== 'pending' ? 'disabled' : '';
    return `
        <button class="btn delivered" data-request-id="${request.id}" ${disabled}>Mark as Delivered</button>
    `;
}

function renderRequests(requests) {
    if (!Array.isArray(requests) || requests.length === 0) {
        requestsBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding: 20px;">
                    No product requests yet. Pharmacists can send requests from their dashboard.
                </td>
            </tr>
        `;
        return;
    }

    const rows = requests.map((request) => {
        const status = (request.status || 'pending').toLowerCase();
        

        return `
            <tr>
                <td>${escapeHtml(request.product_name || 'Unknown')}</td>
                <td>${escapeHtml(String(request.quantity ?? '0'))}</td>
                <td>${escapeHtml(request.request_date || '-')}</td>
                <td>
                    <span class="status-badge status-${status}">
                        ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}
                    </span>
                </td>
                <td>${buildActionButtons(request)}</td>
            </tr>
        `;
    }).join('');

    requestsBody.innerHTML = rows;
}

function renderInitial() {
    if (Array.isArray(initialRequests) && initialRequests.length > 0) {
        renderRequests(initialRequests);
    } else {
        requestsBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding: 20px;">
                    No product requests yet. Pharmacists can send requests from their dashboard.
                </td>
            </tr>
        `;
    }
}

async function loadSupplierRequests() {
    try {
        const response = await fetch(`/dheergayu/public/api/get-supplier-requests.php?supplier_id=${supplierId}`);
        const data = await response.json();

        if (data.success) {
            renderRequests(data.requests);
        } else {
            requestsBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px; color: #c0392b;">
                        ${escapeHtml(data.message || 'Failed to load requests.')}
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Failed to load supplier requests:', error);
        requestsBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align:center; padding: 20px; color: #c0392b;">
                    Error loading requests. Please refresh the page.
                </td>
            </tr>
        `;
    }
}

document.addEventListener("click", async function(e) {
    if (e.target.classList.contains("delivered")) {
        const requestId = e.target.getAttribute("data-request-id");

        const formData = new FormData();
        formData.append("request_id", requestId);

        const response = await fetch('/dheergayu/public/api/update-request-status.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert("Marked as Delivered!");
            loadSupplierRequests();
        } else {
            alert("Failed to update status.");
        }
    }
});


renderInitial();
loadSupplierRequests();
</script>

</body>
</html>
