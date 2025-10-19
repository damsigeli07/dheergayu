<?php
require_once __DIR__ . '/../../../core/bootloader.php';

use App\Models\TreatmentModel;

$model = new TreatmentModel();
$treatments = $model->getAll();

// Calculate statistics
$totalTreatments = count($treatments);
$activeTreatments = 0;
$inactiveTreatments = 0;
$totalRevenue = 0.0;

foreach ($treatments as $treatment) {
    if (($treatment['status'] ?? '') === 'Active') {
        $activeTreatments++;
        $totalRevenue += (float)($treatment['price'] ?? 0);
    } else {
        $inactiveTreatments++;
    }
}
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
<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <a href="admindashboard.php" class="nav-btn">Home</a>
                <a href="admininventory.php" class="nav-btn">Products</a>
                <a href="adminappointment.php" class="nav-btn">Appointments</a>
                <a href="adminusers.php" class="nav-btn">Users</a>
                <a href="admintreatment.php" class="nav-btn">Treatments</a>
                <a href="adminsuppliers.php" class="nav-btn">Supplier-info</a>
            </nav>
        </div>
        <div class="header-right">
            <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Dheergayu Logo" class="logo">
            <h1 class="header-title">Dheergayu</h1>
            <div class="user-section">
                <div class="user-icon" id="user-icon">👤</div>
                <span class="user-role">Admin</span>
                <!-- Dropdown -->
        <div class="user-dropdown" id="user-dropdown">
            <a href="adminprofile.php" class="profile-btn">Profile</a>
            <a href="../patient/login.php" class="logout-btn">Logout</a>
        </div>
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
            
            <div class="overview-card revenue">
                <div class="overview-icon">💰</div>
                <div class="overview-content">
                    <h3>Total Revenue</h3>
                    <p class="overview-number">Rs. <?= number_format($totalRevenue) ?></p>
                    <p class="overview-desc">From active treatments</p>
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
                    <option value="30 min">30 min</option>
                    <option value="45 min">45 min</option>
                    <option value="60 min">60 min</option>
                    <option value="75 min">75 min</option>
                    <option value="90 min">90 min</option>
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
                        <th>Treatment Name</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($treatments as $treatment): ?>
                        <tr class="treatment-row <?= strtolower($treatment['status']) ?>">
                            <td class="treatment-name">
                                <div class="treatment-info">
                                    <h4><?= htmlspecialchars($treatment['treatment_name']) ?></h4>
                                    <span class="treatment-id">ID: <?= $treatment['treatment_id'] ?></span>
                                </div>
                            </td>
                            <td class="treatment-description"><?= htmlspecialchars($treatment['description']) ?></td>
                            <td class="duration"><?= htmlspecialchars($treatment['duration']) ?></td>
                            <td class="price">Rs. <?= htmlspecialchars(number_format((float)$treatment['price'], 2)) ?></td>
                            <td class="status">
                                <span class="status-badge <?= strtolower($treatment['status']) ?>">
                                    <?= htmlspecialchars($treatment['status']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="action-btn edit-btn" onclick="openEditTreatment(<?= (int)$treatment['treatment_id'] ?>)">
                                    ✏️ Edit
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteTreatment(<?= (int)$treatment['treatment_id'] ?>)">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-schedule" onclick="viewTreatmentSchedule()">📅 View Treatment Schedule</button>
            <button class="btn btn-export" onclick="exportTreatments()">📊 Export Report</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ Print Report</button>
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

        async function openEditTreatment(id) {
            const res = await fetch(`/dheergayu/public/api/treatments/show?id=${id}`);
            const data = await res.json();
            const item = data.data;
            if (!item) { alert('Not found'); return; }
            const params = new URLSearchParams({
                treatment_id: item.treatment_id,
                treatment_name: item.treatment_name,
                description: item.description || '',
                duration: item.duration,
                price: item.price,
                status: item.status
            });
            window.location.href = `addnewtreatment.php?${params.toString()}`;
        }

        async function deleteTreatment(id) {
            if (!confirm('Are you sure you want to delete this treatment? This action cannot be undone.')) return;
            const form = new FormData();
            form.append('treatment_id', id);
            const res = await fetch('/dheergayu/public/api/treatments/delete', { method: 'POST', body: form });
            const data = await res.json();
            if (data.success) {
                alert('✅ Treatment deleted');
                location.reload();
            } else {
                alert('Delete failed');
            }
        }

        // View treatment schedule functionality
        function viewTreatmentSchedule() {
            alert('Treatment Schedule view functionality would be implemented here. This would show the daily/weekly schedule of treatments.');
        }

        // Export treatments functionality
        function exportTreatments() {
            alert('Export functionality would generate a detailed treatments report here.');
        }
    </script>
</body>
</html>
