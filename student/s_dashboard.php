<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies
include('includes/db_connect.php');

if (!isset($_SESSION['student_id'])) {
    header('Location: s_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student details
$query = $conn->prepare("SELECT * FROM student WHERE id = ?");
$query->bind_param("i", $student_id);
$query->execute();
$student = $query->get_result()->fetch_assoc();

if (!$student) {
    echo "Error: Student not found!";
    exit;
}

$student_name = $student['name'];
$org_id = $student['org_id'];
$class_id = $student['class_id'] ?? null; // assuming you have class_id in student table
$tick = $student['tick'];

// Redirect if no class assigned
if (empty($class_id)) {
    echo "<script>alert('Please update your profile first to select a class.'); window.location.href='s_profile.php';</script>";
    exit;
}

// Convert time function
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
  <title>Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="css/s_dashboard.css" />
  <link rel="stylesheet" href="css/footer.css" />
</head>
<body>

<!-- Header -->
<div class="header d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center text-white">
    <h5 class="me-4 mb-0"><?= htmlspecialchars($student_name) ?></h5>
    <div class="nav-buttons d-flex gap-2">
      <a class="nav-link-box" href="s_dashboard.php">Home</a>
      <a class="nav-link-box" href="view_schedule.php">Schedule</a>
      <a class="nav-link-box" href="s_profile.php">Profile</a>
    </div>
  </div>
  <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Body Section -->
<div class="container py-5">
  <div id="home">
    <h3 class="section-title mb-4 text-center">📅 Your Class Schedule</h3>

    <?php
    $sched_query = $conn->prepare("SELECT id, class_name, schedule_json, created_at 
                                   FROM schedule 
                                   WHERE org_id = ? AND id = ? 
                                   ORDER BY created_at DESC");
    $sched_query->bind_param("ii", $org_id, $class_id);
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
    <div class="col-12 mb-5">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header text-white bg-gradient-primary p-3 rounded-top-4">
          <h5 class="mb-0">
            <?= htmlspecialchars($className) ?>
            <small class="text-light"> | Generated: <?= date('d M Y', strtotime($sched['created_at'])) ?></small>
          </h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th style="width: 10%">Period</th>
                  <th style="width: 20%">Time</th>
                  <th style="width: 35%">Subject</th>
                  <th style="width: 35%">Teacher</th>
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
                <tr>
                  <td class="text-center"><span class="badge bg-primary fs-6"><?= $period++ ?></span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="material-icons text-info me-2">schedule</i>
                      <span><?= $time ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="material-icons text-success me-2">book</i>
                      <span class="fw-semibold"><?= $subject ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="material-icons text-primary me-2">person</i>
                      <span class="fw-bold"><?= $teacherName ?></span>
                    </div>
                  </td>
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
        echo "<div class='alert alert-warning text-center fs-5'>⚠️ No schedule found for your class yet. Please wait while the admin generates it.</div>";
    endif;
    $sched_query->close();
    ?>
  </div>
</div>

<style>
.section-title {
  font-weight: 700;
  font-size: 1.6rem;
  color: #05142dff;
}
.bg-gradient-primary {
  background: linear-gradient(45deg, #0d6efd, #4dabf7);
}
.table th, .table td {
  padding: 14px;
  font-size: 1rem;
  vertical-align: middle;
}
.table td div {
  display: flex;
  align-items: center;
}
.card {
  transition: transform 0.2s ease-in-out;
}
.card:hover {
  transform: translateY(-5px);
}
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
