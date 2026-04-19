<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/auth_admin.php';
// Fetch treatments from database
$db = $conn;

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch all staff users
$staffUsers = [];
$staffResult = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE role = 'staff' ORDER BY first_name");
if ($staffResult) {
    while ($row = $staffResult->fetch_assoc()) $staffUsers[] = $row;
}

// Fetch staff assignments per treatment
$staffAssignments = [];
$saResult = $db->query("SELECT ts.treatment_id, tl.treatment_name,
    ts.primary_staff1_id, CONCAT(u1.first_name,' ',u1.last_name) AS staff1_name,
    ts.primary_staff2_id, CONCAT(u2.first_name,' ',u2.last_name) AS staff2_name
    FROM treatment_staff ts
    JOIN treatment_list tl ON tl.treatment_id = ts.treatment_id
    LEFT JOIN users u1 ON u1.id = ts.primary_staff1_id
    LEFT JOIN users u2 ON u2.id = ts.primary_staff2_id");
if ($saResult) {
    while ($row = $saResult->fetch_assoc()) $staffAssignments[$row['treatment_id']] = $row;
}

// Fetch all treatments from treatment_list table
$query = "SELECT treatment_id, treatment_name, description, duration, price, image, status
          FROM treatment_list
          ORDER BY treatment_id";
$result = $db->query($query);

$treatments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $treatments[] = [
            'id' => $row['treatment_id'],
            'name' => $row['treatment_name'],
            'description' => $row['description'] ?? '',
            'duration' => $row['duration'] ?? '',
            'price' => number_format($row['price'], 2, '.', ','),
            'status' => $row['status'],
            'image' => $row['image'] ?? '/dheergayu/public/assets/images/Admin/health-treatments.jpg'
        ];
    }
}

// Fetch oils for all treatments
$treatmentOilsMap = [];
$oilsQuery = "SELECT tp.treatment_id, p.name FROM treatment_products tp JOIN products p ON p.product_id = tp.product_id";
$oilsResult = $db->query($oilsQuery);
if ($oilsResult) {
    while ($row = $oilsResult->fetch_assoc()) {
        $treatmentOilsMap[$row['treatment_id']][] = $row['name'];
    }
}

// Calculate statistics
$totalTreatments = count($treatments);
$activeTreatments = count(array_filter($treatments, function($t) { return $t['status'] === 'Active'; }));
$inactiveTreatments = count(array_filter($treatments, function($t) { return $t['status'] === 'Inactive'; }));

