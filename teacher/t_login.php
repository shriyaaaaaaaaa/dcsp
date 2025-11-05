<?php
session_start();
include('includes/header.php');
include('includes/db_connect.php'); // DB connection

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $reg_no = $_POST['teacher_id'];  // this is the input field for teacher_id
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM teacher WHERE reg_no = ?");
  $stmt->bind_param("s", $reg_no);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $teacher = $result->fetch_assoc();

    if ($password == $teacher['password']) {
      $_SESSION['teacher_id'] = $teacher['reg_no'];
      header("Location: t_dashboard.php");
      exit;
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "Teacher ID not found.";
  }
}
?>
<link rel="stylesheet" href="css/log_style.css">

<section class="login-page d-flex justify-content-center align-items-center">
  <div class="card login-card shadow-lg">
    <div class="card-body p-5">
      <h3 class="text-center mb-4 text-white">
        <span class="material-icons text-white">person</span>
        Teacher Login
      </h3>
      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label for="teacher_id" class="form-label text-white">Teacher ID</label>
          <input type="text" class="form-control" id="teacher_id" name="teacher_id" required placeholder="Enter your ID">
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

      </body>
      </html>