<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('PHARMACIST_SID');
    session_set_cookie_params(['path' => '/', 'httponly' => true]);
    session_start();
}
require_once __DIR__ . '/../../includes/auth_pharmacist.php';
require_once __DIR__ . '/../../../config/config.php';

$today = date('Y-m-d');
$baseWhere = "tp.payment_status = 'Completed' AND ts.status IN ('Confirmed', 'InProgress') AND ts.session_date >= '$today' AND ts.oils_dispensed = 0";

// Today's sessions
$stmt = $conn->prepare("
    SELECT ts.session_id, ts.session_date, ts.session_time, ts.status, ts.session_number,
           tl.treatment_id, tl.treatment_name,
           tp.plan_id, tp.patient_id,
           p.first_name, p.last_name
    FROM treatment_sessions ts
    JOIN treatment_plans tp ON tp.plan_id = ts.plan_id
    JOIN treatment_list tl ON tl.treatment_id = tp.treatment_id
    JOIN patients p ON p.id = tp.patient_id
    WHERE $baseWhere AND ts.session_date = ?
    ORDER BY ts.session_time ASC
");
$stmt->bind_param('s', $today);
$stmt->execute();
$todaySessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// All sessions (date filter optional)
$filterDate = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : null;
if ($filterDate) {
    $stmt = $conn->prepare("
        SELECT ts.session_id, ts.session_date, ts.session_time, ts.status, ts.session_number,
               tl.treatment_id, tl.treatment_name,
               tp.plan_id, tp.patient_id,
               p.first_name, p.last_name
        FROM treatment_sessions ts
        JOIN treatment_plans tp ON tp.plan_id = ts.plan_id
        JOIN treatment_list tl ON tl.treatment_id = tp.treatment_id
        JOIN patients p ON p.id = tp.patient_id
        WHERE $baseWhere AND ts.session_date = ?
        ORDER BY ts.session_date ASC, ts.session_time ASC
    ");
    $stmt->bind_param('s', $filterDate);
} else {
    $stmt = $conn->prepare("
        SELECT ts.session_id, ts.session_date, ts.session_time, ts.status, ts.session_number,
               tl.treatment_id, tl.treatment_name,
               tp.plan_id, tp.patient_id,
               p.first_name, p.last_name
        FROM treatment_sessions ts
        JOIN treatment_plans tp ON tp.plan_id = ts.plan_id
        JOIN treatment_list tl ON tl.treatment_id = tp.treatment_id
        JOIN patients p ON p.id = tp.patient_id
        WHERE $baseWhere
        ORDER BY ts.session_date ASC, ts.session_time ASC
    ");
}
$stmt->execute();
$allSessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dispensed sessions
$stmt = $conn->prepare("
    SELECT ts.session_id, ts.session_date, ts.session_time, ts.status, ts.session_number,
           tl.treatment_id, tl.treatment_name,
           tp.plan_id, tp.patient_id,
           p.first_name, p.last_name
    FROM treatment_sessions ts
    JOIN treatment_plans tp ON tp.plan_id = ts.plan_id
    JOIN treatment_list tl ON tl.treatment_id = tp.treatment_id
    JOIN patients p ON p.id = tp.patient_id
    WHERE ts.oils_dispensed = 1
    ORDER BY ts.session_date DESC, ts.session_time DESC
");
$stmt->execute();
$dispensedSessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Oils map
$oilsMap = [];
$oilsResult = $conn->query("
    SELECT tp.treatment_id, p.name AS oil_name, tp.quantity_per_session
    FROM treatment_products tp
    JOIN products p ON p.product_id = tp.product_id
");
if ($oilsResult) {
    while ($row = $oilsResult->fetch_assoc()) {
        $oilsMap[(int)$row['treatment_id']][] = $row;
    }
}

function oilSummary(array $sessions, array $oilsMap): array {
    $summary = [];
    foreach ($sessions as $s) {
        foreach ($oilsMap[(int)$s['treatment_id']] ?? [] as $oil) {
            $summary[$oil['oil_name']] = ($summary[$oil['oil_name']] ?? 0) + (int)$oil['quantity_per_session'];
        }
    }
    return $summary;
}

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'today';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Preparation - Pharmacist</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Pharmacist/pharmacistinventory.css">
    <style>
        .tab-nav { display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:2px solid #e0e0e0; }
        .tab-btn { padding:0.6rem 1.4rem; border:none; border-radius:8px 8px 0 0; background:#f0f0f0; color:#555; font-size:0.95rem; font-weight:600; cursor:pointer; position:relative; bottom:-2px; border-bottom:2px solid transparent; transition:all 0.2s; }
        .tab-btn.active { background:white; color:#5b8a6e; border-bottom:2px solid white; border-top:2px solid #5b8a6e; }
        .tab-btn:hover:not(.active) { background:#e5e5e5; }
        .tab-count { background:#5b8a6e; color:white; font-size:0.7rem; padding:1px 6px; border-radius:10px; margin-left:4px; }
        .tab-section { display:none; }
        .tab-section.active { display:block; }

        .date-filter { display:flex; align-items:center; gap:0.5rem; margin-bottom:1.2rem; }
        .date-filter input[type="date"] { padding:0.45rem 0.8rem; border:1px solid #ddd; border-radius:6px; font-size:0.9rem; }
        .date-filter button { padding:0.45rem 1rem; background:#5b8a6e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        .date-filter a { padding:0.45rem 0.8rem; background:#ddd; color:#333; border-radius:6px; text-decoration:none; font-weight:600; font-size:0.9rem; }

        .summary-box { background:#f0f7f4; border:1px solid #c3e6cb; border-radius:10px; padding:1rem 1.5rem; margin-bottom:1.5rem; }
        .summary-box h4 { margin:0 0 0.7rem 0; color:#2d6a4f; }
        .summary-grid { display:flex; gap:1.5rem; flex-wrap:wrap; }
        .summary-item { display:flex; align-items:center; gap:0.5rem; font-size:0.9rem; }

        .session-card { background:white; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); margin-bottom:1rem; overflow:hidden; border-left:5px solid #5b8a6e; }
        .session-card-header { display:flex; align-items:center; justify-content:space-between; padding:0.9rem 1.2rem; background:#f8faf9; border-bottom:1px solid #e8e8e8; flex-wrap:wrap; gap:0.5rem; }
        .session-info { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
        .session-time { font-size:1rem; font-weight:700; color:#2d2d2d; }
        .session-date-label { font-size:0.82rem; color:#888; }
        .session-treatment { font-size:1rem; font-weight:600; color:#5b8a6e; }
        .session-patient { font-size:0.88rem; color:#666; }
        .session-number { font-size:0.78rem; background:#e8f5e9; color:#2d6a4f; padding:2px 8px; border-radius:12px; }
        .status-badge { font-size:0.75rem; padding:3px 10px; border-radius:12px; font-weight:600; text-transform:uppercase; }
        .status-confirmed { background:#d1ecf1; color:#0c5460; }
        .status-inprogress { background:#d4edda; color:#155724; }
        .oils-list { padding:0.8rem 1.2rem; display:flex; gap:0.8rem; flex-wrap:wrap; align-items:center; }
        .btn-dispense { margin-left:auto; padding:0.4rem 1.1rem; background:#5b8a6e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.85rem; }
        .btn-dispense:hover { background:#4a7a5e; }
        .btn-dispense:disabled { background:#aaa; cursor:not-allowed; }
        .oil-pill { display:flex; align-items:center; gap:0.4rem; background:#f0f7f4; border:1px solid #c3e6cb; border-radius:20px; padding:0.3rem 0.9rem; font-size:0.85rem; color:#2d6a4f; }
        .oil-qty { background:#5b8a6e; color:white; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:0.72rem; font-weight:700; flex-shrink:0; }
        .empty-state { text-align:center; padding:4rem 2rem; color:#888; }
        .empty-state h3 { font-size:1.3rem; margin-bottom:0.5rem; color:#555; }
    </style>
</head>
<body class="has-sidebar">
<header class="header">
    <div class="header-top">
        <img src="/dheergayu/public/assets/images/dheergayu.png" alt="Logo" class="logo">
        <h1 class="header-title">Dheergayu</h1>
    </div>
    <nav class="navigation">
        <a href="pharmacisthome.php" class="nav-btn">Home</a>
        <a href="pharmacistinventory.php" class="nav-btn">Inventory</a>
        <a href="pharmacistorders.php" class="nav-btn">Orders</a>
        <a href="pharmacistreports.php" class="nav-btn">Reports</a>
            <a href="pharmacistshoporders.php" class="nav-btn">Shop Orders</a>
        <a href="pharmacistrequest.php" class="nav-btn">Request</a>
        <button class="nav-btn active">Treatment Prep</button>
    </nav>
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role">Pharmacist</span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="pharmacistprofile.php" class="profile-btn">Profile</a>
            <a href="/dheergayu/app/Views/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <h2 class="section-title">Treatment Preparation</h2>

    <div class="tab-nav">
        <button class="tab-btn <?= $activeTab === 'today' ? 'active' : '' ?>" onclick="switchTab('today', this)">
            Today's Treatments <span class="tab-count"><?= count($todaySessions) ?></span>
        </button>
        <button class="tab-btn <?= $activeTab === 'all' ? 'active' : '' ?>" onclick="switchTab('all', this)">
            All Treatments <span class="tab-count"><?= count($allSessions) ?></span>
        </button>
        <button class="tab-btn <?= $activeTab === 'dispensed' ? 'active' : '' ?>" onclick="switchTab('dispensed', this)">
            Dispensed <span class="tab-count" style="background:#888;"><?= count($dispensedSessions) ?></span>
        </button>
    </div>

    <!-- TODAY TAB -->
    <div id="tab-today" class="tab-section <?= $activeTab === 'today' ? 'active' : '' ?>">
        <?php if (empty($todaySessions)): ?>
            <div class="empty-state">
                <h3>No sessions today</h3>
                <p>No confirmed treatment sessions for <?= date('M d, Y') ?>.</p>
            </div>
        <?php else: ?>
            <?php $todaySummary = oilSummary($todaySessions, $oilsMap); ?>
            <?php if (!empty($todaySummary)): ?>
            <div class="summary-box">
                <h4>Oils needed today — <?= count($todaySessions) ?> session<?= count($todaySessions) > 1 ? 's' : '' ?></h4>
                <div class="summary-grid">
                    <?php foreach ($todaySummary as $oil => $qty): ?>
                        <div class="summary-item">
                            <div class="oil-qty"><?= $qty ?></div>
                            <strong><?= htmlspecialchars($oil) ?></strong>
                            <span style="color:#888;">bottle<?= $qty > 1 ? 's' : '' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php foreach ($todaySessions as $s):
                $oils = $oilsMap[(int)$s['treatment_id']] ?? [];
            ?>
            <div class="session-card">
                <div class="session-card-header">
                    <div class="session-info">
                        <span class="session-time">⏰ <?= date('g:i A', strtotime($s['session_time'])) ?></span>
                        <span class="session-treatment"><?= htmlspecialchars($s['treatment_name']) ?></span>
                        <span class="session-patient">👤 <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></span>
                        <span class="session-number">Session #<?= $s['session_number'] ?></span>
                    </div>
                    <span class="status-badge status-<?= strtolower($s['status']) ?>"><?= $s['status'] ?></span>
                </div>
                <div class="oils-list">
                    <?php if (empty($oils)): ?>
                        <span style="color:#888;font-size:0.88rem;">No oils linked.</span>
                    <?php else: ?>
                        <?php foreach ($oils as $oil): ?>
                            <div class="oil-pill">
                                <div class="oil-qty"><?= (int)$oil['quantity_per_session'] ?></div>
                                <?= htmlspecialchars($oil['oil_name']) ?>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn-dispense" onclick="dispenseOils(this, <?= $s['session_id'] ?>)">Mark as Dispensed</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- DISPENSED TAB -->
    <div id="tab-dispensed" class="tab-section <?= $activeTab === 'dispensed' ? 'active' : '' ?>">
        <?php if (empty($dispensedSessions)): ?>
            <div class="empty-state">
                <h3>No dispensed records yet</h3>
                <p>Oils you dispense to staff will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($dispensedSessions as $s):
                $oils = $oilsMap[(int)$s['treatment_id']] ?? [];
            ?>
            <div class="session-card" style="border-left-color:#888;opacity:0.85;">
                <div class="session-card-header">
                    <div class="session-info">
                        <span class="session-date-label">📅 <?= date('M d, Y', strtotime($s['session_date'])) ?></span>
                        <span class="session-time">⏰ <?= date('g:i A', strtotime($s['session_time'])) ?></span>
                        <span class="session-treatment"><?= htmlspecialchars($s['treatment_name']) ?></span>
                        <span class="session-patient">👤 <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></span>
                        <span class="session-number">Session #<?= $s['session_number'] ?></span>
                    </div>
                    <span style="font-size:0.78rem;background:#e8f5e9;color:#2d6a4f;padding:3px 10px;border-radius:12px;font-weight:600;">✓ Dispensed</span>
                </div>
                <div class="oils-list">
                    <?php foreach ($oils as $oil): ?>
                        <div class="oil-pill" style="opacity:0.7;">
                            <div class="oil-qty" style="background:#888;"><?= (int)$oil['quantity_per_session'] ?></div>
                            <?= htmlspecialchars($oil['oil_name']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ALL TREATMENTS TAB -->
    <div id="tab-all" class="tab-section <?= $activeTab === 'all' ? 'active' : '' ?>">
        <form class="date-filter" method="GET">
            <input type="hidden" name="tab" value="all">
            <label style="font-weight:600;color:#555;">Filter by date:</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate ?? '') ?>">
            <button type="submit">Filter</button>
            <?php if ($filterDate): ?>
                <a href="?tab=all">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($allSessions)): ?>
            <div class="empty-state">
                <h3>No sessions found</h3>
                <p><?= $filterDate ? 'No confirmed sessions for ' . date('M d, Y', strtotime($filterDate)) . '.' : 'No confirmed treatment sessions recorded.' ?></p>
            </div>
        <?php else: ?>
            <?php $allSummary = oilSummary($allSessions, $oilsMap); ?>
            <?php if (!empty($allSummary)): ?>
            <div class="summary-box">
                <h4><?= $filterDate ? 'Oils needed for ' . date('M d, Y', strtotime($filterDate)) : 'Total oils needed (all sessions)' ?> — <?= count($allSessions) ?> session<?= count($allSessions) > 1 ? 's' : '' ?></h4>
                <div class="summary-grid">
                    <?php foreach ($allSummary as $oil => $qty): ?>
                        <div class="summary-item">
                            <div class="oil-qty"><?= $qty ?></div>
                            <strong><?= htmlspecialchars($oil) ?></strong>
                            <span style="color:#888;">bottle<?= $qty > 1 ? 's' : '' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php foreach ($allSessions as $s):
                $oils = $oilsMap[(int)$s['treatment_id']] ?? [];
            ?>
            <div class="session-card">
                <div class="session-card-header">
                    <div class="session-info">
                        <span class="session-date-label">📅 <?= date('M d, Y', strtotime($s['session_date'])) ?></span>
                        <span class="session-time">⏰ <?= date('g:i A', strtotime($s['session_time'])) ?></span>
                        <span class="session-treatment"><?= htmlspecialchars($s['treatment_name']) ?></span>
                        <span class="session-patient">👤 <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></span>
                        <span class="session-number">Session #<?= $s['session_number'] ?></span>
                    </div>
                    <span class="status-badge status-<?= strtolower($s['status']) ?>"><?= $s['status'] ?></span>
                </div>
                <div class="oils-list">
                    <?php if (empty($oils)): ?>
                        <span style="color:#888;font-size:0.88rem;">No oils linked.</span>
                    <?php else: ?>
                        <?php foreach ($oils as $oil): ?>
                            <div class="oil-pill">
                                <div class="oil-qty"><?= (int)$oil['quantity_per_session'] ?></div>
                                <?= htmlspecialchars($oil['oil_name']) ?>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn-dispense" onclick="dispenseOils(this, <?= $s['session_id'] ?>)">Mark as Dispensed</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        btn.classList.add('active');
    }

    async function dispenseOils(btn, sessionId) {
        if (!confirm('Mark oils as dispensed for this session? This will deduct from inventory.')) return;
        btn.disabled = true;
        btn.textContent = 'Dispensing...';
        try {
            const fd = new FormData();
            fd.append('session_id', sessionId);
            const res = await fetch('/dheergayu/public/api/dispense-oils.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                alert('✅ ' + data.message);
                // Remove the session card from view
                btn.closest('.session-card').remove();
            } else {
                alert('❌ ' + data.error);
                btn.disabled = false;
                btn.textContent = 'Mark as Dispensed';
            }
        } catch (e) {
            alert('Error: ' + e.message);
            btn.disabled = false;
            btn.textContent = 'Mark as Dispensed';
        }
    }
</script>
</body>
</html>
