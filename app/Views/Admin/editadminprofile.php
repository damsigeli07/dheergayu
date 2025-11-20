<?php
// Example data – in real case, fetch from database
$pharmacist = [
    'name' => 'D.Gunasekara',
    'age' => 28,
    'email' => 'admindheergayu@gmail.com',
    'contact' => '+94 77 342 4567',
    'address' => '23, Deans Road, Colombo, Sri Lanka',
    'gender' => 'Male'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pharmacist Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Admin/editadminprofile.css">
    <script>
        function showAlert(event) {
            event.preventDefault();
            alert("Changes saved!");
        }
    </script>
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
            <div class="user-dropdown" id="user-dropdown">
                <a href="adminprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="adminprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <form class="edit-profile-form" onsubmit="showAlert(event)">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $pharmacist['name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $pharmacist['age']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $pharmacist['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $pharmacist['contact']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required><?php echo $pharmacist['address']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($pharmacist['gender']=='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo ($pharmacist['gender']=='Female')?'selected':''; ?>>Female</option>
                    <option value="Other" <?php echo ($pharmacist['gender']=='Other')?'selected':''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="adminprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
