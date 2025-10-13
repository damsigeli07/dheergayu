c

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = "Active";
    $reg_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, role, email, phone, password, status, reg_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first, $last, $role, $email, $phone, $password, $status, $reg_date);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully!'); window.location.href='../../frontend/admin/adminusers.php';</script>";
    } else {
        echo "<script>alert('Error adding user.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../frontend/admin/adminaddnewuser.php");
    exit;
}
?>
