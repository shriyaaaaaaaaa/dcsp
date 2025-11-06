<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: t_login.php');
    exit;
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include('includes/db_connect.php');

$teacher_id = $_SESSION['teacher_id'];

// Get success message if it exists
$success_message = "";
if (isset($_SESSION['login_success'])) {
    $success_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Prepare and execute query to fetch teacher data
$query = $conn->prepare("SELECT name, org_id, tick FROM teacher WHERE reg_no = ?");
$query->bind_param("s", $teacher_id);
$query->execute();
$result = $query->get_result();
$teacher = $result->fetch_assoc();

if ($teacher === false) {
    session_destroy();
    header('Location: t_login.php');
    exit;
}

// Extract required fields
$teacher_name = $teacher['name'];
$org_id = $teacher['org_id'];
$tick = $teacher['tick'];

// Redirect if tick is not '0'
if ($tick == '0') {
    header('Location: t_initial_profile.php');
    exit;
}

//convert time to 12 hours
function convertTo12Hours($timeStr) {
    if (strpos($timeStr, '-') !== false) {
        [$start, $end] = explode('-', $timeStr, 2);
        $start12 = date("g:i A", strtotime(trim($start)));
        $end12   = date("g:i A", strtotime(trim($end)));
        return "$start12 - $end12";
    } else {
        return date("g:i A", strtotime($timeStr));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="css/t_dashboard.css" />
</head>
<body>

<!-- Header -->
<div class="header d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center text-white">
    <h5 class="me-4 mb-0"><?= htmlspecialchars($teacher_name) ?></h5>
    <div class="nav-buttons d-flex gap-2">
      <a class="nav-link-box" href="t_dashboard.php">Home</a>
      <a class="nav-link-box" href="#home">Schedule</a>
      <a class="nav-link-box" href="t_profile.php">Profile</a>
    </div>
  </div>
  <a href="t_logout.php" class="logout-btn">Logout</a>
</div>

<!-- Body Section -->
<div class="container py-5">

  <!-- Success Message Alert -->
  <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="material-icons" style="font-size: 20px; vertical-align: middle;">check_circle</i>
      <strong><?= htmlspecialchars($success_message) ?></strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Class Schedule Section -->
  <div id="home">
    <div class="container py-5">
      <h3 class="section-title mb-4">📘 Class Schedules</h3>

      <div class="row g-4">
          <?php
          $sched_query = $conn->prepare("SELECT class_name, schedule_json, created_at FROM schedule WHERE org_id = ? ORDER BY created_at DESC");
          $sched_query->bind_param("i", $org_id);
          $sched_query->execute();
          $schedule_result = $sched_query->get_result();

          if ($schedule_result->num_rows > 0):
              while ($sched = $schedule_result->fetch_assoc()):
                  $className = $sched['class_name'];
                  $scheduleData = json_decode($sched['schedule_json'], true);

                  if (!empty($scheduleData)) {
                      usort($scheduleData, function($a, $b) {
                          $timeA = $a['time'] ?? '';
                          $timeB = $b['time'] ?? '';
                          $startA = strpos($timeA, '-') !== false ? explode('-', $timeA)[0] : $timeA;
                          $startB = strpos($timeB, '-') !== false ? explode('-', $timeB)[0] : $timeB;
                          return strtotime(trim($startA)) <=> strtotime(trim($startB));
                      });
                  }
          ?>
          <div class="col-lg-6 col-md-12">
              <div class="card shadow-sm h-100 border-0 rounded-4">
                  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                      <h5 class="mb-0"><?= htmlspecialchars($className) ?></h5>
                      <small class="text-light">🗓 <?= date('d M Y', strtotime($sched['created_at'])) ?></small>
                  </div>
                  <div class="card-body p-0">
                      <div class="table-responsive">
                          <table class="table table-hover table-bordered mb-0 text-center align-middle">
                              <thead class="table-light">
                                  <tr>
                                      <th style="width:10%;">Period</th>
                                      <th style="width:25%;">Time</th>
                                      <th style="width:40%;">Subject</th>
                                      <th style="width:25%;">Teacher</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $period = 1;
                                  foreach ($scheduleData as $row):
                                      $time = htmlspecialchars(convertTo12Hours($row['time'] ?? ''));
                                      $subject = htmlspecialchars($row['subject'] ?? '');
                                      $teacherName = htmlspecialchars($row['teacher'] ?? '');
                                  ?>
                                  <tr class="align-middle">
                                      <td class="fw-bold"><?= $period++ ?></td>
                                      <td class="text-start"><i class="fa fa-clock me-1"></i> <?= $time ?></td>
                                      <td class="text-start fw-semibold"><i class="fa fa-book me-1"></i> <?= $subject ?></td>
                                      <td class="text-start fw-semibold"><i class="fa fa-chalkboard-teacher me-1"></i> <?= $teacherName ?></td>
                                  </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
          <?php
              endwhile;
          else:
              echo "<div class='alert alert-warning w-100 text-center'>⚠️ No schedule found for your organization yet. Please wait while the admin generates it.</div>";
          endif;
          $sched_query->close();
          ?>
      </div>
    </div>
  </div>

  <!-- Comment Box -->
  <div class="comment-box text-end">
      <form method="POST" action="comment.php">
          <textarea class="form-control mb-2" name="comment" rows="2" placeholder="Leave a comment..." required></textarea>
          <button type="submit" class="btn btn-primary">Comment</button>
      </form>
  </div>
</div>

<!-- Styles -->
<style>
.section-title {
    font-weight: 700;
    font-size: 1.5rem;
    color: #0d6efd;
    text-align: center;
}
.card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}
.card-header {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
    font-weight: 600;
}
.table th, .table td {
    font-size: 0.95rem;
    padding: 10px;
}
.table td i {
    color: #0d6efd;
}
.table-hover tbody tr:hover {
    background-color: #f0f8ff;
}
.table td, .table th {
    vertical-align: middle !important;
}
.alert {
  border-radius: 10px;
  animation: slideDown 0.5s ease-out;
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

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-hide success alert after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alert = document.querySelector('.alert-success');
  if (alert) {
    setTimeout(function() {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  }
});

// Prevent back button issues and form resubmission
window.history.replaceState(null, null, window.location.href);

window.addEventListener('popstate', function() {
  window.history.replaceState(null, null, window.location.href);
});

// Prevent page from being cached
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>

</body>
</html>