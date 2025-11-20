<?php
// Hardcoded treatments data from patient page
$treatments = [
    [
        'id' => 1,
        'name' => 'Nasya',
        'description' => 'Traditional full-body massage using warm herbal oils',
        'duration' => '30 min',
        'price' => '2,500.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/asthma.png',
        'conditions' => ['Asthma', 'ENT Disorders']
    ],
    [
        'id' => 2,
        'name' => 'Panchakarma Detox',
        'description' => 'Complete detoxification and rejuvenation therapy',
        'duration' => '90 min',
        'price' => '9,000.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/skin_diseases.jpg',
        'conditions' => ['Diabetes', 'Skin Diseases']
    ],
    [
        'id' => 3,
        'name' => 'Vashpa Sweda',
        'description' => 'Nasal administration of herbal oils',
        'duration' => '30 min',
        'price' => '3,500.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/respiratory_disorders.jpg',
        'conditions' => ['Respiratory Disorders']
    ],
    [
        'id' => 4,
        'name' => 'Elakizhi',
        'description' => 'Specialized treatment for lower back pain',
        'duration' => '60 min',
        'price' => '7,000.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/arthritis.jpg',
        'conditions' => ['Arthritis']
    ],
    [
        'id' => 5,
        'name' => 'Basti',
        'description' => 'Traditional Ayurvedic foot massage',
        'duration' => '45 min',
        'price' => '5,000.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/paralysis.jpg',
        'conditions' => ['Neurological Diseases and Paralysis', 'Osteoporosis']
    ],
    [
        'id' => 6,
        'name' => 'Abhyanga',
        'description' => 'Energy point therapy',
        'duration' => '60 min',
        'price' => '5,000.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/bone_disorders.png',
        'conditions' => ['Dislocation Features of Joints & Bones']
    ],
    [
        'id' => 7,
        'name' => 'Shirodhara',
        'description' => 'Energy point therapy',
        'duration' => '45 min',
        'price' => '7,000.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/ENT_disorders.jpg',
        'conditions' => ['Anxiety, Stress and Depression']
    ],
    [
        'id' => 8,
        'name' => 'Udvartana',
        'description' => 'Energy point therapy',
        'duration' => '45 min',
        'price' => '3,500.00',
        'status' => 'Active',
        'image' => '/dheergayu/public/assets/images/Admin/health-treatments.jpg',
        'conditions' => ['Cholesterol']
    ]
];

// Calculate statistics
$totalTreatments = count($treatments);
$activeTreatments = count($treatments); // All are active
$inactiveTreatments = 0;
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
                                    ✏️ Edit
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteTreatment(<?= $treatment['id'] ?>)">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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

        function editTreatment(id) {
            alert('Edit functionality for treatment ID: ' + id + '\nThis would open an edit form for the treatment.');
        }

        function deleteTreatment(id) {
            if (!confirm('Are you sure you want to delete this treatment? This action cannot be undone.')) return;
            alert('Delete functionality for treatment ID: ' + id + '\nThis would delete the treatment from the system.');
        }

    </script>
</body>
</html>
