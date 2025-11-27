<?php
// Fetch treatments from database
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
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
            <a href="admintreatment.php" class="nav-btn">Treatments</a>
            <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <!-- Dropdown -->
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
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

    </script>
</body>
</html>
