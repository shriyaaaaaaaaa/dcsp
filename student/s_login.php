<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: s_dashboard.php");
    exit;
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/db_connect.php');
include('includes/header.php');

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (empty($email) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    // Prepare the query to fetch user by email
    $stmt = $conn->prepare("SELECT id, name, email, password FROM student WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows == 1) {
      $student = $result->fetch_assoc();

      // Verify the password (plain text comparison - consider using password_hash/password_verify)
      if ($password === $student['password']) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_email'] = $student['email'];
        
        // Store success message in session
        $_SESSION['login_success'] = "Login successful! Welcome back, " . $student['name'];
        
        // Redirect after login to clear form data (Post/Redirect/Get pattern)
        header("Location: s_dashboard.php");
        exit;
      } else {
        $error = "Invalid password. Please try again.";
      }
    } else {
      $error = "Student email not found. Please check your email or register.";
    }
    
    $stmt->close();
  }
}
?>

<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="css/s_register.css" rel="stylesheet">

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-lg">
        <div class="card-body p-5">
          <h3 class="text-center mb-4">
            <span class="material-icons" style="font-size: 32px;">school</span>
            <span class="ms-2">Student Login</span>
          </h3>

          <!-- Show error message as Bootstrap alert -->
          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="material-icons" style="font-size: 18px; vertical-align: middle;">error</i>
              <?php echo htmlspecialchars($error); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <!-- Show success message as Bootstrap alert (if redirected back for some reason) -->
          <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="material-icons" style="font-size: 18px; vertical-align: middle;">check_circle</i>
              <?php echo htmlspecialchars($success); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form method="POST" action="s_login.php" autocomplete="off">
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input 
                type="email" 
                name="email" 
                id="email" 
                class="form-control" 
                required 
                placeholder="Enter your email"
                value="">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control" 
                required 
                placeholder="Enter your password"
                value="">
            </div>
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-success">Login</button>
            </div>
            <p class="text-center">
              Don't have an account?
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Clear form on page load to prevent password from showing when going back
window.onload = function() {
  document.getElementById('email').value = '';
  document.getElementById('password').value = '';
};

// Alternative: Clear form on back button
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
  }
});
</script>

</body>
</html>