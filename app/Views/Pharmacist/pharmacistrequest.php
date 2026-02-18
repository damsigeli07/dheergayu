<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../../core/bootloader.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/SupplierModel.php';

use App\Models\BatchModel;

$supplierModel = new SupplierModel($conn);
$suppliers = $supplierModel->getAllSuppliers();

// Admin products (from products table) – supplied by Ayurvedic Traders
$batchModel = new BatchModel();
$adminProducts = $batchModel->getProducts();

// Patient products (from patient_products table) – supplied by Herbal Suppliers Co.
$patientProducts = [];
$patientRes = $conn->query("SELECT product_id AS id, name FROM patient_products ORDER BY name");
if ($patientRes && $patientRes->num_rows > 0) {
    while ($row = $patientRes->fetch_assoc()) {
        $patientProducts[] = ['id' => $row['id'], 'name' => $row['name']];
    }
}

// Map supplier_id -> products: Herbal Suppliers Co. → patient (4), Ayurvedic Traders → admin (8)
$supplierProductsMap = [];
foreach ($suppliers as $s) {
    $name = $s['supplier_name'] ?? '';
    if (stripos($name, 'Herbal') !== false) {
        $supplierProductsMap[$s['id']] = $patientProducts;
    } elseif (stripos($name, 'Ayurvedic') !== false) {
        $supplierProductsMap[$s['id']] = $adminProducts;
    } else {
        $supplierProductsMap[$s['id']] = [];
    }
}

