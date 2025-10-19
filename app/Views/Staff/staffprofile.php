<?php
// Example data – in real case, fetch from database
$staff = [
    'name' => 'M.H.Gunarathne',
    'age' => 30,
    'email' => 'staffdheergayu@gmail.com',
    'contact' => '+94 76 566 9333',
    'address' => '44, Gonahena Road, Kadawatha, Sri Lanka',
    'gender' => 'Female'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/staffprofile.css">
</head>
<body>
    <main class="profile-container">
        <!-- Close button -->
        <a href="staffhome.php" class="btn-back">&times;</a>

        <h1 class="profile-title">My Profile</h1>
        
        <!-- Profile Icon -->
        <div class="profile-icon-container">
            <img src="/dheergayu/public/assets/images/Staff/profileicon.jpg" alt="Profile Icon" class="profile-icon">
        </div>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo $staff['name']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Age:</span>
                <span class="value"><?php echo $staff['age']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo $staff['email']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo $staff['contact']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Address:</span>
                <span class="value"><?php echo $staff['address']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Gender:</span>
                <span class="value"><?php echo $staff['gender']; ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="editstaffprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>
