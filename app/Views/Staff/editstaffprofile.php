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
    <title>Edit Staff Profile</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Staff/editstaffprofile.css">
    <script>
        function showAlert(event) {
            event.preventDefault();
            alert("Changes saved!");
        }
    </script>
</head>
<body>
    <main class="edit-profile-container">
        <!-- Close button -->
        <a href="staffprofile.php" class="btn-back">&times;</a>

        <h1 class="edit-profile-title">Edit Profile</h1>
        
        <form class="edit-profile-form" onsubmit="showAlert(event)">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $staff['name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo $staff['age']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $staff['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact No:</label>
                <input type="text" id="contact" name="contact" value="<?php echo $staff['contact']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required><?php echo $staff['address']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($staff['gender']=='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo ($staff['gender']=='Female')?'selected':''; ?>>Female</option>
                    <option value="Other" <?php echo ($staff['gender']=='Other')?'selected':''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="staffprofile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
