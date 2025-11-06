<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/header.php');
include('includes/db_connect.php');

// Check if org_id is provided
if (!isset($_GET['org_id'])) {
    $_SESSION['error_message'] = 'Please select an organization first.';
    header('Location: place.php');
    exit;
}
$org_id = $_GET['org_id'];

// Get error message if it exists
$error_message = "";
$attempted_reg_no = "";
$attempted_name = "";
$attempted_email = "";

if (isset($_SESSION['registration_error'])) {
    $error_message = $_SESSION['registration_error'];
    $attempted_reg_no = $_SESSION['attempted_reg_no'] ?? '';
    $attempted_name = $_SESSION['attempted_name'] ?? '';
    $attempted_email = $_SESSION['attempted_email'] ?? '';
    unset($_SESSION['registration_error']);
    unset($_SESSION['attempted_reg_no']);
    unset($_SESSION['attempted_name']);
    unset($_SESSION['attempted_email']);
}
?>

<!-- Bootstrap & Material Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body {
    background: url('img/s_register.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
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
    padding: 12px;
}

.btn-success:hover {
    background-color: #218838;
}

input.form-control {
    border-radius: 10px;
    padding: 12px;
}

.text-center a {
    color: #2c3e50;
}

.text-center a:hover {
    color: #0069d9;
    text-decoration: underline;
}

.alert-warning {
    border-left: 4px solid #ffc107;
    background-color: rgba(255, 243, 205, 0.95);
    border-radius: 10px;
    animation: slideDown 0.5s ease-out;
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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

          <!-- Show error message as Bootstrap alert -->
          <?php if ($error_message): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <div class="d-flex align-items-start">
                <i class="material-icons text-warning me-2" style="font-size: 24px;">warning</i>
                <div>
                  <strong>Registration Failed!</strong>
                  <p class="mb-2"><?= htmlspecialchars($error_message) ?></p>
                  <small class="text-muted">
                    <i class="material-icons" style="font-size: 16px; vertical-align: middle;">info</i>
                    <strong>Expected Format:</strong> Please ensure your registration number matches the format provided by your organization (e.g., STU-2024-001, 2024001, or as specified by your institution).
                  </small>
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="s_register_process.php" method="POST" autocomplete="off">
            <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">
            
            <div class="mb-3">
              <label for="reg_no" class="form-label">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">badge</i>
                Registration Number <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                name="reg_no" 
                id="reg_no" 
                class="form-control <?= $error_message ? 'is-invalid' : '' ?>" 
                required 
                placeholder="Enter your student registration number"
                value="<?= htmlspecialchars($attempted_reg_no) ?>">
              <div class="form-text">
                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">help_outline</i>
                Enter the registration number provided by your organization
              </div>
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">person</i>
                Full Name <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                name="name" 
                id="name" 
                class="form-control" 
                required 
                placeholder="Enter your full name"
                value="<?= htmlspecialchars($attempted_name) ?>">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">email</i>
                Email Address <span class="text-danger">*</span>
              </label>
              <input 
                type="email" 
                name="email" 
                id="email" 
                class="form-control" 
                required 
                placeholder="Enter your email address"
                value="<?= htmlspecialchars($attempted_email) ?>">
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">lock</i>
                Password <span class="text-danger">*</span>
              </label>
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control" 
                required 
                placeholder="Create a password"
                minlength="6">
              <div class="form-text">Minimum 6 characters</div>
            </div>

            <div class="mb-3">
              <label for="confirm_password" class="form-label">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">lock_outline</i>
                Confirm Password <span class="text-danger">*</span>
              </label>
              <input 
                type="password" 
                name="confirm_password" 
                id="confirm_password" 
                class="form-control" 
                required 
                placeholder="Re-enter your password"
                minlength="6">
            </div>

            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-success">
                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">how_to_reg</i>
                Register
              </button>
            </div>

            <p class="text-center">
              Already registered? 
              <a href="s_login.php" class="fw-bold">Login here</a>
            </p>
            <p class="text-center mt-2">
              <a href="../index.php">← Back to Home</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Focus on registration number field if there's an error
<?php if ($error_message): ?>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('reg_no').focus();
  });
<?php endif; ?>

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
  const password = document.getElementById('password').value;
  const confirmPassword = this.value;
  
  if (password !== confirmPassword) {
    this.setCustomValidity('Passwords do not match');
  } else {
    this.setCustomValidity('');
  }
});

// Prevent form resubmission
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}

// Clear browser cache on page load
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>

</body>
</html>