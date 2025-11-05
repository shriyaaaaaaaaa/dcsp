<?php
include('includes/header.php'); // Includes db_connect.php and session

// Validate essential session variables
if (empty($org_name) || empty($_SESSION['sub_admin_id'])) {
    $_SESSION['error'] = "Error: Organization name or sub-admin ID not found.";
    header("Location: classes.php");
    exit();
}

$sub_admin_id = $_SESSION['sub_admin_id'];
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = $error = "";
$debug_log = 'debug_log.txt';
$json_error_log = 'json_errors.log';

// Validate class ID
if ($class_id <= 0) {
    $_SESSION['error'] = "Invalid class ID.";
    header("Location: classes.php");
    exit();
}

// Fetch class info
$stmt = $conn->prepare("SELECT class_name, subjects, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time FROM classes WHERE id = ? AND sub_admin_id = ?");
$stmt->bind_param("ii", $class_id, $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['error'] = "Class not found or you do not have permission to edit it.";
    header("Location: classes.php");
    exit();
}
$class = $result->fetch_assoc();
$stmt->close();

// Log fetched data
file_put_contents($debug_log, date('Y-m-d H:i:s') . " - Class ID $class_id fetched: " . json_encode($class) . "\n", FILE_APPEND);

// Table slug generation
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

// Check if subject table exists
if (!$conn->query("SHOW TABLES LIKE '$table_slug'")->num_rows) {
    $_SESSION['error'] = "Subjects table '$table_slug' does not exist.";
    header("Location: classes.php");
    exit();
}

// Fetch subjects
$sql = "SELECT subjects FROM `$table_slug`";
$result = $conn->query($sql);
$subjects = [];

if ($result && $result->num_rows > 0) {
    $i = 0;
    while ($row = $result->fetch_assoc()) {
        $i++;
        if (!empty($row['subjects'])) {
            $decoded = json_decode($row['subjects'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $subjects = array_merge($subjects, $decoded);
            } else {
                file_put_contents($json_error_log, date('Y-m-d H:i:s') . " - Row $i in $table_slug: Invalid JSON - {$row['subjects']} (" . json_last_error_msg() . ")\n", FILE_APPEND);
            }
        } else {
            file_put_contents($json_error_log, date('Y-m-d H:i:s') . " - Row $i in $table_slug: Empty/null subjects\n", FILE_APPEND);
        }
    }
    $subjects = array_unique(array_map('trim', $subjects));
}

$class_subjects = array_map('trim', explode(',', $class['subjects']));

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $class_name = trim($_POST['class_name'] ?? '');
    $selected_subjects = $_POST['subjects'] ?? [];
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    file_put_contents($debug_log, date('Y-m-d H:i:s') . " - Submitted: class_name=$class_name, subjects=" . implode(',', $selected_subjects) . ", start_time=$start_time, end_time=$end_time\n", FILE_APPEND);

    $subjects_string = implode(',', $selected_subjects);
    $start_time_db = $start_time ? $start_time . ':00' : '';
    $end_time_db = $end_time ? $end_time . ':00' : '';

    if (empty($class_name) || empty($selected_subjects) || empty($start_time) || empty($end_time)) {
        $error = "Please fill all required fields and select at least one subject.";
    } elseif (strtotime($end_time_db) <= strtotime($start_time_db)) {
        $error = "End time must be after start time.";
    } else {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, subjects = ?, start_time = ?, end_time = ? WHERE id = ? AND sub_admin_id = ?");
        $stmt->bind_param("ssssii", $class_name, $subjects_string, $start_time_db, $end_time_db, $class_id, $sub_admin_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Class updated successfully.";
            $stmt->close();
            $conn->close();
            header("Location: classes.php");
            exit();
        } else {
            $error = "Error updating class: " . $stmt->error;
            file_put_contents($debug_log, date('Y-m-d H:i:s') . " - Update Error: " . $stmt->error . "\n", FILE_APPEND);
            $stmt->close();
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Validate class ID and sub-admin ID
    if ($class_id <= 0 || empty($sub_admin_id)) {
        $_SESSION['error'] = "Invalid class ID or sub-admin ID.";
        header("Location: classes.php");
        exit();
    }

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND sub_admin_id = ?");
    $stmt->bind_param("ii", $class_id, $sub_admin_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Class deleted successfully.";
        $stmt->close();
        $conn->close();
        header("Location: classes.php");
        exit();
    } else {
        $error = "Error deleting class: " . $stmt->error;
        file_put_contents($debug_log, date('Y-m-d H:i:s') . " - Delete Error: " . $stmt->error . "\n", FILE_APPEND);
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <!-- Assuming Bootstrap is included in header.php -->
    <style>
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .subjects-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .subject-item {
            padding: 5px;
        }
        .time-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .time-field {
            flex: 1;
        }
        .dash {
            font-size: 1.2em;
        }
        .alert.fade-out {
            animation: fadeOut 3s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Edit Class for <span class="text-primary"><?php echo htmlspecialchars($org_name); ?></span></h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success fade-out"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger fade-out"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($subjects)): ?>
    <form method="POST" action="" id="editClassForm">
        <div class="mb-3">
            <label for="class_name" class="form-label">Class Name</label>
            <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subjects</label>
            <div class="subjects-container">
                <div class="subject-grid">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="subject-item">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars($subject); ?>" id="subject_<?php echo htmlspecialchars($subject); ?>" <?php echo in_array($subject, $class_subjects) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="subject_<?php echo htmlspecialchars($subject); ?>">
                                    <?php echo htmlspecialchars($subject); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="mb-3 time-row">
            <div class="time-field">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control" name="start_time" id="start_time" value="<?php echo htmlspecialchars($class['start_time']); ?>" required step="60">
            </div>
            <span class="dash"> - </span>
            <div class="time-field">
                <label class="form-label">End Time</label>
                <input type="time" class="form-control" name="end_time" id="end_time" value="<?php echo htmlspecialchars($class['end_time']); ?>" required step="60">
            </div>
        </div>

        <div class="button-group">
            <a href="classes.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="submit" class="btn btn-primary">Update</button>
            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this class? This action cannot be undone.');">Remove</button>
        </div>
    </form>
    <?php else: ?>
        <div class="alert alert-warning">No valid subjects found. Please add subjects to proceed.</div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('editClassForm');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger d-none';
    form.prepend(errorDiv);

    form.addEventListener('submit', function (e) {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        const start = new Date(`1970-01-01T${startTime}:00`);
        const end = new Date(`1970-01-01T${endTime}:00`);

        if (end <= start && !e.target.querySelector('[name="delete"]')) {
            e.preventDefault();
            errorDiv.textContent = 'End time must be after start time.';
            errorDiv.classList.remove('d-none');
            setTimeout(() => errorDiv.classList.add('d-none'), 3000);
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>