// Only use session user_id as pharmacist_id when the logged-in user is actually a pharmacist.
// Check both user_type and user_role (LoginController sets user_role only; Patient/login sets both).
$pharmacist_id = null;
if (!empty($_SESSION['user_id'])) {
    $role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '');
    if (strtolower((string)$role) === 'pharmacist') {
        $pharmacist_id = (int) $_SESSION['user_id'];
    }
}
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
            
            <div class="request-form-container">
                <form id="requestForm" class="request-form">
                    <div class="form-group">
                        <label for="supplier">Select Supplier *</label>
                        <select id="supplier" name="supplier_id" required>
                            <option value="">-- Choose a Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= (int)$supplier['id'] ?>">
                                    <?= htmlspecialchars($supplier['supplier_name']) ?> 
                                    (<?= htmlspecialchars($supplier['contact_person']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="productsSection" class="form-group products-section" style="display: none;">
                        <label>Select quantities for each product</label>
                        <div class="table-container products-table-wrap">
                            <table class="products-order-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="request_date">Request Date *</label>
                        <input type="date" id="request_date" name="request_date" required 
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit" id="btnSubmit" disabled>Submit Request</button>
                        <button type="reset" class="btn-reset">Clear Form</button>
                    </div>
                </form>
            </div>

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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">Loading requests...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <div id="messageContent"></div>
        </div>
    </div>

    <!-- Edit order line modal -->
    <div id="editRequestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditRequestModal()">&times;</span>
            <h3>Edit Order Line</h3>
            <form id="editRequestForm">
                <input type="hidden" id="edit_request_id" name="request_id">
                <div class="form-group">
                    <label for="edit_product_name">Product Name *</label>
                    <input type="text" id="edit_product_name" name="product_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_quantity">Quantity *</label>
                    <input type="number" id="edit_quantity" name="quantity" min="1" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save</button>
                    <button type="button" class="btn-reset" onclick="closeEditRequestModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const pharmacistId = <?= json_encode($pharmacist_id) ?>;
        const supplierProductsMap = <?= json_encode($supplierProductsMap) ?>;

        document.getElementById('request_date').valueAsDate = new Date();

        const supplierSelect = document.getElementById('supplier');
        const productsSection = document.getElementById('productsSection');
        const productsTableBody = document.getElementById('productsTableBody');
        const btnSubmit = document.getElementById('btnSubmit');

        function renderProductsTable() {
            const supplierId = supplierSelect.value;
            const products = supplierProductsMap[supplierId];
            productsTableBody.innerHTML = '';
            if (!products || products.length === 0) {
                productsSection.style.display = 'none';
                btnSubmit.disabled = true;
                return;
            }
            productsSection.style.display = 'block';
            btnSubmit.disabled = false;
            products.forEach(function(p) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td class="product-name-cell">' + escapeHtml(p.name) + '</td>' +
                    '<td><input type="number" class="quantity-input" name="qty_' + escapeHtml(String(p.id)) + '" data-product-name="' + escapeHtml(p.name) + '" min="0" value="0" placeholder="0"></td>';
                productsTableBody.appendChild(tr);
            });
        }

        supplierSelect.addEventListener('change', function() {
            renderProductsTable();
        });

        function loadRequestHistory() {
            if (!pharmacistId) {
                document.getElementById('requestsTableBody').innerHTML = 
                    '<tr><td colspan="6" style="text-align: center; padding: 20px;">Please log in to view requests.</td></tr>';
                return;
            }
            var url = '/dheergayu/public/api/get-requests.php?pharmacist_id=' + encodeURIComponent(pharmacistId) + '&_=' + Date.now();
            fetch(url, { credentials: 'same-origin', cache: 'no-store' })
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.text();
                })
                .then(function(text) {
                    var data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Invalid JSON from get-requests:', text);
                        throw new Error('Invalid response from server');
                    }
                    var tbody = document.getElementById('requestsTableBody');
                    if (!data.success) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #856404;">' + escapeHtml(data.error || 'Please log in to view requests.') + '</td></tr>';
                        return;
                    }
                    var requests = Array.isArray(data.requests) ? data.requests : [];
                    if (requests.length > 0) {
                        tbody.innerHTML = requests.map(function(request) {
                            var status = request.status || 'pending';
                            var isPending = (status === 'pending');
                            var safeProduct = escapeHtmlAttr(request.product_name || '');
                            var actions = isPending
                                ? '<button type="button" class="btn-edit-order" data-id="' + request.id + '" data-product="' + safeProduct + '" data-qty="' + request.quantity + '">Edit</button> '
                                + '<button type="button" class="btn-cancel-order" data-id="' + request.id + '" data-product="' + safeProduct + '">Cancel</button>'
                                : '<span class="no-actions">—</span>';
                            return '<tr><td>' + escapeHtml(request.product_name) + '</td><td>' + request.quantity + '</td><td>' + escapeHtml(request.supplier_name || 'N/A') + '</td><td>' + request.request_date + '</td><td><span class="status-badge status-' + status + '">' + status + '</span></td><td class="actions-cell">' + actions + '</td></tr>';
                        }).join('');
                        tbody.querySelectorAll('.btn-edit-order').forEach(function(btn) {
                            btn.addEventListener('click', openEditRequestModal);
                        });
                        tbody.querySelectorAll('.btn-cancel-order').forEach(function(btn) {
                            btn.addEventListener('click', cancelRequest);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No requests found.</td></tr>';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading requests:', error);
                    document.getElementById('requestsTableBody').innerHTML =
                        '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #721c24;">Error loading requests. Check console or try again.</td></tr>';
                });
        }

        function openEditRequestModal(e) {
            var btn = e.target;
            var id = btn.getAttribute('data-id');
            var product = btn.getAttribute('data-product');
            var qty = btn.getAttribute('data-qty');
            document.getElementById('edit_request_id').value = id;
            document.getElementById('edit_product_name').value = product;
            document.getElementById('edit_quantity').value = qty;
            document.getElementById('editRequestModal').style.display = 'block';
        }

        function closeEditRequestModal() {
            document.getElementById('editRequestModal').style.display = 'none';
        }

        document.getElementById('editRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'update_request');
            formData.append('pharmacist_id', pharmacistId);
            fetch('/dheergayu/public/api/submit-request.php', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        showMessage('Success', data.message || 'Order updated.', 'success');
                        closeEditRequestModal();
                        loadRequestHistory();
                    } else {
                        showMessage('Error', data.error || 'Update failed.', 'error');
                    }
                })
                .catch(function(err) {
                    showMessage('Error', 'Request failed.', 'error');
                });
        });

        function cancelRequest(e) {
            var btn = e.target;
            var id = btn.getAttribute('data-id');
            var product = btn.getAttribute('data-product');
            if (!confirm('Cancel this order line?\n\nProduct: ' + product + '\n\nThis will remove it from your requests.')) return;
            var formData = new FormData();
            formData.append('action', 'delete_request');
            formData.append('request_id', id);
            formData.append('pharmacist_id', pharmacistId);
            fetch('/dheergayu/public/api/submit-request.php', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        showMessage('Success', data.message || 'Order line cancelled.', 'success');
                        loadRequestHistory();
                    } else {
                        showMessage('Error', data.error || 'Cancel failed.', 'error');
                    }
                })
                .catch(function(err) {
                    showMessage('Error', 'Request failed.', 'error');
                });
        }

        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!pharmacistId) {
                showMessage('Error', 'Please log in to submit requests.', 'error');
                return;
            }
            const supplierId = document.getElementById('supplier').value;
            if (!supplierId) {
                showMessage('Error', 'Please select a supplier.', 'error');
                return;
            }
            const items = [];
            document.querySelectorAll('#productsTableBody .quantity-input').forEach(function(input) {
                const qty = parseInt(input.value, 10) || 0;
                if (qty > 0) {
                    items.push({
                        product_name: input.getAttribute('data-product-name'),
                        quantity: qty
                    });
                }
            });
            if (items.length === 0) {
                showMessage('Error', 'Enter at least one quantity to order.', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('pharmacist_id', pharmacistId);
            formData.append('supplier_id', supplierId);
            formData.append('request_date', document.getElementById('request_date').value);
            formData.append('action', 'create_order');
            formData.append('items', JSON.stringify(items));

            fetch('/dheergayu/public/api/submit-request.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showMessage('Success', data.message || 'Request submitted successfully!', 'success');
                    this.reset();
                    document.getElementById('request_date').valueAsDate = new Date();
                    renderProductsTable();
                    loadRequestHistory();
                } else {
                    showMessage('Error', data.error || 'Failed to submit request.', 'error');
                }
            }.bind(this))
            .catch(function(error) {
                console.error('Error:', error);
                showMessage('Error', 'An error occurred while submitting the request.', 'error');
            });
        });

        document.getElementById('requestForm').addEventListener('reset', function() {
            setTimeout(function() {
                document.getElementById('request_date').valueAsDate = new Date();
                renderProductsTable();
            }, 0);
        });

        function showMessage(title, message, type) {
            var modal = document.getElementById('messageModal');
            var content = document.getElementById('messageContent');
            content.innerHTML = '<h3 style="color: ' + (type === 'success' ? '#28a745' : '#dc3545') + ';">' + title + '</h3><p>' + message + '</p>';
            modal.style.display = 'block';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        function escapeHtmlAttr(text) {
            var s = String(text);
            return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        loadRequestHistory();
    </script>
</body>
</html>
