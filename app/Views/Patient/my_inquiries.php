<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';

require_once __DIR__ . '/../../../config/config.php';

// Ensure columns exist
$checkCol = $conn->query("SHOW COLUMNS FROM contact_submissions LIKE 'admin_reply'");
if ($checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE contact_submissions ADD COLUMN admin_reply TEXT DEFAULT NULL AFTER message");
    $conn->query("ALTER TABLE contact_submissions ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL AFTER admin_reply");
}

// Fetch this user's submissions by email or name
$stmt = $conn->prepare("SELECT * FROM contact_submissions WHERE email = ? OR name = ? ORDER BY created_at DESC");
$stmt->bind_param("ss", $userEmail, $userName);
$stmt->execute();
$result = $stmt->get_result();
$inquiries = [];
while ($row = $result->fetch_assoc()) {
    $inquiries[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiries - Dheergayu</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/contact.css">
    <style>
        .inquiries-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .page-title {
            font-size: 2rem;
            color: #5a4a3a;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #888;
            font-size: 1rem;
        }

        /* Inquiry Cards */
        .inquiry-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .inquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            cursor: pointer;
            user-select: none;
            gap: 12px;
        }

        .inquiry-header:hover {
            background: #faf8f5;
        }

        .inquiry-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
            min-width: 0;
        }

        .inquiry-subject-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .icon-appointment { background: #e8f5e9; }
        .icon-treatment { background: #e3f2fd; }
        .icon-general { background: #fff3e0; }
        .icon-feedback { background: #f3e5f5; }
        .icon-other { background: #f5f5f5; }

        .inquiry-title-group {
            min-width: 0;
        }

        .inquiry-subject {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .inquiry-date {
            color: #999;
            font-size: 0.8rem;
            margin-top: 2px;
        }

        .inquiry-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .inquiry-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-new { background: #e8f5e9; color: #2e7d32; }
        .status-read { background: #e3f2fd; color: #1565c0; }
        .status-replied { background: #fff3e0; color: #e65100; }
        .status-archived { background: #f5f5f5; color: #616161; }

        .toggle-icon {
            color: #bbb;
            font-size: 18px;
            transition: transform 0.3s;
        }

        .inquiry-card.open .toggle-icon {
            transform: rotate(180deg);
        }

        /* Inquiry Body */
        .inquiry-body {
            display: none;
            padding: 0 24px 22px;
            border-top: 1px solid #f0ede8;
        }

        .inquiry-card.open .inquiry-body {
            display: block;
        }

        .message-section {
            padding: 16px 0;
        }

        .section-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8B7355;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .message-bubble {
            padding: 14px 18px;
            border-radius: 12px;
            line-height: 1.6;
            font-size: 0.92rem;
        }

        .my-message {
            background: #f0ede8;
            color: #444;
            border-bottom-left-radius: 4px;
        }

        .admin-reply-bubble {
            background: linear-gradient(135deg, #5a4a3a, #8B7355);
            color: #fff;
            border-bottom-right-radius: 4px;
            margin-top: 4px;
        }

        .reply-meta {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 8px;
        }

        .no-reply-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #faf8f5;
            padding: 10px 16px;
            border-radius: 10px;
            color: #999;
            font-size: 0.85rem;
            border: 1px dashed #ddd;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 1.2rem;
            color: #5a4a3a;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-text {
            color: #999;
            margin-bottom: 24px;
        }

        .contact-link {
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(135deg, #FF8C42, #FFB84D);
            color: white;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .contact-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        }

        /* Summary bar */
        .summary-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .summary-chip {
            background: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #666;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .summary-chip strong {
            color: #5a4a3a;
        }

        @media (max-width: 600px) {
            .inquiry-header {
                padding: 14px 16px;
            }
            .inquiry-body {
                padding: 0 16px 18px;
            }
            .inquiry-header-left {
                gap: 10px;
            }
            .inquiry-subject-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php">CONTACT US</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="/dheergayu/app/Views/Patient/home.php" class="back-btn">&larr; Back to Home</a>
            </div>
        </div>
    </header>

    <div class="inquiries-wrapper">
        <div class="page-header">
            <h1 class="page-title">My Inquiries</h1>
            <p class="page-subtitle">Track your messages and replies from Dheergayu</p>
        </div>

        <?php if (count($inquiries) > 0): ?>
            <?php
                $totalCount = count($inquiries);
                $repliedCount = count(array_filter($inquiries, fn($i) => $i['status'] === 'replied'));
                $pendingCount = $totalCount - $repliedCount;
            ?>
            <div class="summary-bar">
                <div class="summary-chip"><strong><?= $totalCount ?></strong> Total</div>
                <div class="summary-chip"><strong><?= $repliedCount ?></strong> Replied</div>
                <div class="summary-chip"><strong><?= $pendingCount ?></strong> Awaiting Reply</div>
            </div>

            <?php foreach ($inquiries as $index => $inquiry): ?>
                <?php
                    $subjectIcons = [
                        'appointment' => '📅',
                        'treatment' => '🌿',
                        'general' => '💬',
                        'feedback' => '⭐',
                        'other' => '📝'
                    ];
                    $icon = $subjectIcons[$inquiry['subject']] ?? '📝';
                    $iconClass = 'icon-' . ($inquiry['subject'] ?: 'other');
                    $hasReply = !empty($inquiry['admin_reply']);
                ?>
                <div class="inquiry-card <?= $index === 0 ? 'open' : '' ?>">
                    <div class="inquiry-header" onclick="toggleCard(this)">
                        <div class="inquiry-header-left">
                            <div class="inquiry-subject-icon <?= $iconClass ?>"><?= $icon ?></div>
                            <div class="inquiry-title-group">
                                <div class="inquiry-subject"><?= ucfirst(htmlspecialchars($inquiry['subject'])) ?> Inquiry</div>
                                <div class="inquiry-date"><?= date('M d, Y \a\t h:i A', strtotime($inquiry['created_at'])) ?></div>
                            </div>
                        </div>
                        <div class="inquiry-header-right">
                            <span class="inquiry-status status-<?= $inquiry['status'] ?>"><?= ucfirst($inquiry['status']) ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                    </div>
                    <div class="inquiry-body">
                        <div class="message-section">
                            <div class="section-label">Your Message</div>
                            <div class="message-bubble my-message">
                                <?= nl2br(htmlspecialchars($inquiry['message'])) ?>
                            </div>
                        </div>

                        <div class="message-section">
                            <div class="section-label">Reply from Dheergayu</div>
                            <?php if ($hasReply): ?>
                                <div class="message-bubble admin-reply-bubble">
                                    <?= nl2br(htmlspecialchars($inquiry['admin_reply'])) ?>
                                    <div class="reply-meta">
                                        Replied on <?= date('M d, Y \a\t h:i A', strtotime($inquiry['replied_at'])) ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-reply-badge">
                                    ⏳ Awaiting reply — we'll get back to you soon
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <div class="empty-title">No inquiries yet</div>
                <div class="empty-text">You haven't sent us any messages. Have a question? We're here to help!</div>
                <a href="contact_us.php" class="contact-link">Contact Us</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleCard(header) {
            const card = header.closest('.inquiry-card');
            card.classList.toggle('open');
        }
    </script>
</body>
</html>
