<?php
include('includes/header.php');
include('includes/db_connect.php');

if (!isset($_GET['org_id'])) {
    echo "<script>alert('Please select an organization first.'); window.location.href='place.php';</script>";
    exit;
}
$org_id = $_GET['org_id'];
?>

<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Custom CSS -->


<style>
body {
    background: url('img/s_register.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
}

.card {
    background-color: rgba(255, 255, 255, 0.65); /* 35% transparent */
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    border: none;
}

h3 {
    font-weight: 600;
    color: #2e7d32;
}

label {
    font-weight: 500;
}

.btn-success {
    background-color: #28a745;
    border: none;
    font-weight: bold;
}

.btn-success:hover {
    background-color: #218838;
}

input.form-control {
    border-radius: 10px;
}

.text-center a {
    color: #2c3e50;
}

.text-center a:hover {
    color: #0069d9;
    text-decoration: underline;
}
</style>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card shadow-lg">
        <div class="card-body p-5">
          <h3 class="text-center mb-4">
            <span class="material-icons" style="font-size: 32px; vertical-align: middle;">how_to_reg</span>
            <span class="ms-2">Student Registration</span>
          </h3>
          <form action="s_register_process.php" method="POST">
            <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
            <div class="mb-3">
              <label for="reg_no" class="form-label">Registration Number</label>
              <input type="text" name="reg_no" id="reg_no" class="form-control" required placeholder="Enter your student registration number">
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" name="name" id="name" class="form-control" required placeholder="Enter your full name">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email address">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" required placeholder="Create a password">
            </div>
            <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Re-enter your password">
            </div>
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-success">Register</button>
            </div>
            <p class="text-center">Already registered? <a href="s_login.php">Login here</a></p>
            <p class="text-center mt-2"><a href="../index.php">← Back to Home</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>

