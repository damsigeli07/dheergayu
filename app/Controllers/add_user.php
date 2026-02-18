<?php
require_once(__DIR__ . "/../../config/config.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = "Active";
    $reg_date = date('Y-m-d');

    // Ensure users.id auto-increments so new rows get the next id (not 0)
    while (true) {
        $max = $conn->query("SELECT COALESCE(MAX(id), 0) AS m FROM users");
        $m = $max && ($row = $max->fetch_assoc()) ? (int)$row['m'] : 0;
        $conn->query("UPDATE users SET id = " . ($m + 1) . " WHERE id = 0 LIMIT 1");
        if (!$conn->affected_rows) break;
    }
    @$conn->query("ALTER TABLE users MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT");

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, role, email, phone, password, status, reg_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first, $last, $role, $email, $phone, $password, $status, $reg_date);

    if ($stmt->execute()) {
        $user_id = (int) $conn->insert_id;
        if ($user_id <= 0) {
            $get = $conn->prepare("SELECT id FROM users WHERE email = ? ORDER BY id DESC LIMIT 1");
            $get->bind_param("s", $email);
            $get->execute();
            $r = $get->get_result()->fetch_assoc();
            $get->close();
            $user_id = $r ? (int) $r['id'] : 0;
        }
        $stmt->close();

        // Ensure staff_info table exists (in case migration not run)
        $conn->query("CREATE TABLE IF NOT EXISTS staff_info (
            user_id INT NOT NULL PRIMARY KEY,
            age INT NULL,
            address VARCHAR(500) NULL,
            gender VARCHAR(20) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_staff_info_user (user_id)
        )");

        // When role is Staff, insert a row in staff_info so profile has a record (user_id must not be 0)
        if ($user_id > 0 && strtolower($role) === 'staff') {
            $ins = $conn->prepare("INSERT INTO staff_info (user_id, age, address, gender) VALUES (?, NULL, '', NULL) ON DUPLICATE KEY UPDATE user_id = user_id");
            $ins->bind_param("i", $user_id);
            $ins->execute();
            $ins->close();
        }

        $conn->close();
        echo "<script>alert('User added successfully!'); window.location.href='/dheergayu/app/Views/Admin/adminusers.php';</script>";
    } else {
        echo "<script>alert('Error adding user.'); window.history.back();</script>";
        $stmt->close();
        $conn->close();
    }
} else {
    header("Location: /dheergayu/app/Views/Admin/adminaddnewuser.php");
    exit;
}
?>
