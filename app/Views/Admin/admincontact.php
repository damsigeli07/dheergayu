<?php
session_start();

// Check if admin is logged in
// Add your authentication logic here

require_once __DIR__ . '/../../../config/config.php';

// Fetch all contact submissions
$query = "SELECT * FROM contact_submissions ORDER BY created_at DESC";
$result = $conn->query($query);

// Count by status
$statusQuery = "SELECT status, COUNT(*) as count FROM contact_submissions GROUP BY status";
$statusResult = $conn->query($statusQuery);
$statusCounts = ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
while ($row = $statusResult->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Submissions - Admin</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f5f2;
        }

        body.has-sidebar {
            margin-left: 260px;
        }

        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            color: #8B7355;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .stat-card.new { border-left: 4px solid #4CAF50; }
        .stat-card.read { border-left: 4px solid #2196F3; }
        .stat-card.replied { border-left: 4px solid #FF9800; }
        .stat-card.archived { border-left: 4px solid #9E9E9E; }

        /* Filters */
        .filters {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Submissions Table */
        .submissions-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .submissions-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e1e5e9;
        }

        .submissions-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .submissions-table tr:hover {
            background: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-new { background: #e8f5e9; color: #2e7d32; }
        .status-read { background: #e3f2fd; color: #1565c0; }
        .status-replied { background: #fff3e0; color: #e65100; }
        .status-archived { background: #f5f5f5; color: #616161; }

        .subject-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #f0f0f0;
            color: #666;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-view {
            background: #2196F3;
            color: white;
        }

        .btn-view:hover {
            background: #1976D2;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }

        .modal-close {
            float: right;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .modal-close:hover {
            color: #333;
        }

        .detail-row {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 1rem;
        }

        .status-selector {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
        }

        @media (max-width: 992px) {
            body.has-sidebar {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .submissions-table {
                font-size: 0.9rem;
            }

            .submissions-table th,
            .submissions-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body class="has-sidebar">

    <!-- Sidebar -->
    <header class="header">
        <div class="header-top">
            <img src="/dheergayu/public/assets/images/dheergayu.png" class="logo" alt="Logo" />
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
            <button class="nav-btn active">Contact Submissions</button>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Admin</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Contact Submissions</h1>
            <p class="page-subtitle">Manage and respond to customer inquiries</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card new">
                <div class="stat-card-header">
                    <div class="stat-label">New</div>
                    <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">📩</div>
                </div>
                <div class="stat-value"><?= $statusCounts['new'] ?></div>
            </div>

            <div class="stat-card read">
                <div class="stat-card-header">
                    <div class="stat-label">Read</div>
                    <div class="stat-icon" style="background: #e3f2fd; color: #1565c0;">👁️</div>
                </div>
                <div class="stat-value"><?= $statusCounts['read'] ?></div>
            </div>

            <div class="stat-card replied">
                <div class="stat-card-header">
                    <div class="stat-label">Replied</div>
                    <div class="stat-icon" style="background: #fff3e0; color: #e65100;">✉️</div>
                </div>
                <div class="stat-value"><?= $statusCounts['replied'] ?></div>
            </div>

            <div class="stat-card archived">
                <div class="stat-card-header">
                    <div class="stat-label">Archived</div>
                    <div class="stat-icon" style="background: #f5f5f5; color: #616161;">📁</div>
                </div>
                <div class="stat-value"><?= $statusCounts['archived'] ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Status</label>
                <select id="filterStatus" onchange="filterSubmissions()">
                    <option value="">All</option>
                    <option value="new">New</option>
                    <option value="read">Read</option>
                    <option value="replied">Replied</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Subject</label>
                <select id="filterSubject" onchange="filterSubmissions()">
                    <option value="">All</option>
                    <option value="appointment">Appointment</option>
                    <option value="treatment">Treatment</option>
                    <option value="general">General</option>
                    <option value="feedback">Feedback</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="searchInput" placeholder="Name, email, phone..." onkeyup="filterSubmissions()">
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="submissions-container">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="submissionsTableBody">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-status="<?= $row['status'] ?>" data-subject="<?= $row['subject'] ?>" data-search="<?= strtolower($row['name'] . ' ' . $row['email'] . ' ' . $row['phone']) ?>">
                            <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($row['email']) ?><br>
                                <small><?= htmlspecialchars($row['phone']) ?></small>
                            </td>
                            <td><span class="subject-badge"><?= ucfirst($row['subject']) ?></span></td>
                            <td><span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td>
                                <div class="action-btns">
                                    <button class="action-btn btn-view" onclick='viewSubmission(<?= json_encode($row) ?>)'>View</button>
                                    <button class="action-btn btn-delete" onclick="deleteSubmission(<?= $row['id'] ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h2 class="modal-title">Submission Details</h2>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewSubmission(data) {
            const modal = document.getElementById('viewModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">${data.name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><a href="mailto:${data.email}">${data.email}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><a href="tel:${data.phone}">${data.phone}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Subject</div>
                    <div class="detail-value">${data.subject.charAt(0).toUpperCase() + data.subject.slice(1)}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Message</div>
                    <div class="detail-value">${data.message}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status</div>
                    <select class="status-selector" onchange="updateStatus(${data.id}, this.value)">
                        <option value="new" ${data.status === 'new' ? 'selected' : ''}>New</option>
                        <option value="read" ${data.status === 'read' ? 'selected' : ''}>Read</option>
                        <option value="replied" ${data.status === 'replied' ? 'selected' : ''}>Replied</option>
                        <option value="archived" ${data.status === 'archived' ? 'selected' : ''}>Archived</option>
                    </select>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submitted</div>
                    <div class="detail-value">${new Date(data.created_at).toLocaleString()}</div>
                </div>
            `;
            
            modal.classList.add('active');
            
            // Mark as read if it's new
            if (data.status === 'new') {
                updateStatus(data.id, 'read');
            }
        }

        function closeModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        function updateStatus(id, status) {
            fetch('/dheergayu/public/api/update-contact-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&status=${status}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            });
        }

        function deleteSubmission(id) {
            if (!confirm('Are you sure you want to delete this submission?')) return;
            
            fetch('/dheergayu/public/api/delete-contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete submission');
                }
            });
        }

        function filterSubmissions() {
            const status = document.getElementById('filterStatus').value.toLowerCase();
            const subject = document.getElementById('filterSubject').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            const rows = document.querySelectorAll('#submissionsTableBody tr');
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowSubject = row.getAttribute('data-subject');
                const rowSearch = row.getAttribute('data-search');
                
                const statusMatch = !status || rowStatus === status;
                const subjectMatch = !subject || rowSubject === subject;
                const searchMatch = !search || rowSearch.includes(search);
                
                if (statusMatch && subjectMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('viewModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>