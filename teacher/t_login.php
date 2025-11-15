<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['teacher_id'])) {
    header("Location: t_dashboard.php");
    exit;
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/header.php');
include('includes/db_connect.php');

$error = "";
$success = "";

// Get success message from registration
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $reg_no = trim($_POST['teacher_id']);
  $password = $_POST['password'];

  // Prepare the query to fetch the teacher by registration number
  $stmt = $conn->prepare("SELECT * FROM teacher WHERE reg_no = ?");
  $stmt->bind_param("s", $reg_no);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $teacher = $result->fetch_assoc();

    // Verify the password (use password_verify if using hashed passwords)
    if ($password === $teacher['password']) {
      // Set the session for teacher login
      $_SESSION['teacher_id'] = $teacher['id'];
      $_SESSION['teacher_name'] = $teacher['name'];
      
      // Store success message
      $_SESSION['login_success'] = "Login successful! Welcome back, " . $teacher['name'];
      
      // Redirect to the teacher's dashboard
      header("Location: t_dashboard.php");
      exit;
    } else {
      $error = "Invalid password. Please try again.";
    }
  } else {
    $error = "Teacher ID not found. Please check your ID or register.";
  }
  
  $stmt->close();
}
?>

<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="css/log_style.css" rel="stylesheet">

<section class="login-page d-flex justify-content-center align-items-center">
  <div class="card login-card shadow-lg">
    <div class="card-body p-5">
      <h3 class="text-center mb-4 text-white">
        <span class="material-icons text-white">person</span>
        Teacher Login
      </h3>
      
      <!-- Show success message -->
      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="material-icons" style="font-size: 20px; vertical-align: middle;">check_circle</i>
          <strong><?= htmlspecialchars($success) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Show error message -->
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="material-icons" style="font-size: 20px; vertical-align: middle;">error</i>
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="t_login.php" autocomplete="off">
        <div class="mb-3">
          <label for="teacher_id" class="form-label text-white">Teacher ID</label>
          <input type="text" class="form-control" id="teacher_id" name="teacher_id" required placeholder="Enter your Teacher ID" value="">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label text-white">Password</label>
          <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password" value="">
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-success">Login</button>
        </div>
        <p class="text-center text-white">
          Don't have an account?
          <a href="place.php" class="text-decoration-none fw-bold text-warning">Register here</a>
        </p>
        <p class="text-center mt-2">
          <a href="../index.php" class="text-decoration-none text-light">← Back to Home</a>
        </p>
      </form>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Clear form on page load
window.onload = function() {
  document.getElementById('teacher_id').value = '';
  document.getElementById('password').value = '';
  
  // Prevent form resubmission
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
};

// Clear form on back button
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    document.getElementById('teacher_id').value = '';
    document.getElementById('password').value = '';
  }
});
</script>

</body>
</html>