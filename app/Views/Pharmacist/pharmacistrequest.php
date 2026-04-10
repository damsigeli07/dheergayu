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

$batchModel = new BatchModel();

// Products by type
$adminProducts = [];
$adminRes = $conn->query("SELECT product_id AS id, name FROM products WHERE product_type = 'admin' ORDER BY name");
if ($adminRes) while ($row = $adminRes->fetch_assoc()) $adminProducts[] = ['id' => $row['id'], 'name' => $row['name']];

$patientProducts = [];
$patientRes = $conn->query("SELECT product_id AS id, name FROM products WHERE product_type = 'patient' ORDER BY name");
if ($patientRes) while ($row = $patientRes->fetch_assoc()) $patientProducts[] = ['id' => $row['id'], 'name' => $row['name']];

$treatmentProducts = [];
$treatmentRes = $conn->query("SELECT product_id AS id, name FROM products WHERE product_type = 'treatment' ORDER BY name");
if ($treatmentRes) while ($row = $treatmentRes->fetch_assoc()) $treatmentProducts[] = ['id' => $row['id'], 'name' => $row['name']];

// Map supplier -> products by name keywords
$supplierProductsMap = [];
foreach ($suppliers as $s) {
    $name = strtolower($s['supplier_name'] ?? '');
    if (str_contains($name, 'oil')) {
        $supplierProductsMap[$s['id']] = $treatmentProducts;   // Herbal Oils & Supplies
    } elseif (str_contains($name, 'medicine')) {
        $supplierProductsMap[$s['id']] = $adminProducts;        // Ayurvedic Medicines Co
    } elseif (str_contains($name, 'trader')) {
        $supplierProductsMap[$s['id']] = $patientProducts;      // Ayurvedic Traders
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
            <a href="pharmacisttreatmentprep.php" class="nav-btn">Treatment Prep</a>
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

    <!-- Add to Inventory modal -->
    <div id="addInventoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddInventoryModal()">&times;</span>
            <h3>Add Delivered Stock to Inventory</h3>
            <form id="addInventoryForm">
                <input type="hidden" id="inv_request_id">
                <input type="hidden" id="inv_product_id">
                <input type="hidden" id="inv_product_source">
                <div class="form-group">
                    <label>Product</label>
                    <input type="text" id="inv_product_name" class="form-input" readonly style="background:#f8f9fa;">
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" id="inv_quantity" class="form-input" min="1" required>
                </div>
                <div class="form-group">
                    <label for="inv_batch_number">Batch Number *</label>
                    <input type="text" id="inv_batch_number" class="form-input" required placeholder="e.g. ASM001">
                </div>
                <div class="form-group">
                    <label for="inv_mfd">Manufacturing Date *</label>
                    <input type="date" id="inv_mfd" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="inv_exp">Expiry Date *</label>
                    <input type="date" id="inv_exp" class="form-input" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add to Inventory</button>
                    <button type="button" class="btn-reset" onclick="closeAddInventoryModal()">Cancel</button>
                </div>
            </form>
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
                            var isDelivered = (status === 'delivered');
                            var safeProduct = escapeHtmlAttr(request.product_name || '');
                            var actions = isPending
                                ? '<button type="button" class="btn-edit-order" data-id="' + request.id + '" data-product="' + safeProduct + '" data-qty="' + request.quantity + '">Edit</button> '
                                + '<button type="button" class="btn-cancel-order" data-id="' + request.id + '" data-product="' + safeProduct + '">Cancel</button>'
                                : isDelivered
                                ? '<button type="button" class="btn-add-inventory" data-id="' + request.id + '" data-product="' + safeProduct + '" data-qty="' + request.quantity + '" data-supplier="' + request.supplier_id + '" style="background:#28a745;color:#fff;border:none;padding:0.3rem 0.8rem;border-radius:4px;cursor:pointer;font-weight:600;">Add to Inventory</button>'
                                : '<span class="no-actions">—</span>';
                            return '<tr><td>' + escapeHtml(request.product_name) + '</td><td>' + request.quantity + '</td><td>' + escapeHtml(request.supplier_name || 'N/A') + '</td><td>' + request.request_date + '</td><td><span class="status-badge status-' + status + '">' + status + '</span></td><td class="actions-cell">' + actions + '</td></tr>';
                        }).join('');
                        tbody.querySelectorAll('.btn-edit-order').forEach(function(btn) {
                            btn.addEventListener('click', openEditRequestModal);
                        });
                        tbody.querySelectorAll('.btn-cancel-order').forEach(function(btn) {
                            btn.addEventListener('click', cancelRequest);
                        });
                        tbody.querySelectorAll('.btn-add-inventory').forEach(function(btn) {
                            btn.addEventListener('click', openAddInventoryModal);
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

        window.onclick = function(e) {
            ['messageModal','editRequestModal','addInventoryModal'].forEach(function(id) {
                var m = document.getElementById(id);
                if (e.target === m) m.style.display = 'none';
            });
        };

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        function escapeHtmlAttr(text) {
            var s = String(text);
            return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function getPrefixForProduct(name) {
            var n = name.toLowerCase();
            if (n.includes('asamodagam')) return 'ASM';
            if (n.includes('paspanguwa') || n.includes('pasapanguwa') || n.includes('pasanguwa')) return 'PSP';
            if (n.includes('siddhalepa') || n.includes('sidhalepa') || n.includes('siddalepa')) return 'SDP';
            if (n.includes('dashamoolarishta')) return 'DMR';
            if (n.includes('kothalahimbutu')) return 'KHC';
            if (n.includes('neem') && n.includes('oil')) return 'NEO';
            if (n.includes('nirgundi') && n.includes('oil')) return 'NRO';
            if (n.includes('pinda') && n.includes('thailaya')) return 'PTL';
            if (n.includes('bala') && n.includes('thailaya')) return 'BLT';
            if (n.includes('ashwagandha')) return 'ASH';
            if (n.includes('arawindasawaya')) return 'ARS';
            if (n.includes('chandanasawaya')) return 'CDS';
            if (n.includes('kanakasawaya')) return 'KNS';
            if (n.includes('abayarishtaya')) return 'ABY';
            if (n.includes('amurtharishtaya')) return 'AMR';
            if (n.includes('arjunarishtaya')) return 'ARJ';
            if (n.includes('samahan')) return 'SMH';
            return (name.replace(/[^A-Za-z]/g, '').toUpperCase().slice(0, 3) || 'BAT');
        }

        async function suggestBatchNumberForInventory(productId, productSource, productName) {
            var prefix = getPrefixForProduct(productName);
            var maxNum = 0;
            try {
                var res = await fetch('/dheergayu/public/api/batches/by-product?product_id=' + productId + '&product_source=' + productSource);
                var data = await res.json();
                var rows = data.data || [];
                rows.forEach(function(b) {
                    var m = String(b.batch_number || '').match(/^(\D+)(\d+)$/);
                    if (m && m[1].toUpperCase() === prefix) {
                        var num = parseInt(m[2], 10) || 0;
                        if (num > maxNum) maxNum = num;
                    }
                });
            } catch (e) { console.error(e); }
            var next = String(maxNum + 1).padStart(3, '0');
            document.getElementById('inv_batch_number').value = prefix + next;
        }

        function openAddInventoryModal(e) {
            var btn = e.target;
            var requestId = btn.getAttribute('data-id');
            var productName = btn.getAttribute('data-product');
            var qty = btn.getAttribute('data-qty');
            var supplierId = btn.getAttribute('data-supplier');

            // Lookup product_id and product_source from supplierProductsMap
            var products = supplierProductsMap[supplierId] || [];
            var product = products.find(function(p) { return p.name === productName; });

            if (!product) {
                showMessage('Error', 'Could not find product details. Please add batch manually from Inventory.', 'error');
                return;
            }

            // Determine product_source: Herbal supplier → patient, Ayurvedic → admin
            var isPatient = false;
            for (var sid in supplierProductsMap) {
                var prods = supplierProductsMap[sid];
                if (String(sid) === String(supplierId) && prods.length > 0) {
                    // Check if any of these match patient products by checking the map origin
                    // We determine from the supplier name passed via data or by checking known IDs
                    break;
                }
            }
            // Determine product_source from supplier name
            var supplierCell = btn.closest('tr') ? btn.closest('tr').cells[2] : null;
            var supplierName = supplierCell ? supplierCell.textContent.trim().toLowerCase() : '';
            var productSource = supplierName.includes('oil') ? 'treatment'
                              : supplierName.includes('trader') ? 'patient'
                              : 'admin';

            document.getElementById('inv_request_id').value = requestId;
            document.getElementById('inv_product_id').value = product.id;
            document.getElementById('inv_product_source').value = productSource;
            document.getElementById('inv_product_name').value = productName;
            document.getElementById('inv_quantity').value = qty;

            var today = new Date().toISOString().split('T')[0];
            document.getElementById('inv_mfd').max = today;
            document.getElementById('inv_exp').min = today;
            document.getElementById('inv_mfd').value = '';
            document.getElementById('inv_exp').value = '';
            document.getElementById('inv_batch_number').value = 'Loading...';

            document.getElementById('addInventoryModal').style.display = 'block';

            suggestBatchNumberForInventory(product.id, productSource, productName);
        }

        function closeAddInventoryModal() {
            document.getElementById('addInventoryModal').style.display = 'none';
        }

        document.getElementById('addInventoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var requestId = document.getElementById('inv_request_id').value;
            var productId = document.getElementById('inv_product_id').value;
            var productSource = document.getElementById('inv_product_source').value;
            var quantity = document.getElementById('inv_quantity').value;
            var batchNumber = document.getElementById('inv_batch_number').value.trim();
            var mfd = document.getElementById('inv_mfd').value;
            var exp = document.getElementById('inv_exp').value;

            if (!batchNumber || !mfd || !exp) {
                showMessage('Error', 'All fields are required.', 'error');
                return;
            }

            // Determine status from expiry
            var today = new Date(); today.setHours(0,0,0,0);
            var expDate = new Date(exp);
            var thirtyDays = new Date(); thirtyDays.setDate(thirtyDays.getDate() + 30);
            var status = expDate < today ? 'Expired' : expDate <= thirtyDays ? 'Expiring Soon' : 'Good';

            var payload = new FormData();
            payload.append('product_id', productId);
            payload.append('product_source', productSource);
            payload.append('batch_number', batchNumber);
            payload.append('quantity', quantity);
            payload.append('mfd', mfd);
            payload.append('exp', exp);
            payload.append('status', status);

            try {
                var res = await fetch('/dheergayu/public/api/batches/create', { method: 'POST', body: payload });
                var data = await res.json();
                if (!data.success) {
                    showMessage('Error', data.error || 'Failed to add batch.', 'error');
                    return;
                }

                // Mark request as stocked
                var fd2 = new FormData();
                fd2.append('action', 'mark_stocked');
                fd2.append('request_id', requestId);
                await fetch('/dheergayu/public/api/submit-request.php', { method: 'POST', body: fd2 });

                showMessage('Success', 'Stock added to inventory successfully!', 'success');
                closeAddInventoryModal();
                loadRequestHistory();
            } catch (err) {
                showMessage('Error', 'Error: ' + err.message, 'error');
            }
        });

        loadRequestHistory();
    </script>
</body>
</html>
