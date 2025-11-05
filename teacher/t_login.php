<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['teacher_id'])) {
    header("Location: t_dashboard.php");
    exit;
}

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/header.php');
include('includes/db_connect.php');

$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $reg_no = $_POST['teacher_id'];
  $password = $_POST['password'];

  // Prepare the query to fetch the teacher by registration number
  $stmt = $conn->prepare("SELECT * FROM teacher WHERE reg_no = ?");
  $stmt->bind_param("s", $reg_no);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $teacher = $result->fetch_assoc();

    // Verify the password (use password_verify if using hashed passwords)
    if ($password == $teacher['password']) {
      // Set the session for teacher login
      $_SESSION['teacher_id'] = $teacher['reg_no'];
      
      // Redirect to the teacher's dashboard
      header("Location: t_dashboard.php");
      exit;
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "Teacher ID not found.";
  }
  
  $stmt->close();
}
?>

<!-- Rest of your HTML code remains the same -->

<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="css/log_style.css" rel="stylesheet"> <!-- Add your custom styles if needed -->

<section class="login-page d-flex justify-content-center align-items-center">
  <div class="card login-card shadow-lg">
    <div class="card-body p-5">
      <h3 class="text-center mb-4 text-white">
        <span class="material-icons text-white">person</span>
        Teacher Login
      </h3>
      
      <!-- Show error message as alert if there is an error -->
      <?php if ($error): ?>
        <div class="alert alert-danger text-center" id="error-alert">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="t_login.php" autocomplete="off">
        <div class="mb-3">
          <label for="teacher_id" class="form-label text-white">Teacher ID</label>
          <input type="text" class="form-control" id="teacher_id" name="teacher_id" required placeholder="Enter your Teacher ID">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label text-white">Password</label>
          <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
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

<script>
// Show the error message alert if there is an error
<?php if ($error): ?>
  document.getElementById('error-alert').style.display = 'block';
<?php endif; ?>
</script>

</body>
</html>
