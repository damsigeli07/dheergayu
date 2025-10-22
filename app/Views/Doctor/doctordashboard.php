<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctordashboard.css">
</head>
<body>
<?php
require_once __DIR__ . '/../../Models/AppointmentModel.php';
$db = new mysqli('localhost', 'root', '', 'dheergayu_db'); // Update credentials if needed
$appointmentModel = new AppointmentModel($db);
$appointments = $appointmentModel->getAllDoctorAppointments();

// Compute counts
$totalAppointments = 0;
$upcomingAppointments = 0;
$completedAppointments = 0;
$cancelledAppointments = 0;
if (!empty($appointments)) {
	foreach ($appointments as $apt) {
		$statusRaw = isset($apt['status']) ? $apt['status'] : '';
		$statusNorm = ($statusRaw === 'Pending' || $statusRaw === 'Confirmed') ? 'Upcoming' : $statusRaw;
		$totalAppointments++;
		if ($statusNorm === 'Upcoming') { $upcomingAppointments++; }
		if ($statusNorm === 'Completed') { $completedAppointments++; }
		if ($statusNorm === 'Cancelled') { $cancelledAppointments++; }
	}
}
?>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <button class="nav-btn active">Appointments</button>
                <a href="patienthistory.php" class="nav-btn">Patient History</a>
                <a href="doctorreport.php" class="nav-btn">Reports</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>

            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Doctor</span>
            <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="doctorprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>

        </div>
    </header>

    <main class="main-content">
        <div class="search-container">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search" class="search-input" id="search-input">
            </div>
        </div>

        <!-- Statistics Boxes -->
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?= htmlspecialchars((string)($upcomingAppointments + $completedAppointments + $cancelledAppointments)) ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= htmlspecialchars((string)$upcomingAppointments) ?></div>
                <div class="stat-label">Upcoming Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= htmlspecialchars((string)$completedAppointments) ?></div>
                <div class="stat-label">Completed Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= htmlspecialchars((string)$cancelledAppointments) ?></div>
                <div class="stat-label">Cancelled Appointments</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <button class="tab-btn active" data-tab="all">All Appointments</button>
            <button class="tab-btn" data-tab="upcoming">Upcoming Appointments</button>
            <button class="tab-btn" data-tab="completed">Completed Appointments</button>
            <button class="tab-btn" data-tab="cancelled">Cancelled Appointments</button>
        </div>

        <div class="table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient No.</th>
                        <th>Patient Name</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="appointments-tbody">
                    <?php if (!empty($appointments)) : ?>
                        <?php foreach ($appointments as $apt) : ?>
                            <?php
                                $statusUpper = strtoupper(isset($apt['status']) ? trim($apt['status']) : '');
                                if ($statusUpper === 'PENDING' || $statusUpper === 'CONFIRMED') {
                                    $status = 'Upcoming';
                                } elseif ($statusUpper === 'COMPLETED') {
                                    $status = 'Completed';
                                } elseif ($statusUpper === 'CANCELLED') {
                                    $status = 'Cancelled';
                                } else {
                                    $status = $apt['status'];
                                }
                            ?>
                            <tr class="appointment-row <?= strtolower($status) ?>" data-status="<?= strtolower($status) ?>">
                                <td><?= htmlspecialchars($apt['appointment_id']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_no']) ?></td>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['appointment_datetime']) ?></td>
                                <td>
                                    <?php if ($status === 'Upcoming') : ?>
                                        <span class="status-badge upcoming">Upcoming</span>
                                    <?php elseif ($status === 'Completed') : ?>
                                        <span class="status-badge completed">Completed</span>
                                    <?php elseif ($status === 'Cancelled') : ?>
                                        <span class="status-badge cancelled">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if ($status === 'Upcoming') : ?>
                                        <button class="btn-start" onclick="window.open('doctorconsultform.php?appointment_id=<?= htmlspecialchars($apt['appointment_id']) ?>', '_blank')">Start Consultation</button>
                                        <button class="btn-cancel" onclick="showCancelReason(this, '<?= htmlspecialchars($apt['appointment_id']) ?>')">Cancel</button>
                                    <?php elseif ($status === 'Completed') : ?>
                                        <button class="btn-view" onclick="showConsultationModal(<?= htmlspecialchars($apt['appointment_id']) ?>)">View</button>
                                    <?php elseif ($status === 'Cancelled') : ?>
                                        <button class="btn-view" onclick="showCancelDetails('<?= htmlspecialchars($apt['reason']) ?>')">View</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No appointments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span id="pagination-info">Showing 1-10 of <?= count($appointments) ?> appointments</span>
                </div>
                <div class="pagination-controls">
                    <button id="prev-page" class="pagination-btn" disabled>Previous</button>
                    <span id="page-numbers"></span>
                    <button id="next-page" class="pagination-btn">Next</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Consultation Form Data -->
    <div id="consultationModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div id="consultationModalContent" style="background:linear-gradient(135deg,#f8fafc 0%,#e3e6f3 100%);padding:0;border-radius:16px;box-shadow:0 4px 24px #333;max-width:520px;width:90%;margin:auto;position:relative;">
            <div style="padding:24px 32px 16px 32px;border-radius:16px 16px 0 0;background:#5d9b57;color:#fff;display:flex;align-items:center;justify-content:space-between;">
                <h2 style="margin:0;font-size:22px;font-weight:600;letter-spacing:1px;">Consultation Form Details</h2>
                <div>
                    <button onclick="toggleEditConsultationModal()" id="editConsultationBtn" style="background:#fff;color:#5d9b57;border:none;border-radius:8px;padding:6px 16px;font-size:15px;font-weight:500;cursor:pointer;box-shadow:0 2px 8px #5d9b5733;margin-right:10px;">Edit</button>
                    <button onclick="closeConsultationModal()" style="background:#fff;color:#5d9b57;border:none;border-radius:50%;width:32px;height:32px;font-size:20px;cursor:pointer;box-shadow:0 2px 8px #5d9b5733;display:inline-flex;align-items:center;justify-content:center;">&times;</button>
                </div>
            </div>
            <div id="consultationFormData" style="padding:24px 32px 32px 32px;max-height:70vh;overflow-y:auto;"></div>
        </div>
    </div>
    <script>
    var currentAppointmentId = null;
    var currentConsultationData = null;
    var isEditMode = false;
    function showConsultationModal(appointmentId) {
        currentAppointmentId = appointmentId;
        var modal = document.getElementById('consultationModal');
        var content = document.getElementById('consultationFormData');
        content.innerHTML = '<div style="text-align:center;padding:40px 0;font-size:18px;color:#43a047;">Loading...</div>';
        modal.style.display = 'flex';
        isEditMode = false;
        // AJAX request to fetch consultation form data
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/dheergayu/app/Controllers/ConsultationFormController.php?action=get_consultation_form&appointment_id=' + appointmentId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                currentConsultationData = data;
                renderConsultationModal(data);
            }
        };
        xhr.send();
    }

    function renderConsultationModal(data) {
        var content = document.getElementById('consultationFormData');
        var allowed = {
            first_name: 'First Name',
            lastname: 'Last Name',
            last_name: 'Last Name',
            age: 'Age',
            gender: 'Gender',
            diagnosis: 'Diagnosis',
            personal_products: 'Prescribed Products',
            prescribed_products: 'Prescribed Products',
            prescribed_product: 'Prescribed Products',
            recommended_treatment: 'Recommended Treatment',
            recommended_treatments: 'Recommended Treatments',
            recomended_treatments: 'Recommended Treatments',
            notes: 'Notes'
        };
        function isAllowed(key){
            var k = String(key).toLowerCase();
            return allowed.hasOwnProperty(k);
        }
        function labelFor(key){
            var k = String(key).toLowerCase();
            return allowed[k] || key;
        }
        if (!isEditMode) {
            if (data && Object.keys(data).length > 0) {
                var html = '<table style="width:100%;border-collapse:separate;border-spacing:0 8px;">';
                for (var key in data) {
                    if (data.hasOwnProperty(key) && key !== 'id' && key !== 'appointment_id' && isAllowed(key)) {
                        html += '<tr style="background:#fff;box-shadow:0 2px 8px #e3e6f3;border-radius:8px;">';
                        html += '<td style="font-weight:500;padding:10px 16px;color:#5d9b57;width:40%;border-radius:8px 0 0 8px;">' + labelFor(key) + '</td>';
                        var value = (data[key] !== null ? data[key] : '');
                        if (String(key).toLowerCase() === 'personal_products') {
                            try {
                                var items = JSON.parse(value);
                                if (Array.isArray(items)) {
                                    value = items.map(function(p){ return (p.product || '') + (p.qty ? ' x' + p.qty : ''); }).join(', ');
                                }
                            } catch(e) { /* leave as-is if not JSON */ }
                        }
                        html += '<td style="padding:10px 16px;color:#222;border-radius:0 8px 8px 0;">' + value + '</td>';
                        html += '</tr>';
                    }
                }
                html += '</table>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<div style="text-align:center;padding:40px 0;font-size:18px;color:#e74c3c;">No consultation form data found.</div>';
            }
        } else {
            // Edit mode: show form fields with dynamic product loading
            if (data && Object.keys(data).length > 0) {
                var html = '<form id="editConsultationForm" style="width:100%;">';
                for (var key in data) {
                    if (data.hasOwnProperty(key) && key !== 'id' && key !== 'appointment_id' && isAllowed(key)) {
                        html += '<div style="margin-bottom:16px;">';
                        html += '<label style="font-weight:500;color:#5d9b57;display:block;margin-bottom:6px;">' + labelFor(key) + '</label>';
                        
                        if (key === 'personal_products') {
                            // Special handling for personal_products with dynamic product loading
                            html += '<div id="products-container">';
                            html += '<div style="margin-bottom:10px;">';
                            html += '<input type="text" id="product_search" placeholder="Search products..." style="width:70%;padding:8px 12px;border:1px solid #5d9b57;border-radius:6px;font-size:15px;" list="product_list" />';
                            html += '<input type="number" id="product_qty" placeholder="Qty" min="1" style="width:20%;padding:8px 12px;border:1px solid #5d9b57;border-radius:6px;font-size:15px;margin-left:5px;" />';
                            html += '<button type="button" id="add_product" style="background:#5d9b57;color:#fff;padding:8px 12px;border:none;border-radius:6px;font-size:14px;margin-left:5px;">Add</button>';
                            html += '</div>';
                            html += '<datalist id="product_list"></datalist>';
                            html += '<div id="selected_products"></div>';
                            html += '<input type="hidden" name="personal_products" id="personal_products_input" value="' + (data[key] !== null ? data[key] : '[]') + '" />';
                            html += '</div>';
                        } else {
                            html += '<input type="text" name="' + key + '" value="' + (data[key] !== null ? data[key] : '') + '" style="width:100%;padding:8px 12px;border:1px solid #5d9b57;border-radius:6px;font-size:15px;" />';
                        }
                        html += '</div>';
                    }
                }
                html += '<button type="button" onclick="saveConsultationEdit()" style="background:#5d9b57;color:#fff;padding:10px 28px;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;box-shadow:0 2px 8px #5d9b5733;margin-top:10px;">Save</button>';
                html += '<button type="button" onclick="toggleEditConsultationModal()" style="background:#fff;color:#5d9b57;padding:10px 28px;border:1px solid #5d9b57;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;box-shadow:0 2px 8px #5d9b5733;margin-top:10px;margin-left:10px;">Cancel</button>';
                html += '</form>';
                content.innerHTML = html;
                
                // Initialize product functionality for edit mode
                initializeProductEditMode(data.personal_products);
            } else {
                content.innerHTML = '<div style="text-align:center;padding:40px 0;font-size:18px;color:#e74c3c;">No consultation form data found.</div>';
            }
        }
    }

    function toggleEditConsultationModal() {
        isEditMode = !isEditMode;
        renderConsultationModal(currentConsultationData);
    }

    function saveConsultationEdit() {
        var form = document.getElementById('editConsultationForm');
        var formData = new FormData(form);
        formData.append('appointment_id', currentAppointmentId);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dheergayu/app/Controllers/ConsultationFormController.php?action=update_consultation_form', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.status === 'success') {
                        showCustomAlert('Consultation form updated successfully!');
                        showConsultationModal(currentAppointmentId);
                        isEditMode = false;
                    } else {
                        showCustomAlert('Error updating consultation form.');
                    }
                } catch (e) {
                    showCustomAlert('Error updating consultation form.');
                }
            }
        };
        xhr.send(formData);
    }
    function closeConsultationModal() {
        document.getElementById('consultationModal').style.display = 'none';
    }

    // Product editing functionality
    var availableProducts = [];
    var selectedProducts = [];

    function initializeProductEditMode(existingProducts) {
        // Load products from database
        loadProductsFromDatabase();
        
        // Parse existing products
        try {
            selectedProducts = JSON.parse(existingProducts || '[]');
        } catch (e) {
            selectedProducts = [];
        }
        
        // Display existing products
        displaySelectedProducts();
        
        // Add event listeners
        document.getElementById('add_product').addEventListener('click', addProduct);
    }

    function loadProductsFromDatabase() {
        fetch('/dheergayu/public/api/get-products.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availableProducts = data.products;
                    populateProductList();
                } else {
                    console.error('Failed to load products:', data.error);
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
    }

    function populateProductList() {
        const datalist = document.getElementById('product_list');
        datalist.innerHTML = '';
        
        availableProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = `${product.name} (Av: ${product.available_quantity} units)`;
            option.setAttribute('data-product-id', product.id);
            option.setAttribute('data-available-qty', product.available_quantity);
            datalist.appendChild(option);
        });
    }

    function addProduct() {
        let product = document.getElementById('product_search').value.trim();
        let qty = parseInt(document.getElementById('product_qty').value.trim());

        if (product === "" || qty === "" || qty <= 0) {
            alert("Please enter product and valid quantity.");
            return;
        }

        // Extract product name (remove availability info)
        const productName = product.split(' (Av:')[0];
        
        // Check if product already exists
        const existingIndex = selectedProducts.findIndex(p => p.product === productName);
        if (existingIndex !== -1) {
            selectedProducts[existingIndex].qty += qty;
        } else {
            selectedProducts.push({
                product: productName,
                qty: qty
            });
        }

        // Clear inputs
        document.getElementById('product_search').value = '';
        document.getElementById('product_qty').value = '';
        
        // Update display and hidden input
        displaySelectedProducts();
        syncProductsField();
    }

    function removeProduct(index) {
        selectedProducts.splice(index, 1);
        displaySelectedProducts();
        syncProductsField();
    }

    function displaySelectedProducts() {
        const container = document.getElementById('selected_products');
        container.innerHTML = '';
        
        selectedProducts.forEach((product, index) => {
            const div = document.createElement('div');
            div.style.cssText = 'background:#f8f9fa;border:1px solid #5d9b57;border-radius:6px;padding:8px 12px;margin:4px 0;display:flex;justify-content:space-between;align-items:center;';
            div.innerHTML = `
                <span>${product.product} x${product.qty}</span>
                <button type="button" onclick="removeProduct(${index})" style="background:#e74c3c;color:#fff;border:none;border-radius:4px;padding:4px 8px;font-size:12px;cursor:pointer;">Remove</button>
            `;
            container.appendChild(div);
        });
    }

    function syncProductsField() {
        document.getElementById('personal_products_input').value = JSON.stringify(selectedProducts);
    }
        function showConfirm(action) {
            let msg = '';
            if (action === 'start') {
                msg = 'Are you sure you want to start this consultation?';
            } else if (action === 'view') {
                msg = 'Do you want to view this completed appointment?';
            }
            // Custom confirmation dialog
            const confirmBox = document.createElement('div');
            confirmBox.style.position = 'fixed';
            confirmBox.style.top = '0';
            confirmBox.style.left = '0';
            confirmBox.style.width = '100vw';
            confirmBox.style.height = '100vh';
            confirmBox.style.background = 'rgba(0,0,0,0.4)';
            confirmBox.style.display = 'flex';
            confirmBox.style.alignItems = 'center';
            confirmBox.style.justifyContent = 'center';
            confirmBox.style.zIndex = '9999';
            confirmBox.innerHTML = `<div style="background:#fff;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #333;text-align:center;max-width:350px;">
                <p style='font-size:16px;margin-bottom:20px;'>${msg}</p>
                <button style='background:#7a9b57;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;' onclick='this.closest("div").parentNode.removeChild(this.closest("div"));'>No</button>
                <button style='background:#3498db;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;' onclick='confirmAction("${action}", this)'>Yes</button>
            </div>`;
            document.body.appendChild(confirmBox);
        }

        function confirmAction(action, btn) {
            // Remove dialog
            btn.closest('div').parentNode.removeChild(btn.closest('div'));
            if (action === 'start') {
                window.open('doctorconsultform.php', '_blank');
            } else if (action === 'view') {
                showCustomAlert('Viewing appointment!');
            }
        }
        function showCancelReason(btn, appointmentNo) {
            const confirmBox = document.createElement('div');
            confirmBox.style.position = 'fixed';
            confirmBox.style.top = '0';
            confirmBox.style.left = '0';
            confirmBox.style.width = '100vw';
            confirmBox.style.height = '100vh';
            confirmBox.style.background = 'rgba(0,0,0,0.2)';
            confirmBox.style.display = 'flex';
            confirmBox.style.alignItems = 'center';
            confirmBox.style.justifyContent = 'center';
            confirmBox.style.zIndex = '9999';
            confirmBox.innerHTML = `<div style="background:#f7fafc;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #b2bec3;text-align:center;max-width:350px;">
                <p style='font-size:16px;margin-bottom:10px;'>Please provide a reason for cancellation:</p>
                <textarea id='cancel-reason' style='width:90%;height:60px;border-radius:6px;border:1px solid #b2bec3;margin-bottom:18px;'></textarea><br>
                <button id='cancel-no-btn' style='background:#b2dfdb;color:#333;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;'>No</button>
                <button id='cancel-next-btn' style='background:#e57373;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;'>Next</button>
            </div>`;
            document.body.appendChild(confirmBox);
            
            // Add event listeners
            document.getElementById('cancel-no-btn').addEventListener('click', function() {
                document.body.removeChild(confirmBox);
            });
            
            document.getElementById('cancel-next-btn').addEventListener('click', function() {
                showFinalCancelConfirm(appointmentNo, confirmBox);
            });
        }

        function showFinalCancelConfirm(appointmentNo, previousDialog) {
            const reason = document.getElementById('cancel-reason').value.trim();
            if (!reason) {
                showCustomAlert('Please enter a reason.');
                return;
            }
            
            // Remove previous dialog
            document.body.removeChild(previousDialog);
            
            // Show custom confirmation dialog
            const confirmBox = document.createElement('div');
            confirmBox.style.position = 'fixed';
            confirmBox.style.top = '0';
            confirmBox.style.left = '0';
            confirmBox.style.width = '100vw';
            confirmBox.style.height = '100vh';
            confirmBox.style.background = 'rgba(0,0,0,0.2)';
            confirmBox.style.display = 'flex';
            confirmBox.style.alignItems = 'center';
            confirmBox.style.justifyContent = 'center';
            confirmBox.style.zIndex = '9999';
            confirmBox.innerHTML = `<div style="background:#f7fafc;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #b2bec3;text-align:center;max-width:350px;">
                <p style='font-size:16px;margin-bottom:20px;'>Are you sure you want to cancel this appointment?</p>
                <button id='final-cancel-no-btn' style='background:#b2dfdb;color:#333;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;'>No</button>
                <button id='final-cancel-yes-btn' style='background:#e57373;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;'>Yes</button>
            </div>`;
            document.body.appendChild(confirmBox);
            
            // Add event listeners
            document.getElementById('final-cancel-no-btn').addEventListener('click', function() {
                document.body.removeChild(confirmBox);
            });
            
            document.getElementById('final-cancel-yes-btn').addEventListener('click', function() {
                submitCancelReason(appointmentNo, reason, confirmBox);
            });
        }

        function submitCancelReason(appointmentNo, reason, dialogElement) {
            // Remove dialog
            document.body.removeChild(dialogElement);
            
            // AJAX request to update cancellation in DB
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/dheergayu/app/Controllers/AppointmentController.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    showCustomAlert('Appointment cancelled!');
                    setTimeout(function(){ location.reload(); }, 1200);
                }
            };
            xhr.send('action=cancel&appointment_id=' + encodeURIComponent(appointmentNo) + '&reason=' + encodeURIComponent(reason));
        }

        function showCustomAlert(msg) {
            const alertBox = document.createElement('div');
            alertBox.style.position = 'fixed';
            alertBox.style.top = '0';
            alertBox.style.left = '0';
            alertBox.style.width = '100vw';
            alertBox.style.height = '100vh';
            alertBox.style.background = 'rgba(0,0,0,0.2)';
            alertBox.style.display = 'flex';
            alertBox.style.alignItems = 'center';
            alertBox.style.justifyContent = 'center';
            alertBox.style.zIndex = '9999';
            alertBox.innerHTML = `<div style="background:#f7fafc;padding:20px 30px;border-radius:10px;box-shadow:0 2px 10px #b2bec3;text-align:center;max-width:300px;">
                <p style='font-size:15px;margin-bottom:10px;'>${msg}</p>
                <button id='custom-alert-ok' style='background:#b2dfdb;color:#333;padding:6px 14px;border:none;border-radius:6px;font-size:13px;cursor:pointer;'>OK</button>
            </div>`;
            document.body.appendChild(alertBox);
            document.getElementById('custom-alert-ok').addEventListener('click', function() {
                const container = this.closest('div').parentNode; 
                // remove overlay without reloading
                container.parentNode.removeChild(container);
            });
        }

        function showCancelDetails(reason) {
            showCustomAlert('Cancellation Reason:<br>' + reason);
        }
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const appointmentRows = document.querySelectorAll('.appointment-row');
            const searchInput = document.getElementById('search-input');
            
            // Pagination variables
            let currentPage = 1;
            const rowsPerPage = 10;
            let filteredRows = Array.from(appointmentRows);
            let currentFilter = 'all';
            
            // Initialize pagination
            function initializePagination() {
                updatePagination();
                showPage(1);
            }
            
            // Update pagination controls
            function updatePagination() {
                const totalRows = filteredRows.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage);
                const startRow = (currentPage - 1) * rowsPerPage + 1;
                const endRow = Math.min(currentPage * rowsPerPage, totalRows);
                
                // Update pagination info
                document.getElementById('pagination-info').textContent = 
                    `Showing ${startRow}-${endRow} of ${totalRows} appointments`;
                
                // Update page numbers
                const pageNumbers = document.getElementById('page-numbers');
                pageNumbers.innerHTML = '';
                
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.className = 'pagination-btn page-number';
                    if (i === currentPage) {
                        pageBtn.classList.add('active');
                    }
                    pageBtn.addEventListener('click', () => showPage(i));
                    pageNumbers.appendChild(pageBtn);
                }
                
                // Update prev/next buttons
                document.getElementById('prev-page').disabled = currentPage === 1;
                document.getElementById('next-page').disabled = currentPage === totalPages;
            }
            
            // Show specific page
            function showPage(page) {
                currentPage = page;
                const startIndex = (page - 1) * rowsPerPage;
                const endIndex = startIndex + rowsPerPage;
                
                // Hide all rows first
                appointmentRows.forEach(row => row.style.display = 'none');
                
                // Show rows for current page
                filteredRows.slice(startIndex, endIndex).forEach(row => {
                    row.style.display = '';
                });
                
                updatePagination();
            }
            
            // Filter rows based on tab and search
            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-tab');
                
                filteredRows = Array.from(appointmentRows).filter(row => {
                    const patientName = row.cells[2].textContent.toLowerCase();
                    const patientNo = row.cells[1].textContent.toLowerCase();
                    const status = row.getAttribute('data-status');
                    
                    const matchesTab = activeTab === 'all' || status === activeTab;
                    const matchesSearch = patientName.includes(searchTerm) || patientNo.includes(searchTerm);
                    
                    return matchesTab && matchesSearch;
                });
                
                currentPage = 1;
                showPage(1);
            }
            
            // Tab functionality
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    currentFilter = this.getAttribute('data-tab');
                    filterRows();
                });
            });
            
            // Search functionality
            searchInput.addEventListener('input', filterRows);
            
            // Pagination controls
            document.getElementById('prev-page').addEventListener('click', () => {
                if (currentPage > 1) {
                    showPage(currentPage - 1);
                }
            });
            
            document.getElementById('next-page').addEventListener('click', () => {
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage < totalPages) {
                    showPage(currentPage + 1);
                }
            });
            
            // Initialize pagination on page load
            initializePagination();
            
            // Delete functionality
            // Custom confirmation dialog for actions
            window.showConfirm = function(action) {
                let msg = '';
                if (action === 'start') {
                    msg = 'Are you sure you want to start this consultation?';
                } else if (action === 'cancel') {
                    msg = 'Are you sure you want to cancel this appointment?';
                } else if (action === 'view') {
                    msg = 'Do you want to view this completed appointment?';
                }
                // Custom confirmation dialog
                const confirmBox = document.createElement('div');
                confirmBox.style.position = 'fixed';
                confirmBox.style.top = '0';
                confirmBox.style.left = '0';
                confirmBox.style.width = '100vw';
                confirmBox.style.height = '100vh';
                confirmBox.style.background = 'rgba(0,0,0,0.4)';
                confirmBox.style.display = 'flex';
                confirmBox.style.alignItems = 'center';
                confirmBox.style.justifyContent = 'center';
                confirmBox.style.zIndex = '9999';
                confirmBox.innerHTML = `<div style="background:#fff;padding:30px 40px;border-radius:10px;box-shadow:0 2px 10px #333;text-align:center;max-width:350px;">
                    <p style='font-size:16px;margin-bottom:20px;'>${msg}</p>
                    <button style='background:#7a9b57;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;margin-right:10px;cursor:pointer;' onclick='this.closest("div").parentNode.removeChild(this.closest("div"));'>No</button>
                    <button style='background:#3498db;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;' onclick='confirmAction("${action}", this)'>Yes</button>
                </div>`;
                document.body.appendChild(confirmBox);
            }

            window.confirmAction = function(action, btn) {
                // Remove dialog
                btn.closest('div').parentNode.removeChild(btn.closest('div'));
                // Here you can add AJAX or redirect logic for each action
                if (action === 'start') {
                    alert('Consultation started!');
                } else if (action === 'cancel') {
                    alert('Appointment cancelled!');
                } else if (action === 'view') {
                    alert('Viewing appointment!');
                }
            }

        });
    </script>
</body>
</html>