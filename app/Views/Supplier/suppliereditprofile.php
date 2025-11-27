<?php
// Example data – in real case, fetch from database
$supplier = [
    'name' => 'S. Fernando',
    'age' => 35,
    'email' => 'supplier1@gmail.com',
    'contact' => '+94 77 987 6543',
    'address' => '456 Supplier Street, Kandy, Sri Lanka',
    'gender' => 'Male'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/header.css">
    <script src="/dheergayu/public/assets/js/header.js"></script>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Supplier/suppliereditprofile.css">
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
            <a href="supplierhome.php" class="nav-btn">Home</a>
            <a href="supplierrequest.php" class="nav-btn">Request</a>
        </nav>
        
        <div class="user-section">
            <div class="user-icon" id="user-icon">👤</div>
            <span class="user-role">Supplier</span>
            <div class="user-dropdown" id="user-dropdown">
                <a href="supplierprofile.php" class="profile-btn">Profile</a>
                <a href="../patient/login.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="supplierprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <form class="edit-profile-form" onsubmit="showAlert(event)">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $supplier['name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $supplier['age']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $supplier['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $supplier['contact']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required><?php echo $supplier['address']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($supplier['gender']=='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo ($supplier['gender']=='Female')?'selected':''; ?>>Female</option>
                    <option value="Other" <?php echo ($supplier['gender']=='Other')?'selected':''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="supplierprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
