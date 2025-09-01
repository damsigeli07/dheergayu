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
    <title>Pharmacist Profile</title>
    <link rel="stylesheet" href="css/adminprofile.css">
</head>
<body>
    <main class="profile-container">
        <!-- Close button -->
        <a href="admindashboard.php" class="btn-back">&times;</a>

        <h1 class="profile-title">My Profile</h1>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo $pharmacist['name']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Age:</span>
                <span class="value"><?php echo $pharmacist['age']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo $pharmacist['email']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo $pharmacist['contact']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Address:</span>
                <span class="value"><?php echo $pharmacist['address']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Gender:</span>
                <span class="value"><?php echo $pharmacist['gender']; ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="editadminprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>
