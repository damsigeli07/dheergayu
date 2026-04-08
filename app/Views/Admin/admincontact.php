<?php
session_start();

// Check if admin is logged in
// Add your authentication logic here

require_once __DIR__ . '/../../../config/config.php';

// Ensure admin_reply and replied_at columns exist
$checkCol = $conn->query("SHOW COLUMNS FROM contact_submissions LIKE 'admin_reply'");
if ($checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE contact_submissions ADD COLUMN admin_reply TEXT DEFAULT NULL AFTER message");
    $conn->query("ALTER TABLE contact_submissions ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL AFTER admin_reply");
}

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

        .btn-reply {
            background: #FF8C42;
            color: white;
        }

        .btn-reply:hover {
            background: #e67a30;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
            animation: fadeIn 0.25s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 620px;
            width: 92%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2), 0 0 0 1px rgba(0,0,0,0.05);
            animation: slideUp 0.3s ease;
        }

        .modal-content::-webkit-scrollbar {
            width: 6px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #d0d0d0;
            border-radius: 3px;
        }

        .modal-header {
            background: linear-gradient(135deg, #5a4a3a, #8B7355);
            padding: 22px 28px;
            border-radius: 16px 16px 0 0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .modal-title {
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .modal-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: rgba(255,255,255,0.7);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            line-height: 1;
        }

        .modal-close:hover {
            color: #fff;
            background: rgba(255,255,255,0.15);
        }

        .modal-body-inner {
            padding: 24px 28px 28px;
        }

        .detail-row {
            margin-bottom: 0;
            padding: 14px 0;
            border-bottom: 1px solid #f0ede8;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #8B7355;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 2px;
        }

        .detail-value {
            color: #333;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .detail-value a {
            color: #FF8C42;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .detail-value a:hover {
            color: #e67a30;
            text-decoration: underline;
        }

        .detail-row-inline {
            display: flex;
            gap: 20px;
        }

        .detail-row-inline .detail-row {
            flex: 1;
            border-bottom: none;
            background: #faf8f5;
            padding: 12px 16px;
            border-radius: 10px;
        }

        .message-box {
            background: #faf8f5;
            border-radius: 10px;
            padding: 14px 16px;
            border-left: 3px solid #8B7355;
            line-height: 1.6;
            color: #444;
        }

        .status-selector {
            padding: 10px 14px;
            border: 2px solid #e8e4de;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            background: #faf8f5;
            color: #333;
            cursor: pointer;
            transition: border-color 0.3s;
            appearance: auto;
        }

        .status-selector:focus {
            outline: none;
            border-color: #FF8C42;
        }

        .submitted-time {
            color: #999;
            font-size: 0.85rem;
        }

        /* Reply Form */
        .reply-section {
            margin-top: 0;
            padding: 22px 28px 28px;
            background: linear-gradient(to bottom, #f9f7f4, #fff);
            border-top: 2px solid #f0ede8;
            border-radius: 0 0 16px 16px;
        }

        .reply-section h3 {
            font-size: 1rem;
            color: #5a4a3a;
            margin-bottom: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reply-section h3::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 18px;
            background: linear-gradient(135deg, #FF8C42, #FFB84D);
            border-radius: 2px;
        }

        .reply-textarea {
            width: 100%;
            min-height: 110px;
            padding: 14px;
            border: 2px solid #e8e4de;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            resize: vertical;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fff;
            line-height: 1.5;
        }

        .reply-textarea:focus {
            outline: none;
            border-color: #FF8C42;
            box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.12);
        }

        .reply-btn {
            margin-top: 14px;
            padding: 11px 28px;
            background: linear-gradient(135deg, #FF8C42, #FFB84D);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reply-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 140, 66, 0.4);
        }

        .reply-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .previous-reply {
            background: #f8f9fa;
            border-left: 4px solid #FF8C42;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 8px;
        }

        .previous-reply .reply-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 6px;
        }

        .reply-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: 500;
        }

        .reply-error {
            background: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: 500;
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
            <div class="modal-body-inner" id="modalBody"></div>
            <div id="replySection"></div>
        </div>
    </div>

    <script>
        function viewSubmission(data) {
            const modal = document.getElementById('viewModal');
            const modalBody = document.getElementById('modalBody');
            const replySection = document.getElementById('replySection');

            // Build previous reply section if exists
            let previousReplyHtml = '';
            if (data.admin_reply) {
                const replyDate = data.replied_at ? new Date(data.replied_at).toLocaleString() : '';
                previousReplyHtml = `
                    <div class="detail-row" style="border-bottom:none;">
                        <div class="detail-label">Your Previous Reply</div>
                        <div class="previous-reply">
                            <div class="detail-value">${data.admin_reply}</div>
                            ${replyDate ? `<div class="reply-date">Replied on: ${replyDate}</div>` : ''}
                        </div>
                    </div>
                `;
            }

            modalBody.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Name</div>
                    <div class="detail-value" style="font-size:1.05rem; font-weight:500;">${data.name}</div>
                </div>
                <div class="detail-row-inline">
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:${data.email}">${data.email}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><a href="tel:${data.phone}">${data.phone}</a></div>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Subject</div>
                    <div class="detail-value">
                        <span class="subject-badge" style="font-size:0.85rem; padding:5px 14px;">${data.subject.charAt(0).toUpperCase() + data.subject.slice(1)}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Message</div>
                    <div class="message-box">${data.message}</div>
                </div>
                <div class="detail-row-inline">
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
                        <div class="detail-value submitted-time">${new Date(data.created_at).toLocaleString()}</div>
                    </div>
                </div>
                ${previousReplyHtml}
            `;

            replySection.innerHTML = `
                <div class="reply-section">
                    <h3>${data.admin_reply ? 'Send Another Reply' : 'Reply to Inquiry'}</h3>
                    <textarea class="reply-textarea" id="replyMessage" placeholder="Type your reply to ${data.name}..."></textarea>
                    <div id="replyFeedback"></div>
                    <button class="reply-btn" id="replyBtn" onclick="sendReply(${data.id}, '${data.email.replace(/'/g, "\\'")}', '${data.name.replace(/'/g, "\\'")}', '${data.subject.replace(/'/g, "\\'")}')">
                        Send Reply
                    </button>
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

        function sendReply(id, email, name, subject) {
            const replyMessage = document.getElementById('replyMessage').value.trim();
            const replyBtn = document.getElementById('replyBtn');
            const feedback = document.getElementById('replyFeedback');

            if (!replyMessage) {
                feedback.innerHTML = '<div class="reply-error">Please enter a reply message.</div>';
                return;
            }

            replyBtn.disabled = true;
            replyBtn.textContent = 'Sending...';
            feedback.innerHTML = '';

            fetch('/dheergayu/public/api/reply-contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&reply_message=${encodeURIComponent(replyMessage)}&recipient_email=${encodeURIComponent(email)}&recipient_name=${encodeURIComponent(name)}&subject=${encodeURIComponent(subject)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    feedback.innerHTML = `<div class="reply-success">${data.message}</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    feedback.innerHTML = `<div class="reply-error">${data.error || 'Failed to send reply'}</div>`;
                    replyBtn.disabled = false;
                    replyBtn.textContent = 'Send Reply';
                }
            })
            .catch(() => {
                feedback.innerHTML = '<div class="reply-error">Network error. Please try again.</div>';
                replyBtn.disabled = false;
                replyBtn.textContent = 'Send Reply';
            });
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