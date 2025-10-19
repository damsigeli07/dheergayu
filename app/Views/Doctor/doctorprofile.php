<?php
// Example data – in real case, fetch from database
$doctor = [
    'name' => 'G.B.D.Bandara',
    'age' => 35,
    'email' => 'doctordheergayu@gmail.com',
    'contact' => '+94 74 166 4838',
    'address' => '26, Samagi Road, Mahara, Sri Lanka',
    'gender' => 'Male'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Doctor/doctorprofile.css">
</head>
<body>
    <main class="profile-container">
        <!-- Close button -->
        <a href="doctordashboard.php" class="btn-back">&times;</a>

        <div class="profile-picture">
    <img src="/dheergayu/public/assets/images/Doctor/doctor-profile.jpg" alt="Doctor Profile">
</div>

        <h1 class="profile-title">My Profile</h1>
        
        <div class="profile-card">
            <div class="profile-item">
                <span class="label">Name:</span>
                <span class="value"><?php echo $doctor['name']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Age:</span>
                <span class="value"><?php echo $doctor['age']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Email:</span>
                <span class="value"><?php echo $doctor['email']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Contact No:</span>
                <span class="value"><?php echo $doctor['contact']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Address:</span>
                <span class="value"><?php echo $doctor['address']; ?></span>
            </div>
            <div class="profile-item">
                <span class="label">Gender:</span>
                <span class="value"><?php echo $doctor['gender']; ?></span>
            </div>
        </div>

        <div class="edit-btn-container">
            <a href="editdoctorprofile.php" class="btn-edit-profile">Edit Profile</a>
        </div>
    </main>
</body>
</html>
