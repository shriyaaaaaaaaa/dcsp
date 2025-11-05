<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");  // redirect if not logged in
    exit();
}
include('includes/db_connect.php');
 
// Initialize counts
$teacher_count = 0;
$student_count = 0;
$pending_count = 0;

// Fetch teacher count
$stmt = $conn->prepare("SELECT COUNT(*) as teacher_count FROM teacher");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher_count = $result->fetch_assoc()['teacher_count'];
    $stmt->close();
} else {
    error_log("Failed to prepare teacher count query: " . $conn->error);
}

// Fetch student count (assuming a student table exists)
$stmt = $conn->prepare("SELECT COUNT(*) as student_count FROM student");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $student_count = $result->fetch_assoc()['student_count'];
    $stmt->close();
} else {
    error_log("Failed to prepare student count query: " . $conn->error);
}

// Fetch pending approvals count
$stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM sub_admin WHERE approval = 0");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_count = $result->fetch_assoc()['pending_count'];
    $stmt->close();
} else {
    error_log("Failed to prepare pending count query: " . $conn->error);
}
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Dashboard</h1>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body">
          <h5 class="card-title">Total Teachers</h5>
          <p class="card-text"><?= htmlspecialchars($teacher_count) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-body">
          <h5 class="card-title">Total Students</h5>
          <p class="card-text"><?= htmlspecialchars($student_count) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <a href="pending_approvals.php" class="text-decoration-none">
        <div class="card text-white bg-warning mb-3">
          <div class="card-body">
            <h5 class="card-title">Pending Approvals</h5>
            <p class="card-text"><?= htmlspecialchars($pending_count) ?></p>
          </div>
        </div>
      </a>
    </div>
  </div>
</main>

<?php include('includes/footer.php'); ?>