<?php
session_start();

// Get session values
$org_name = $_SESSION['org_name'] ?? '';
$sub_admin_id = $_SESSION['sub_admin_id'] ?? '';

if (!$org_name || !$sub_admin_id) {
    die("Session not found. Please log in again.");
}

// Normalize table name using org name and session ID to ensure uniqueness
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

// Connect to database
$conn = new mysqli("localhost", "root", "", "dcsp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$check = $conn->query("SHOW TABLES LIKE '$table_slug'");
if ($check->num_rows == 0) {
    $create_sql = "CREATE TABLE `$table_slug` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        org_name VARCHAR(255),
        sub_admin_id INT,
        teacher_reg VARCHAR(255),
        student_reg VARCHAR(255),
        subjects Varchar(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_teacher (teacher_reg, sub_admin_id),
        UNIQUE KEY unique_student (student_reg, sub_admin_id)
    )";
    if (!$conn->query($create_sql)) {
        die("Table creation failed: " . $conn->error);
    }
}

$success = $error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_teacher'])) {
        $teacher_name = trim($_POST['teacher_name']);
        if (!empty($teacher_name)) {
            // Check for duplicate teacher for this sub_admin
            $stmt = $conn->prepare("SELECT * FROM `$table_slug` WHERE LOWER(teacher_reg) = LOWER(?) AND sub_admin_id = ?");
            $stmt->bind_param("si", $teacher_name, $sub_admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Teacher '$teacher_name' already exists.";
            } else {
                $insert = $conn->prepare("INSERT INTO `$table_slug` (org_name, sub_admin_id, teacher_reg) VALUES (?, ?, ?)");
                $insert->bind_param("sis", $org_name, $sub_admin_id, $teacher_name);
                if ($insert->execute()) {
                    $success = "Teacher '$teacher_name' added successfully.";
                } else {
                    $error = "Error inserting teacher.";
                }
            }
        } else {
            $error = "Please enter a teacher name.";
        }
    }

    if (isset($_POST['submit_student'])) {
        $student_name = trim($_POST['student_name']);
        if (!empty($student_name)) {
            // Check for duplicate student for this sub_admin
            $stmt = $conn->prepare("SELECT * FROM `$table_slug` WHERE LOWER(student_reg) = LOWER(?) AND sub_admin_id = ?");
            $stmt->bind_param("si", $student_name, $sub_admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Student '$student_name' already exists.";
            } else {
                $insert = $conn->prepare("INSERT INTO `$table_slug` (org_name, sub_admin_id, student_reg) VALUES (?, ?, ?)");
                $insert->bind_param("sis", $org_name, $sub_admin_id, $student_name);
                if ($insert->execute()) {
                    $success = "Student '$student_name' added successfully.";
                } else {
                    $error = "Error inserting student.";
                }
            }
        } else {
            $error = "Please enter a student name.";
        }
    }

    if (isset($_POST['done'])) {
        header("Location: sa_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="css/footer.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .card-form {
            padding: 20px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .fade-out {
            animation: fadeOut 3s forwards;
            animation-delay: 2s;
        }
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper"> <!-- START WRAPPER -->

<header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="sa_dashboard.php"><?php echo htmlspecialchars($org_name); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="sa_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="sa_profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="member.php">Member</a></li>
        <li class="nav-item"><a class="nav-link" href="display_schedule.php">Schedule</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" data-bs-toggle="dropdown">Manage</a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="manage_teachers.php">Teachers</a></li>
            <li><a class="dropdown-item" href="manage_students.php">Students</a></li>
            <li><a class="dropdown-item" href="classes.php">Classes</a></li>
            <li><a class="dropdown-item" href="subjects.php">Subjects</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="btn btn-danger ms-2" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</header>

<div class="container py-5">
    <h2 class="text-center mb-4">Manage Members for <span class="text-primary"><?= htmlspecialchars($org_name) ?></span></h2>

    <?php if ($success): ?>
        <div class="alert alert-success text-center fade-out"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger text-center fade-out"><?= $error ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
            <form method="post" class="card-form">
                <h5 class="mb-3">Add Teacher</h5>
                <input type="text" name="teacher_name" class="form-control mb-3" placeholder="Enter teacher name">
                <button type="submit" name="submit_teacher" class="btn btn-primary w-100">Submit Teacher</button>
            </form>
        </div>
        <div class="col-md-5 mb-4">
            <form method="post" class="card-form">
                <h5 class="mb-3">Add Student</h5>
                <input type="text" name="student_name" class="form-control mb-3" placeholder="Enter student name">
                <button type="submit" name="submit_student" class="btn btn-success w-100">Submit Student</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-4">
        <form method="post">
            <button name="done" class="btn btn-secondary px-4">Done</button>
        </form>
    </div>
</div>
    </div>
    <?php include('includes/footer.php'); ?>
<script>
    setTimeout(() => {
        document.querySelectorAll('.fade-out').forEach(el => el.remove());
    }, 4000);
</script>
</body>
</html>
