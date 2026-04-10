<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$role = strtolower(trim($_GET['role'] ?? ''));
$allowed = ['staff', 'doctor', 'pharmacist', 'admin'];

if (!in_array($role, $allowed)) {
    echo json_encode(['email' => '']);
    exit;
}

// Admin has a fixed email pattern: admindheergayu@gmail.com, admindheergayu2@gmail.com, etc.
if ($role === 'admin') {
    $res = $conn->query("SELECT email FROM users WHERE role = 'admin' AND email LIKE 'admindheergayu%@gmail.com'");
    $max = 0;
    while ($row = $res->fetch_assoc()) {
        if (preg_match('/^admindheergayu(\d*)@gmail\.com$/', $row['email'], $m)) {
            $n = $m[1] === '' ? 1 : (int)$m[1];
            if ($n > $max) $max = $n;
        }
    }
    $next = $max === 0 ? 'admindheergayu2@gmail.com' : 'admindheergayu' . ($max + 1) . '@gmail.com';
    echo json_encode(['email' => $next]);
    exit;
}

// For staff, doctor, pharmacist: roleN@gmail.com
$stmt = $conn->prepare("SELECT email FROM users WHERE role = ? AND email LIKE ?");
$like = $role . '%@gmail.com';
$stmt->bind_param('ss', $role, $like);
$stmt->execute();
$result = $stmt->get_result();

$max = 0;
while ($row = $result->fetch_assoc()) {
    if (preg_match('/^' . preg_quote($role, '/') . '(\d+)@gmail\.com$/', $row['email'], $m)) {
        $n = (int)$m[1];
        if ($n > $max) $max = $n;
    }
}
$stmt->close();

echo json_encode(['email' => $role . ($max + 1) . '@gmail.com']);
