<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['student_id'])) {
    header('Location: s_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$success_message = '';
$error_message = '';

// Fetch student details
$query = $conn->prepare("SELECT * FROM student WHERE id = ?");
$query->bind_param("i", $student_id);
$query->execute();
$student = $query->get_result()->fetch_assoc();

if (!$student) {
    echo "Error: Student not found!";
    exit;
}

$student_name  = $student['name'];
$student_email = $student['email'];
$student_reg   = $student['reg_no'];
$org_id        = $student['org_id'];
$class_id      = $student['class_id'];
$class_name    = $student['class'];

// Fetch available classes for this org - using string comparison
$class_query = $conn->prepare("SELECT id, class_name FROM schedule WHERE CAST(org_id AS CHAR) = ? ORDER BY class_name ASC");
$class_query->bind_param("s", $org_id);
$class_query->execute();
$class_result = $class_query->get_result();

// Check if there are no classes available
$no_classes = ($class_result->num_rows == 0);

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_class_id = intval($_POST['class_id']);
    
    // Validate inputs
    if (empty($name) || empty($email) || $new_class_id <= 0) {
        $error_message = 'Please fill all fields correctly.';
    } else {
        // Fetch selected class name
        $class_stmt = $conn->prepare("SELECT class_name FROM schedule WHERE id = ? AND CAST(org_id AS CHAR) = ? LIMIT 1");
        $class_stmt->bind_param("is", $new_class_id, $org_id);
        $class_stmt->execute();
        $classRow = $class_stmt->get_result()->fetch_assoc();
        
        if ($classRow) {
            $new_class_name = $classRow['class_name'];
            
            $update = $conn->prepare("UPDATE student SET name=?, email=?, class=?, class_id=? WHERE id=?");
            $update->bind_param("sssii", $name, $email, $new_class_name, $new_class_id, $student_id);
            
            if ($update->execute()) {
                $success_message = 'Profile updated successfully!';
                // Update current session variables
                $student_name = $name;
                $student_email = $email;
                $class_id = $new_class_id;
                $class_name = $new_class_name;
                
                // Redirect after 2 seconds
                echo "<script>setTimeout(function(){ window.location.href='s_dashboard.php'; }, 2000);</script>";
            } else {
                $error_message = 'Error updating profile. Please try again.';
            }
        } else {
            $error_message = 'Invalid class selected.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
    .card {
      transition: box-shadow 0.3s ease;
    }
    .card:hover {
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
    .form-label {
      font-weight: 500;
      color: #495057;
    }
    .btn {
      border-radius: 8px;
      font-weight: 500;
      padding: 10px 24px;
    }
    .alert {
      border-radius: 10px;
      border: none;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
      
      <!-- Success Alert -->
      <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Error Alert -->
      <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Warning for No Classes -->
      <?php if ($no_classes): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
          <i class="bi bi-exclamation-circle-fill me-2"></i>
          <strong>Notice!</strong> No classes are available for your organization yet. Please contact your administrator.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Profile Card -->
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
          <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Student Profile</h4>
        </div>
        <div class="card-body p-4">
          <form method="POST" action="" id="profileForm">
            
            <!-- Registration Number -->
            <div class="mb-3">
              <label class="form-label">
                <i class="bi bi-card-text text-primary me-1"></i> Registration No.
              </label>
              <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($student_reg) ?>" disabled>
              <small class="text-muted">This field cannot be changed</small>
            </div>
            
            <!-- Full Name -->
            <div class="mb-3">
              <label class="form-label">
                <i class="bi bi-person text-primary me-1"></i> Full Name <span class="text-danger">*</span>
              </label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student_name) ?>" required placeholder="Enter your full name">
            </div>
            
            <!-- Email -->
            <div class="mb-3">
              <label class="form-label">
                <i class="bi bi-envelope text-primary me-1"></i> Email <span class="text-danger">*</span>
              </label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student_email) ?>" required placeholder="Enter your email">
            </div>
            
            <!-- Class -->
            <div class="mb-4">
              <label class="form-label">
                <i class="bi bi-book text-primary me-1"></i> Class <span class="text-danger">*</span>
              </label>
              <select name="class_id" class="form-select" required <?= $no_classes ? 'disabled' : '' ?>>
                <option value="">-- Select Class --</option>
                <?php 
                if ($class_result->num_rows > 0) {
                    while ($row = $class_result->fetch_assoc()): 
                ?>
                  <option value="<?= $row['id'] ?>" <?= $row['id'] == $class_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['class_name']) ?>
                  </option>
                <?php 
                    endwhile;
                } else {
                    echo '<option value="">No classes available</option>';
                }
                ?>
              </select>
              <?php if ($no_classes): ?>
                <small class="text-danger">
                  <i class="bi bi-info-circle"></i> No classes have been created for your organization yet.
                </small>
              <?php endif; ?>
            </div>
            
            <!-- Submit Button -->
            <div class="d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-success" <?= $no_classes ? 'disabled' : '' ?>>
                <i class="bi bi-save me-1"></i> Save Changes
              </button>
            </div>
          </form>
          
          <!-- Back Button - Outside Form -->
          <div class="mt-3">
            <a href="s_dashboard.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
          </div>
        </div>
      </div>

      <!-- Debug Info (Remove in production) -->
      <div class="mt-3 p-3 bg-white rounded shadow-sm">
        <small class="text-muted">
          <strong>Debug Info:</strong><br>
          Organization ID: <?= htmlspecialchars($org_id) ?> | 
          Classes Found: <?= $class_result->num_rows ?> | 
          Current Class ID: <?= htmlspecialchars($class_id) ?> |
          Current Class Name: <?= htmlspecialchars($class_name ?: 'Not set') ?>
        </small>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
</body>
</html>