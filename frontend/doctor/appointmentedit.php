<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Patient Details</title>
  <link rel="stylesheet" href="css/appointmentedit.css">
</head>
<body>
  <div class="container">
    <h1>Edit Patient Details</h1>

    <form class="edit-form">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="John Smith">
      </div>

      <div class="form-group">
        <label for="age">Age</label>
        <input type="number" id="age" name="age" value="42">
      </div>

      <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender">
          <option value="Male" selected>Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="contact">Contact Number</label>
        <input type="text" id="contact" name="contact" value="776547969">
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="john@gmail.com" >
      </div>

      
      <div class="actions">
        <button type="submit" class="btn">Save Changes</button>
        <button type="button" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</body>
</html>