// Get unique durations for filter
$uniqueDurations = [];
foreach ($treatments as $treatment) {
    if (!empty($treatment['duration']) && !in_array($treatment['duration'], $uniqueDurations)) {
        $uniqueDurations[] = $treatment['duration'];
    }
}
sort($uniqueDurations);

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Management - Admin Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/admintreatment.css">
</head>
<body class="has-sidebar">
    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
        </div>
        
        <nav class="navigation">
            <a href="admindashboard.php" class="nav-btn">Home</a>
            <a href="admininventory.php" class="nav-btn">Products</a>
            <a href="admininventoryview.php" class="nav-btn">Inventory</a>
            <a href="adminappointment.php" class="nav-btn">Appointments</a>
            <a href="adminusers.php" class="nav-btn">Users</a>
            <a href="adminpatients.php" class="nav-btn">Patients</a>
            <button class="nav-btn active">Treatments</button>
            <a href="adminpayments.php" class="nav-btn">Payments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            <a href="admincontact.php" class="nav-btn">Contact Submissions</a>
                <a href="adminreports.php" class="nav-btn">Reports</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Treatment Overview Cards -->
        <div class="treatment-overview">
            <div class="overview-card total">
                <div class="overview-icon">🏥</div>
                <div class="overview-content">
                    <h3>Total Treatments</h3>
                    <p class="overview-number"><?= $totalTreatments ?></p>
                    <p class="overview-desc">Available services</p>
                </div>
            </div>
            
            <div class="overview-card active">
                <div class="overview-icon">✅</div>
                <div class="overview-content">
                    <h3>Active Treatments</h3>
                    <p class="overview-number"><?= $activeTreatments ?></p>
                    <p class="overview-desc">Currently offered</p>
                </div>
            </div>
            
            <div class="overview-card inactive">
                <div class="overview-icon">⏸️</div>
                <div class="overview-content">
                    <h3>Inactive Treatments</h3>
                    <p class="overview-number"><?= $inactiveTreatments ?></p>
                    <p class="overview-desc">Temporarily suspended</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <input type="text" placeholder="Search treatments..." class="search-input" id="searchInput">
                </div>
            </div>
            
            <div class="filter-container">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                
                <select class="filter-select" id="durationFilter">
                    <option value="">All Durations</option>
                    <?php foreach ($uniqueDurations as $duration): ?>
                        <option value="<?= htmlspecialchars($duration) ?>"><?= htmlspecialchars($duration) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div style="display:flex; gap:0; margin-bottom:1.5rem; border-bottom:2px solid #e0d5c8;">
            <button class="admin-tab-btn active" onclick="switchAdminTab('treatments', this)" style="padding:0.6rem 1.4rem; border:none; border-radius:8px 8px 0 0; background:#f0ece6; color:#555; font-size:0.95rem; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; position:relative; bottom:-2px; transition:all 0.2s;">Treatments</button>
            <button class="admin-tab-btn" onclick="switchAdminTab('staff-assign', this)" style="padding:0.6rem 1.4rem; border:none; border-radius:8px 8px 0 0; background:#f0ece6; color:#555; font-size:0.95rem; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; position:relative; bottom:-2px; transition:all 0.2s; margin-left:4px;">Staff Assignments</button>
        </div>

        <div id="tab-treatments">
        <!-- Add New Treatment Button -->
        <div class="add-treatment-section">
            <a href="addnewtreatment.php" class="btn btn-add">+ Add New Treatment</a>
        </div>

        <!-- Treatments Table -->
        <div class="treatments-table-container">
            <table class="treatments-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Treatment Name</th>
                        <th>Description</th>
                        <th>Oils Used</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($treatments)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #666;">
                                No treatments found. <a href="addnewtreatment.php" style="color: #E6A85A;">Add a new treatment</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($treatments as $treatment): ?>
                            <tr class="treatment-row <?= strtolower($treatment['status']) ?>">
                                <td class="treatment-image">
                                    <img src="<?= htmlspecialchars($treatment['image']) ?>" 
                                         alt="<?= htmlspecialchars($treatment['name']) ?>" 
                                         class="treatment-thumbnail"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td class="treatment-name">
                                    <div class="treatment-info">
                                        <h4><?= htmlspecialchars($treatment['name']) ?></h4>
                                        <span class="treatment-id">ID: <?= $treatment['id'] ?></span>
                                    </div>
                                </td>
                                <td class="treatment-description"><?= htmlspecialchars($treatment['description']) ?></td>
                                <td class="treatment-oils">
                                    <?php $oils = $treatmentOilsMap[$treatment['id']] ?? []; ?>
                                    <?php if (!empty($oils)): ?>
                                        <?php foreach ($oils as $oil): ?>
                                            <span style="display:inline-block; background:#e8f5ee; color:#5b8a6e; border-radius:12px; padding:2px 8px; font-size:0.78rem; margin:2px;"><?= htmlspecialchars($oil) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span style="color:#aaa; font-size:0.85rem;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="duration"><?= htmlspecialchars($treatment['duration']) ?></td>
                                <td class="price">Rs. <?= htmlspecialchars($treatment['price']) ?></td>
                                <td class="status">
                                    <span class="status-badge <?= strtolower($treatment['status']) ?>">
                                        <?= htmlspecialchars($treatment['status']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="action-btn edit-btn" onclick="editTreatment(<?= $treatment['id'] ?>)">
                                        Edit
                                    </button>
                                    <?php if ($treatment['status'] === 'Active'): ?>
                                        <button class="action-btn delete-btn" onclick="deactivateTreatment(<?= $treatment['id'] ?>)">
                                            Deactivate
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn activate-btn" onclick="activateTreatment(<?= $treatment['id'] ?>)">
                                            Activate
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div><!-- end treatments table container -->
        </div><!-- end tab-treatments -->

        <!-- Staff Assignments Tab -->
        <div id="tab-staff-assign" style="display:none;">
            <table class="treatments-table">
                <thead>
                    <tr>
                        <th>Treatment</th>
                        <th>Primary Therapist 1</th>
                        <th>Primary Therapist 2</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($treatments as $t): ?>
                    <?php $sa = $staffAssignments[$t['id']] ?? null; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                        <td><?= $sa ? htmlspecialchars($sa['staff1_name']) : '<span style="color:#aaa;">Unassigned</span>' ?></td>
                        <td><?= $sa ? htmlspecialchars($sa['staff2_name']) : '<span style="color:#aaa;">Unassigned</span>' ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openAssignModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['name']) ?>', <?= $sa['primary_staff1_id'] ?? 0 ?>, <?= $sa['primary_staff2_id'] ?? 0 ?>)">
                                <?= $sa ? 'Edit' : 'Assign' ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Assign Modal -->
        <div id="assignModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:12px; padding:2rem; width:420px; max-width:95%;">
                <h3 id="assignModalTitle" style="margin-bottom:1.2rem; color:#5c3d1e;">Assign Staff</h3>
                <input type="hidden" id="assign_treatment_id">
                <div style="margin-bottom:1rem;">
                    <label style="font-weight:600; display:block; margin-bottom:0.4rem;">Primary Therapist 1</label>
                    <select id="assign_staff1" style="width:100%; padding:0.6rem; border:1px solid #ddd; border-radius:6px;">
                        <option value="0">-- Unassigned --</option>
                        <?php foreach ($staffUsers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="font-weight:600; display:block; margin-bottom:0.4rem;">Primary Therapist 2</label>
                    <select id="assign_staff2" style="width:100%; padding:0.6rem; border:1px solid #ddd; border-radius:6px;">
                        <option value="0">-- Unassigned --</option>
                        <?php foreach ($staffUsers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; gap:0.8rem; justify-content:flex-end;">
                    <button onclick="closeAssignModal()" style="padding:0.5rem 1.2rem; border:1px solid #ddd; border-radius:6px; background:#fff; cursor:pointer;">Cancel</button>
                    <button onclick="saveAssignment()" style="padding:0.5rem 1.2rem; border:none; border-radius:6px; background:#8B7355; color:#fff; font-weight:600; cursor:pointer;">Save</button>
                </div>
            </div>
        </div>

    </main>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.treatment-row');
            
            rows.forEach(row => {
                const treatmentName = row.querySelector('.treatment-name h4').textContent.toLowerCase();
                const description = row.querySelector('.treatment-description').textContent.toLowerCase();
                
                if (treatmentName.includes(searchTerm) || description.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('durationFilter').addEventListener('change', filterTable);

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const durationFilter = document.getElementById('durationFilter').value;
            const rows = document.querySelectorAll('.treatment-row');
            
            rows.forEach(row => {
                const status = row.querySelector('.status-badge').textContent;
                const duration = row.querySelector('.duration').textContent;
                
                const statusMatch = !statusFilter || status === statusFilter;
                const durationMatch = !durationFilter || duration === durationFilter;
                
                if (statusMatch && durationMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        async function editTreatment(id) {
            // Fetch treatment data from server
            try {
                const formData = new FormData();
                formData.append('treatment_id', id);
                formData.append('action', 'get');
                
                const res = await fetch('/dheergayu/app/Controllers/TreatmentController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await res.json();
                if (result.success && result.treatment) {
                    const t = result.treatment;
                    // Build URL with query parameters
                    const params = new URLSearchParams({
                        treatment_id: t.treatment_id,
                        treatment_name: t.treatment_name || '',
                        description: t.description || '',
                        duration: t.duration || '',
                        price: t.price || '',
                        status: t.status || 'Active'
                    });
                    window.location.href = 'addnewtreatment.php?' + params.toString();
                } else {
                    alert('Error: Could not load treatment data');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to load treatment data: ' + err.message);
            }
        }

        async function deactivateTreatment(id) {
            if (!confirm('Are you sure you want to deactivate this treatment? It will no longer be visible to patients.')) return;
            
            try {
                const formData = new FormData();
                formData.append('treatment_id', id);
                formData.append('action', 'deactivate');
                
                const res = await fetch('/dheergayu/app/Controllers/TreatmentController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await res.json();
                if (result.success) {
                    alert('✅ Treatment deactivated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to deactivate treatment'));
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to deactivate treatment: ' + err.message);
            }
        }

        async function activateTreatment(id) {
            if (!confirm('Are you sure you want to activate this treatment? It will be visible to patients.')) return;
            
            try {
                const formData = new FormData();
                formData.append('treatment_id', id);
                formData.append('action', 'activate');
                
                const res = await fetch('/dheergayu/app/Controllers/TreatmentController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await res.json();
                if (result.success) {
                    alert('✅ Treatment activated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to activate treatment'));
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Failed to activate treatment: ' + err.message);
            }
        }

        function switchAdminTab(tab, btn) {
            document.getElementById('tab-treatments').style.display = tab === 'treatments' ? '' : 'none';
            document.getElementById('tab-staff-assign').style.display = tab === 'staff-assign' ? '' : 'none';
            document.querySelectorAll('.admin-tab-btn').forEach(b => {
                b.style.background = '#f0ece6';
                b.style.color = '#555';
                b.style.borderBottom = '2px solid transparent';
            });
            btn.style.background = 'white';
            btn.style.color = '#8B7355';
            btn.style.borderBottom = '2px solid white';
        }

        function openAssignModal(treatmentId, treatmentName, staff1Id, staff2Id) {
            document.getElementById('assign_treatment_id').value = treatmentId;
            document.getElementById('assignModalTitle').textContent = 'Assign Staff — ' + treatmentName;
            document.getElementById('assign_staff1').value = staff1Id || 0;
            document.getElementById('assign_staff2').value = staff2Id || 0;
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }

        async function saveAssignment() {
            const treatmentId = document.getElementById('assign_treatment_id').value;
            const staff1 = document.getElementById('assign_staff1').value;
            const staff2 = document.getElementById('assign_staff2').value;
            const form = new FormData();
            form.append('treatment_id', treatmentId);
            form.append('staff1_id', staff1);
            form.append('staff2_id', staff2);
            try {
                const res = await fetch('/dheergayu/public/api/treatment-staff-assign.php', { method: 'POST', body: form });
                const data = await res.json();
                if (data.success) {
                    alert('✅ Staff assignment saved');
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Failed to save'));
                }
            } catch (e) {
                alert('❌ Error: ' + e.message);
            }
        }
    </script>
</body>
</html>
