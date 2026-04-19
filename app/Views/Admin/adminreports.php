<?php
require_once __DIR__ . '/../../includes/auth_admin.php';
require_once __DIR__ . '/../../../config/config.php';

$today = date('Y-m-d');
$fromDate = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$toDate   = isset($_GET['to'])   ? $_GET['to']   : $today;

$from = $conn->real_escape_string($fromDate);
$to   = $conn->real_escape_string($toDate);

// ── APPOINTMENT REPORT ────────────────────────────────────────────────────────
$apptTotal = $conn->query("SELECT COUNT(*) AS c FROM consultations WHERE appointment_date BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
$apptCompleted = $conn->query("SELECT COUNT(*) AS c FROM consultations WHERE appointment_date BETWEEN '$from' AND '$to' AND status='Completed'")->fetch_assoc()['c'];
$apptCancelled = $conn->query("SELECT COUNT(*) AS c FROM consultations WHERE appointment_date BETWEEN '$from' AND '$to' AND status='Cancelled'")->fetch_assoc()['c'];
$apptPending   = $conn->query("SELECT COUNT(*) AS c FROM consultations WHERE appointment_date BETWEEN '$from' AND '$to' AND status IN ('Pending','Confirmed')")->fetch_assoc()['c'];

$apptByDoctorResult = $conn->query("
    SELECT doctor_name, COUNT(*) AS total,
           SUM(status='Completed') AS completed,
           SUM(status='Cancelled') AS cancelled
    FROM consultations
    WHERE appointment_date BETWEEN '$from' AND '$to'
    GROUP BY doctor_name ORDER BY total DESC
");
$apptByDoctor = [];
while ($r = $apptByDoctorResult->fetch_assoc()) $apptByDoctor[] = $r;

// ── TREATMENT REPORT ──────────────────────────────────────────────────────────
$tpTotal     = $conn->query("SELECT COUNT(*) AS c FROM treatment_plans WHERE DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
$tpCompleted = $conn->query("SELECT COUNT(*) AS c FROM treatment_plans WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='Completed'")->fetch_assoc()['c'];
$tpInProgress= $conn->query("SELECT COUNT(*) AS c FROM treatment_plans WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='InProgress'")->fetch_assoc()['c'];
$tpCancelled = $conn->query("SELECT COUNT(*) AS c FROM treatment_plans WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='Cancelled'")->fetch_assoc()['c'];

$tpByTypeResult = $conn->query("
    SELECT treatment_name, COUNT(*) AS total,
           SUM(status='Completed') AS completed,
           COALESCE(SUM(total_cost),0) AS revenue
    FROM treatment_plans
    WHERE DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY treatment_name ORDER BY total DESC
");
$tpByType = [];
while ($r = $tpByTypeResult->fetch_assoc()) $tpByType[] = $r;

$sessTotal     = $conn->query("SELECT COUNT(*) AS c FROM treatment_sessions WHERE DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
$sessCompleted = $conn->query("SELECT COUNT(*) AS c FROM treatment_sessions WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='Completed'")->fetch_assoc()['c'];

// ── FINANCIAL REPORT ──────────────────────────────────────────────────────────
$finTotal    = $conn->query("SELECT COALESCE(SUM(amount),0) AS t FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid'")->fetch_assoc()['t'];
$finAppt     = $conn->query("SELECT COALESCE(SUM(amount),0) AS t FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid' AND (order_items LIKE 'Consultation #%' OR order_items LIKE 'Treatment #%')")->fetch_assoc()['t'];
$finTreat    = $conn->query("SELECT COALESCE(SUM(amount),0) AS t FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid' AND order_items LIKE 'Treatment Plan #%'")->fetch_assoc()['t'];
$finProduct  = $conn->query("SELECT COALESCE(SUM(amount),0) AS t FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid' AND order_items NOT LIKE 'Consultation #%' AND order_items NOT LIKE 'Treatment #%' AND order_items NOT LIKE 'Treatment Plan #%' AND order_items NOT LIKE 'Dispensed:%'")->fetch_assoc()['t'];

$finByDayResult = $conn->query("
    SELECT DATE(created_at) AS day, COALESCE(SUM(amount),0) AS total
    FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid'
    GROUP BY DATE(created_at) ORDER BY day ASC
");
$finByDay = [];
while ($r = $finByDayResult->fetch_assoc()) $finByDay[] = $r;

$finByMethodResult = $conn->query("
    SELECT payment_method, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total
    FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='paid'
    GROUP BY payment_method
");
$finByMethod = [];
while ($r = $finByMethodResult->fetch_assoc()) $finByMethod[] = $r;

// ── INVENTORY REPORT ──────────────────────────────────────────────────────────
$invTotal      = $conn->query("SELECT COUNT(DISTINCT product_id) AS c FROM products")->fetch_assoc()['c'];
$invLowStock   = $conn->query("SELECT COUNT(DISTINCT product_id) AS c FROM batches WHERE quantity < 5 AND quantity >= 0")->fetch_assoc()['c'];
$invExpired    = $conn->query("SELECT COUNT(*) AS c FROM batches WHERE exp < '$today'")->fetch_assoc()['c'];
$invExpiringSoon=$conn->query("SELECT COUNT(*) AS c FROM batches WHERE exp BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 30 DAY)")->fetch_assoc()['c'];

$invLowResult = $conn->query("
    SELECT p.name, SUM(b.quantity) AS total_qty, MIN(b.exp) AS nearest_expiry
    FROM products p JOIN batches b ON p.product_id=b.product_id
    WHERE b.quantity < 5 AND b.quantity >= 0
    GROUP BY p.product_id, p.name ORDER BY total_qty ASC LIMIT 20
");
$invLowItems = [];
while ($r = $invLowResult->fetch_assoc()) $invLowItems[] = $r;

$invExpiringResult = $conn->query("
    SELECT p.name, b.batch_number, b.quantity, b.exp AS expiry_date,
           DATEDIFF(b.exp, '$today') AS days_left
    FROM products p JOIN batches b ON p.product_id=b.product_id
    WHERE b.exp BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 30 DAY)
    ORDER BY b.exp ASC LIMIT 20
");
$invExpiring = [];
while ($r = $invExpiringResult->fetch_assoc()) $invExpiring[] = $r;

// ── PATIENT REPORT ────────────────────────────────────────────────────────────
$patTotal   = $conn->query("SELECT COUNT(*) AS c FROM patients")->fetch_assoc()['c'];
$patNew     = $conn->query("SELECT COUNT(*) AS c FROM patients WHERE DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];

$patByMonthResult = $conn->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS mon, COUNT(*) AS cnt
    FROM patients GROUP BY mon ORDER BY mon DESC LIMIT 6
");
$patByMonth = [];
while ($r = $patByMonthResult->fetch_assoc()) $patByMonth[] = $r;

// ── CONTACT/SUPPORT REPORT ────────────────────────────────────────────────────
$ctTotal    = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE DATE(created_at) BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
$ctNew      = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='new'")->fetch_assoc()['c'];
$ctReplied  = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status='replied'")->fetch_assoc()['c'];
$ctResolved = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND (status='resolved' OR status='archived')")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f7f5f2; }
        body.has-sidebar { margin-left: 260px; }

        .main-content { padding: 30px; max-width: 1400px; margin: 0 auto; }

        .page-header { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; }
        .page-title { color: #8B7355; font-size: 2rem; font-weight: 600; }
        .page-subtitle { color: #666; font-size: 1rem; margin-top: 4px; }

        /* Date filter */
        .filter-bar {
            background: #fff;
            border-radius: 12px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 2px 8px rgba(0,0,0,.07);
            margin-bottom: 30px;
        }
        .filter-bar label { font-weight: 500; color: #555; font-size: .9rem; }
        .filter-bar input[type=date] {
            border: 1px solid #ddd; border-radius: 8px;
            padding: 8px 12px; font-size: .9rem; color: #333;
            background: #fafafa;
        }
        .filter-btn {
            background: #8B7355; color: #fff; border: none;
            border-radius: 8px; padding: 9px 22px;
            font-size: .9rem; font-weight: 500; cursor: pointer;
            transition: background .2s;
        }
        .filter-btn:hover { background: #7a6348; }
        .print-btn {
            background: #4CAF50; color: #fff; border: none;
            border-radius: 8px; padding: 9px 22px;
            font-size: .9rem; font-weight: 500; cursor: pointer;
            margin-left: auto;
        }
        .print-btn:hover { background: #43a047; }

        /* Section cards */
        .report-section {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            margin-bottom: 30px;
        }
        .section-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 14px;
            border-bottom: 2px solid #f0ebe3;
        }
        .section-title {
            font-size: 1.25rem; font-weight: 600; color: #8B7355;
            display: flex; align-items: center; gap: 10px;
        }
        .section-title i { font-size: 1.1rem; }
        .section-print {
            background: none; border: 1px solid #8B7355; color: #8B7355;
            border-radius: 6px; padding: 5px 14px; font-size: .82rem;
            cursor: pointer; transition: all .2s;
        }
        .section-print:hover { background: #8B7355; color: #fff; }

        /* Summary stat cards */
        .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat-box {
            background: #f9f6f2; border-radius: 10px; padding: 16px 18px;
            border-left: 4px solid #8B7355; text-align: center;
        }
        .stat-box.green  { border-color: #4CAF50; }
        .stat-box.red    { border-color: #e53935; }
        .stat-box.blue   { border-color: #1976D2; }
        .stat-box.orange { border-color: #FF9800; }
        .stat-box .val { font-size: 1.7rem; font-weight: 700; color: #333; }
        .stat-box .lbl { font-size: .78rem; color: #777; margin-top: 4px; }

        /* Tables */
        .report-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .report-table th {
            background: #f0ebe3; color: #8B7355; font-weight: 600;
            padding: 10px 14px; text-align: left; border-bottom: 2px solid #e0d5c5;
        }
        .report-table td { padding: 9px 14px; border-bottom: 1px solid #f0ebe3; color: #444; }
        .report-table tr:last-child td { border-bottom: none; }
        .report-table tr:hover td { background: #faf8f5; }

        .badge {
            display: inline-block; padding: 2px 10px; border-radius: 12px;
            font-size: .75rem; font-weight: 500;
        }
        .badge-green  { background: #e8f5e9; color: #2e7d32; }
        .badge-red    { background: #ffebee; color: #c62828; }
        .badge-orange { background: #fff3e0; color: #e65100; }
        .badge-blue   { background: #e3f2fd; color: #1565c0; }
        .badge-grey   { background: #f5f5f5; color: #616161; }

        .empty-msg { color: #aaa; font-style: italic; padding: 16px 0; text-align: center; }

        /* Divider label */
        .sub-label { font-size: .82rem; font-weight: 600; color: #8B7355; text-transform: uppercase; letter-spacing: .5px; margin: 20px 0 10px; }

        @media print {
            body.has-sidebar { margin-left: 0; }
            .header, .filter-bar, .section-print, .print-btn, .filter-btn { display: none !important; }
            .report-section { box-shadow: none; break-inside: avoid; }
            .main-content { padding: 10px; }
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
        <a href="admindashboard.php"      class="nav-btn">Home</a>
        <a href="admininventory.php"      class="nav-btn">Products</a>
        <a href="admininventoryview.php"  class="nav-btn">Inventory</a>
        <a href="adminappointment.php"    class="nav-btn">Appointments</a>
        <a href="adminusers.php"          class="nav-btn">Users</a>
        <a href="adminpatients.php"       class="nav-btn">Patients</a>
        <a href="admintreatment.php"      class="nav-btn">Treatments</a>
        <a href="adminpayments.php"       class="nav-btn">Payments</a>
        <a href="adminsuppliers.php"      class="nav-btn">Supplier-info</a>
        <a href="admincontact.php"        class="nav-btn">Contact Submissions</a>
        <button class="nav-btn active">Reports</button>
    </nav>
    <div class="user-section">
        <div class="user-icon" id="user-icon">👤</div>
        <span class="user-role">Admin</span>
        <div class="user-dropdown" id="user-dropdown">
            <a href="adminprofile.php"                   class="profile-btn">Profile</a>
            <a href="/dheergayu/app/Views/logout.php"    class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>
</header>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Reports</h1>
            <p class="page-subtitle">Comprehensive overview across all system modules</p>
        </div>
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print All Reports</button>
    </div>

    <!-- Date Filter -->
    <form class="filter-bar" method="GET">
        <label>From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>">
        <label>To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>">
        <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Apply Filter</button>
        <a href="adminreports.php" style="color:#8B7355;font-size:.85rem;text-decoration:none;">Reset</a>
    </form>

    <!-- ── APPOINTMENT REPORT ──────────────────────────────────────────── -->
    <div class="report-section" id="section-appointments">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-calendar-check"></i> Appointment Report</div>
            <button class="section-print" onclick="printSection('section-appointments','Appointment Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="val"><?= $apptTotal ?></div>
                <div class="lbl">Total Appointments</div>
            </div>
            <div class="stat-box green">
                <div class="val"><?= $apptCompleted ?></div>
                <div class="lbl">Completed</div>
            </div>
            <div class="stat-box blue">
                <div class="val"><?= $apptPending ?></div>
                <div class="lbl">Pending / Confirmed</div>
            </div>
            <div class="stat-box red">
                <div class="val"><?= $apptCancelled ?></div>
                <div class="lbl">Cancelled</div>
            </div>
            <div class="stat-box orange">
                <div class="val"><?= $apptTotal > 0 ? round(($apptCompleted/$apptTotal)*100) : 0 ?>%</div>
                <div class="lbl">Completion Rate</div>
            </div>
        </div>

        <div class="sub-label">Appointments by Doctor</div>
        <?php if (count($apptByDoctor) > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Cancelled</th>
                    <th>Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apptByDoctor as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['doctor_name'] ?? 'N/A') ?></td>
                    <td><?= $row['total'] ?></td>
                    <td><span class="badge badge-green"><?= $row['completed'] ?></span></td>
                    <td><span class="badge badge-red"><?= $row['cancelled'] ?></span></td>
                    <td><?= $row['total'] > 0 ? round(($row['completed']/$row['total'])*100) : 0 ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="empty-msg">No appointments found for the selected period.</p>
        <?php endif; ?>
    </div>

    <!-- ── TREATMENT REPORT ───────────────────────────────────────────── -->
    <div class="report-section" id="section-treatments">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-spa"></i> Treatment Report</div>
            <button class="section-print" onclick="printSection('section-treatments','Treatment Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="val"><?= $tpTotal ?></div>
                <div class="lbl">Treatment Plans Created</div>
            </div>
            <div class="stat-box green">
                <div class="val"><?= $tpCompleted ?></div>
                <div class="lbl">Completed Plans</div>
            </div>
            <div class="stat-box blue">
                <div class="val"><?= $tpInProgress ?></div>
                <div class="lbl">In Progress</div>
            </div>
            <div class="stat-box red">
                <div class="val"><?= $tpCancelled ?></div>
                <div class="lbl">Cancelled</div>
            </div>
            <div class="stat-box orange">
                <div class="val"><?= $sessCompleted ?> / <?= $sessTotal ?></div>
                <div class="lbl">Sessions Completed</div>
            </div>
        </div>

        <div class="sub-label">Breakdown by Treatment Type</div>
        <?php if (count($tpByType) > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Treatment Type</th>
                    <th>Plans</th>
                    <th>Completed</th>
                    <th>Revenue (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tpByType as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['treatment_name'] ?? 'N/A') ?></td>
                    <td><?= $row['total'] ?></td>
                    <td><span class="badge badge-green"><?= $row['completed'] ?></span></td>
                    <td><?= number_format($row['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="empty-msg">No treatment plans found for the selected period.</p>
        <?php endif; ?>
    </div>

    <!-- ── FINANCIAL REPORT ───────────────────────────────────────────── -->
    <div class="report-section" id="section-financial">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-rupee-sign"></i> Financial Report</div>
            <button class="section-print" onclick="printSection('section-financial','Financial Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box green">
                <div class="val">Rs. <?= number_format($finTotal, 2) ?></div>
                <div class="lbl">Total Revenue (Paid)</div>
            </div>
            <div class="stat-box blue">
                <div class="val">Rs. <?= number_format($finAppt, 2) ?></div>
                <div class="lbl">Consultation Revenue</div>
            </div>
            <div class="stat-box orange">
                <div class="val">Rs. <?= number_format($finTreat, 2) ?></div>
                <div class="lbl">Treatment Plan Revenue</div>
            </div>
            <div class="stat-box">
                <div class="val">Rs. <?= number_format($finProduct, 2) ?></div>
                <div class="lbl">Product Sales Revenue</div>
            </div>
        </div>

        <?php if (count($finByMethod) > 0): ?>
        <div class="sub-label">Revenue by Payment Method</div>
        <table class="report-table">
            <thead>
                <tr><th>Payment Method</th><th>Transactions</th><th>Total (Rs.)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($finByMethod as $row): ?>
                <tr>
                    <td><?= htmlspecialchars(ucfirst($row['payment_method'] ?? 'N/A')) ?></td>
                    <td><?= $row['cnt'] ?></td>
                    <td><?= number_format($row['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (count($finByDay) > 0): ?>
        <div class="sub-label">Daily Revenue Breakdown</div>
        <table class="report-table">
            <thead>
                <tr><th>Date</th><th>Revenue (Rs.)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($finByDay as $row): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['day'])) ?></td>
                    <td><?= number_format($row['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="empty-msg">No paid transactions found for the selected period.</p>
        <?php endif; ?>
    </div>

    <!-- ── INVENTORY REPORT ───────────────────────────────────────────── -->
    <div class="report-section" id="section-inventory">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-boxes"></i> Inventory Report</div>
            <button class="section-print" onclick="printSection('section-inventory','Inventory Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="val"><?= $invTotal ?></div>
                <div class="lbl">Total Products</div>
            </div>
            <div class="stat-box red">
                <div class="val"><?= $invLowStock ?></div>
                <div class="lbl">Low Stock Products (&lt;5 units)</div>
            </div>
            <div class="stat-box orange">
                <div class="val"><?= $invExpiringSoon ?></div>
                <div class="lbl">Expiring Within 30 Days</div>
            </div>
            <div class="stat-box red">
                <div class="val"><?= $invExpired ?></div>
                <div class="lbl">Expired Batches</div>
            </div>
        </div>

        <?php if (count($invLowItems) > 0): ?>
        <div class="sub-label">Low Stock Products</div>
        <table class="report-table">
            <thead>
                <tr><th>Product</th><th>Available Qty</th><th>Nearest Expiry</th></tr>
            </thead>
            <tbody>
                <?php foreach ($invLowItems as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><span class="badge badge-red"><?= $row['total_qty'] ?></span></td>
                    <td><?= $row['nearest_expiry'] ? date('d M Y', strtotime($row['nearest_expiry'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (count($invExpiring) > 0): ?>
        <div class="sub-label">Batches Expiring Within 30 Days</div>
        <table class="report-table">
            <thead>
                <tr><th>Product</th><th>Batch #</th><th>Quantity</th><th>Expiry Date</th><th>Days Left</th></tr>
            </thead>
            <tbody>
                <?php foreach ($invExpiring as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['batch_number']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= date('d M Y', strtotime($row['expiry_date'])) ?></td>
                    <td>
                        <span class="badge <?= $row['days_left'] <= 7 ? 'badge-red' : 'badge-orange' ?>">
                            <?= $row['days_left'] ?> days
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (count($invLowItems) === 0 && count($invExpiring) === 0): ?>
        <p class="empty-msg">No inventory alerts at this time.</p>
        <?php endif; ?>
    </div>

    <!-- ── PATIENT REPORT ─────────────────────────────────────────────── -->
    <div class="report-section" id="section-patients">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-users"></i> Patient Report</div>
            <button class="section-print" onclick="printSection('section-patients','Patient Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="val"><?= $patTotal ?></div>
                <div class="lbl">Total Registered Patients</div>
            </div>
            <div class="stat-box green">
                <div class="val"><?= $patNew ?></div>
                <div class="lbl">New Registrations (Period)</div>
            </div>
        </div>

        <?php if (count($patByMonth) > 0): ?>
        <div class="sub-label">Registrations by Month (Last 6 Months)</div>
        <table class="report-table">
            <thead>
                <tr><th>Month</th><th>New Patients</th></tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($patByMonth) as $row): ?>
                <tr>
                    <td><?= date('F Y', strtotime($row['mon'] . '-01')) ?></td>
                    <td><?= $row['cnt'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ── CONTACT / SUPPORT REPORT ──────────────────────────────────── -->
    <div class="report-section" id="section-contact">
        <div class="section-header">
            <div class="section-title"><i class="fas fa-envelope"></i> Contact &amp; Support Report</div>
            <button class="section-print" onclick="printSection('section-contact','Contact & Support Report')">Print</button>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="val"><?= $ctTotal ?></div>
                <div class="lbl">Total Inquiries</div>
            </div>
            <div class="stat-box orange">
                <div class="val"><?= $ctNew ?></div>
                <div class="lbl">New / Unread</div>
            </div>
            <div class="stat-box blue">
                <div class="val"><?= $ctReplied ?></div>
                <div class="lbl">Replied</div>
            </div>
            <div class="stat-box green">
                <div class="val"><?= $ctResolved ?></div>
                <div class="lbl">Resolved / Archived</div>
            </div>
            <div class="stat-box">
                <div class="val"><?= $ctTotal > 0 ? round((($ctReplied + $ctResolved) / $ctTotal) * 100) : 0 ?>%</div>
                <div class="lbl">Response Rate</div>
            </div>
        </div>
    </div>

</div><!-- /.main-content -->

<script>
function printSection(sectionId, title) {
    const section = document.getElementById(sectionId);
    const win = window.open('', '_blank', 'width=900,height=700');
    win.document.write(`
        <html><head>
        <title>${title}</title>
        <style>
            body { font-family: Roboto, sans-serif; padding: 24px; color: #333; }
            h2 { color: #8B7355; margin-bottom: 20px; }
            .stat-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
            .stat-box { border-left: 4px solid #8B7355; padding: 12px 16px; background: #f9f6f2; border-radius: 8px; min-width: 130px; }
            .stat-box .val { font-size: 1.5rem; font-weight: 700; }
            .stat-box .lbl { font-size: .75rem; color: #777; }
            table { width: 100%; border-collapse: collapse; font-size: .875rem; }
            th { background: #f0ebe3; color: #8B7355; padding: 10px 14px; text-align: left; }
            td { padding: 9px 14px; border-bottom: 1px solid #f0ebe3; }
            .sub-label { font-size: .82rem; font-weight: 600; color: #8B7355; text-transform: uppercase; margin: 18px 0 8px; }
            .section-header, .section-print { display: none; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: .75rem; }
            .badge-green { background: #e8f5e9; color: #2e7d32; }
            .badge-red { background: #ffebee; color: #c62828; }
            .badge-orange { background: #fff3e0; color: #e65100; }
            .print-date { color: #999; font-size: .8rem; margin-bottom: 16px; }
        </style>
        </head><body>
        <h2>${title}</h2>
        <p class="print-date">Generated: ${new Date().toLocaleDateString('en-GB', {day:'2-digit',month:'long',year:'numeric'})}</p>
        ${section.innerHTML}
        </body></html>
    `);
    win.document.close();
    win.focus();
    win.print();
}
</script>

</body>
</html>
