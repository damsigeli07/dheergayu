<?php
session_start();
require_once __DIR__ . '/../../../core/bootloader.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/SupplierModel.php';

use App\Models\BatchModel;

$supplierModel = new SupplierModel($conn);
$suppliers = $supplierModel->getAllSuppliers();

$batchModel = new BatchModel();
$products = $batchModel->getProducts();

$pharmacist_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Products - Pharmacist Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistrequest.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="pharmacisthome.php" class="nav-btn">Home</a>
            <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
            <a href="pharmacistorders.php" class="nav-btn">Orders</a>
            <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            <button class="nav-btn active">Request</button>
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
        <div class="request-container">
            <h2 class="section-title">Request Products from Suppliers</h2>
            
            <!-- Request Form -->
            <div class="request-form-container">
                <form id="requestForm" class="request-form">
                    <div class="form-group">
                        <label for="supplier">Select Supplier *</label>
                        <select id="supplier" name="supplier_id" required>
                            <option value="">-- Choose a Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= htmlspecialchars($supplier['id']) ?>">
                                    <?= htmlspecialchars($supplier['supplier_name']) ?> 
                                    (<?= htmlspecialchars($supplier['contact_person']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product">Product Name *</label>
                        <input type="text" id="product" name="product_name" list="product_list" required 
                               placeholder="Enter or select product name">
                        <datalist id="product_list">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= htmlspecialchars($product['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" min="1" required 
                               placeholder="Enter quantity">
                    </div>

                    <div class="form-group">
                        <label for="request_date">Request Date *</label>
                        <input type="date" id="request_date" name="request_date" required 
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Submit Request</button>
                        <button type="reset" class="btn-reset">Clear Form</button>
                    </div>
                </form>
            </div>

            <!-- Request History -->
            <div class="request-history">
                <h3 class="history-title">My Request History</h3>
                <div class="table-container">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Supplier</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
                                    Loading requests...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Success/Error Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <div id="messageContent"></div>
        </div>
    </div>

    <script>
        const pharmacistId = <?= json_encode($pharmacist_id) ?>;

        // Set today's date as default
        document.getElementById('request_date').valueAsDate = new Date();

        // Load request history
        function loadRequestHistory() {
            if (!pharmacistId) {
                document.getElementById('requestsTableBody').innerHTML = 
                    '<tr><td colspan="5" style="text-align: center; padding: 20px;">Please log in to view requests.</td></tr>';
                return;
            }

            fetch('/dheergayu/public/api/get-requests.php?pharmacist_id=' + pharmacistId)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('requestsTableBody');
                    if (data.success && data.requests && data.requests.length > 0) {
                        tbody.innerHTML = data.requests.map(request => `
                            <tr>
                                <td>${escapeHtml(request.product_name)}</td>
                                <td>${request.quantity}</td>
                                <td>${escapeHtml(request.supplier_name || 'N/A')}</td>
                                <td>${request.request_date}</td>
                                <td><span class="status-badge status-${request.status}">${request.status}</span></td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No requests found.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading requests:', error);
                    document.getElementById('requestsTableBody').innerHTML = 
                        '<tr><td colspan="5" style="text-align: center; padding: 20px;">Error loading requests.</td></tr>';
                });
        }

        // Submit request form
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!pharmacistId) {
                showMessage('Error', 'Please log in to submit requests.', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('pharmacist_id', pharmacistId);
            formData.append('action', 'create_request');

            fetch('/dheergayu/public/api/submit-request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Success', data.message || 'Request submitted successfully!', 'success');
                    this.reset();
                    document.getElementById('request_date').valueAsDate = new Date();
                    loadRequestHistory();
                } else {
                    showMessage('Error', data.error || 'Failed to submit request.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error', 'An error occurred while submitting the request.', 'error');
            });
        });

        function showMessage(title, message, type) {
            const modal = document.getElementById('messageModal');
            const content = document.getElementById('messageContent');
            content.innerHTML = `
                <h3 style="color: ${type === 'success' ? '#28a745' : '#dc3545'};">
                    ${title}
                </h3>
                <p>${message}</p>
            `;
            modal.style.display = 'block';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load history on page load
        loadRequestHistory();
    </script>
</body>
</html>

