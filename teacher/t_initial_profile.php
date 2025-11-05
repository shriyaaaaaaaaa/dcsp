<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['teacher_id'])) {
    header('Location: t_login.php');
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher data
$query = $conn->prepare("SELECT * FROM teacher WHERE reg_no = ?");
$query->bind_param("s", $teacher_id);
$query->execute();
$result = $query->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    die("Error: No teacher found for reg_no $teacher_id");
}

// Redirect if tick is not '0'
if ($teacher['tick'] != '0') {
    header('Location: t_dashboard.php');
    exit;
}

$department = $teacher['department'];

// Fetch subjects from courses_subjects where faculty matches teacher's department
$subject_query = $conn->prepare("SELECT subject FROM courses_subjects WHERE LOWER(TRIM(faculty)) = LOWER(TRIM(?))");
$subject_query->bind_param("s", $department);
$subject_query->execute();
$subject_result = $subject_query->get_result();
$available_subjects = [];
while ($row = $subject_result->fetch_assoc()) {
    $available_subjects[] = $row['subject'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_subjects = $_POST['subjects'] ?? [];
    $selected_times = $_POST['times'] ?? [];

    // Validate minimum selections
    if (empty($selected_subjects)) {
        echo "<script>alert('Please select at least one subject.');</script>";
    } elseif (empty($selected_times)) {
        echo "<script>alert('Please select at least one time period.');</script>";
    } else {
        // Convert arrays to comma-separated strings
        $subjects_str = !empty($selected_subjects) ? implode(',', $selected_subjects) : '';
        $times_str = !empty($selected_times) ? implode(',', $selected_times) : '';

        // Update teacher table with subjects and available_times
        $update_query = $conn->prepare("UPDATE teacher SET subjects = ?, available = ?, tick = '1' WHERE reg_no = ?");
        $update_query->bind_param("sss", $subjects_str, $times_str, $teacher_id);
        if ($update_query->execute()) {
            header('Location: t_dashboard.php');
            exit;
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $update_query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Initial Setup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/t_initial_profile.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <h2>Initial Setup</h2>
  <p>Please select your subjects and available time periods based on your department: <?= htmlspecialchars($department) ?>.</p>
  <form method="POST" action="t_initial_profile.php" id="setupForm">
    <div class="subject-container">
      <div class="subject-grid">
        <?php if (empty($available_subjects)): ?>
          <p class="text-danger">No subjects available for your department.</p>
        <?php else: ?>
          <?php foreach ($available_subjects as $subject): ?>
            <div class="subject-option" onclick="toggleSelection(this, 'subjects[]', '<?= htmlspecialchars(trim($subject)) ?>')">
              <?= htmlspecialchars(trim($subject)) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="time-container">
      <div class="time-grid">
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '06:00-07:00')">6:00 AM - 7:00 AM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '07:00-08:00')">7:00 AM - 8:00 AM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '08:00-09:00')">8:00 AM - 9:00 AM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '09:00-10:00')">9:00 AM - 10:00 AM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '10:00-11:00')">10:00 AM - 11:00 AM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '11:00-12:00')">11:00 AM - 12:00 PM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '12:00-13:00')">12:00 PM - 1:00 PM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '14:00-15:00')">2:00 PM - 3:00 PM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '15:00-16:00')">3:00 PM - 4:00 PM</div>
        <div class="time-option" onclick="toggleSelection(this, 'times[]', '16:00-17:00')">4:00 PM - 5:00 PM</div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" id="submitButton" disabled>Submit</button>
  </form>
</div>

<script>
function toggleSelection(element, name, value) {
    element.classList.toggle('selected');
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;

    let form = element.closest('form');
    let existingInput = form.querySelector(`input[name="${name}"][value="${value}"]`);
    if (element.classList.contains('selected')) {
        if (!existingInput) {
            form.appendChild(input);
        }
    } else {
        if (existingInput) {
            existingInput.remove();
        }
    }
    updateSubmitButton();
}

function updateSubmitButton() {
    let form = document.getElementById('setupForm');
    let subjects = form.querySelectorAll('input[name="subjects[]"]');
    let times = form.querySelectorAll('input[name="times[]"]');
    let submitButton = document.getElementById('submitButton');
    if (subjects.length > 0 && times.length > 0) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

