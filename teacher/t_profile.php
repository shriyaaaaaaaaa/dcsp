<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['teacher_id'])) {
    header('Location: t_login.php');
    exit;
}
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

$department = $teacher['department'];

// Fetch subjects for this department
$subject_query = $conn->prepare("SELECT subject FROM courses_subjects WHERE LOWER(TRIM(faculty)) = LOWER(TRIM(?))");
$subject_query->bind_param("s", $department);
$subject_query->execute();
$subject_result = $subject_query->get_result();
$available_subjects = [];
while ($row = $subject_result->fetch_assoc()) {
    $available_subjects[] = $row['subject'];
}

// Default available time slots
$time_slots = [
    "08:00 AM - 09:00 AM",
    "09:00 AM - 10:00 AM",
    "10:00 AM - 11:00 AM",
    "11:00 AM - 12:00 PM",
    "12:00 PM - 01:00 PM",
    "01:00 PM - 02:00 PM",
    "02:00 PM - 03:00 PM",
    "03:00 PM - 04:00 PM"
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $selected_subjects = $_POST['subjects'] ?? [];
    $selected_times = $_POST['times'] ?? [];

    // Convert selected times into 24-hour format before saving
    $converted_times = [];
    foreach ($selected_times as $timeStr) {
        if (strpos($timeStr, '-') !== false) {
            [$start, $end] = explode('-', $timeStr, 2);
            $start24 = date("H:i", strtotime(trim($start)));
            $end24   = date("H:i", strtotime(trim($end)));
            $converted_times[] = "$start24-$end24";
        } else {
            $converted_times[] = date("H:i", strtotime($timeStr));
        }
    }

    $subjects_str = implode(',', $selected_subjects);
    $times_str = implode(',', $converted_times); // store 24-hour format

    $update_query = $conn->prepare("UPDATE teacher SET phone = ?, subjects = ?, available = ?, tick = '1' WHERE reg_no = ?");
    $update_query->bind_param("ssss", $phone, $subjects_str, $times_str, $teacher_id);
    if ($update_query->execute()) {
        header('Location: t_dashboard.php');
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}


// Convert saved data to arrays for preview
$current_subjects = array_filter(array_map('trim', explode(',', $teacher['subjects'] ?? '')));
$current_times = array_filter(array_map('trim', explode(',', $teacher['available'] ?? '')));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h1 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .data-box {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        .form-check-label {
            display: block;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin: 5px;
            text-align: center;
            cursor: pointer;
            user-select: none;
            transition: background 0.3s, border-color 0.3s;
        }
        .form-check-input {
            display: none; /* hide default checkbox */
        }
        .form-check-input:checked + .form-check-label {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .time-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Teacher Profile</h1>

    <!-- Preview Mode -->
    <div id="preview-section">
        <label><strong>Registration Number:</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['reg_no']) ?></div>

        <label><strong>Name:</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['name']) ?></div>

        <label><strong>Email:</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['email']) ?></div>

        <label><strong>Phone:</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['phone']) ?></div>

        <label><strong>Subjects:</strong></label>
        <div class="data-box">
            <?php if ($current_subjects): ?>
                <ul class="mb-0">
                    <?php foreach ($current_subjects as $sub): ?>
                        <li><?= htmlspecialchars($sub) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No subjects assigned.</p>
            <?php endif; ?>
        </div>

        <label><strong>Available Times:</strong></label>
        <div class="data-box">
            <?php if ($current_times): ?>
                <ul class="mb-0">
                    <?php foreach ($current_times as $time): ?>
                        <li><?= htmlspecialchars(convertTo12Hours($time)) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No availability specified.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Mode -->
    <form method="POST" id="edit-section" style="display:none;">
        <label><strong>Registration Number (not editable):</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['reg_no']) ?></div>

        <label><strong>Name (not editable):</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['name']) ?></div>

        <label><strong>Email (not editable):</strong></label>
        <div class="data-box"><?= htmlspecialchars($teacher['email']) ?></div>

        <label><strong>Phone:</strong></label>
        <input type="text" class="form-control mb-3" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>">

        <label><strong>Select Subjects:</strong></label>
        <div class="subject-grid mb-3">
            <?php foreach ($available_subjects as $sub): ?>
                <div>
                    <input class="form-check-input" type="checkbox" id="sub-<?= md5($sub) ?>" name="subjects[]" value="<?= htmlspecialchars($sub) ?>"
                        <?= in_array($sub, $current_subjects) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="sub-<?= md5($sub) ?>"><?= htmlspecialchars($sub) ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <label><strong>Select Available Times:</strong></label>
        <div class="time-grid mb-3">
            <?php foreach ($time_slots as $slot): ?>
                <div>
                    <input class="form-check-input" type="checkbox" id="time-<?= md5($slot) ?>" name="times[]" value="<?= htmlspecialchars($slot) ?>"
                        <?= in_array($slot, $current_times) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="time-<?= md5($slot) ?>"><?= htmlspecialchars($slot) ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="btn-container">
            <button type="submit" class="btn btn-success px-4">💾 Save Changes</button>
            <button type="button" class="btn btn-secondary px-4" onclick="toggleSections()">Cancel</button>
        </div>
    </form>

    <!-- Buttons -->
    <div class="btn-container" id="action-buttons">
        <button class="btn btn-primary px-4" onclick="toggleSections()">✏️ Edit Profile</button>
        <a href="t_dashboard.php" class="btn btn-outline-dark px-4">⬅ Back</a>
    </div>
</div>

<script>
function toggleSections() {
    document.getElementById('preview-section').style.display =
        document.getElementById('preview-section').style.display === 'none' ? 'block' : 'none';
    document.getElementById('edit-section').style.display =
        document.getElementById('edit-section').style.display === 'none' ? 'block' : 'none';
    document.getElementById('action-buttons').style.display =
        document.getElementById('action-buttons').style.display === 'none' ? 'flex' : 'none';
}
</script>
</body>
</html>
