<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/db_connect.php'); 
include('includes/header.php');
echo "<!-- header and db_connect loaded successfully -->";

?>






<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="css/s_register.css" rel="stylesheet"> <!-- Using same CSS -->

<style>
body {
    background: url('img/s_login.jpg') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card {
    background-color: rgba(255, 255, 255, 0.65);
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
    <div class="col-md-6">
      <div class="card shadow-lg">
        <div class="card-body p-5">
          <h3 class="text-center mb-4">
            <span class="material-icons" style="font-size: 32px;">school</span>
            <span class="ms-2">Student Login</span>
          </h3>

          <form action="s_login_process.php" method="POST" autocomplete="off">
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
            </div>
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-success">Login</button>
            </div>
            <p class="text-center">
              Don’t have an account?
              <a href="place.php" class="text-decoration-none fw-bold text-primary">Register here</a>
            </p>
            <p class="text-center mt-2">
              <a href="../index.php" class="text-decoration-none">← Back to Home</a>
            </p>

          </form>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>
