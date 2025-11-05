<?php
include('includes/header.php'); // Includes db_connect.php and session handling

// Ensure org_name and sub_admin_id are available from header.php
if (empty($org_name) || empty($_SESSION['sub_admin_id'])) {
    die("Error: Organization name or sub-admin ID not found.");
}

// Normalize table name using org_name and sub_admin_id
$sub_admin_id = $_SESSION['sub_admin_id'];
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

// Verify table exists
if (!$conn->query("SHOW TABLES LIKE '$table_slug'")->num_rows) {
    die("Error: Table '$table_slug' does not exist.");
}

// Query to fetch subjects from all rows in the table
$sql = "SELECT subjects FROM `$table_slug`";
$result = $conn->query($sql);

$subjects = [];
$log_file = 'json_errors.log'; // Log file for invalid JSON (ensure write permissions)

if ($result && $result->num_rows > 0) {
    $row_index = 0;
    while ($row = $result->fetch_assoc()) {
        $row_index++;
        // Check if subjects is non-empty before decoding
        if (!empty($row['subjects'])) {
            // Parse JSON subjects
            $row_subjects = json_decode($row['subjects'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($row_subjects)) {
                // Merge valid subjects
                $subjects = array_merge($subjects, $row_subjects);
            } else {
                // Log invalid JSON to file instead of displaying
                $error_message = date('Y-m-d H:i') . " - Row $row_index in $table_slug: Invalid JSON - " . htmlspecialchars($row['subjects']) . " (" . json_last_error_msg() . ")\n";
                file_put_contents($log_file, $error_message, FILE_APPEND);
            }
        } else {
            // Log empty subjects
            $error_message = date('Y-m-d H:i') . " - Row $row_index in $table_slug: Empty or null subjects\n";
            file_put_contents($log_file, $error_message, FILE_APPEND);
        }
    }
    // Remove duplicates and trim whitespace
    $subjects = array_unique(array_map('trim', $subjects));
}

// Display warning if no valid subjects found
if (empty($subjects)) {
    echo "<div class='alert alert-warning'>No valid subjects found for this sub-admin. Please add subjects to proceed.</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'] ?? '';
    $selected_subjects = $_POST['subjects'] ?? [];
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    // Convert selected subjects to a comma-separated string
    $subjects_string = implode(',', $selected_subjects);

    if ($class_name && $start_time && $end_time && !empty($selected_subjects)) {
        // Insert into classes table
        $sql = "INSERT INTO classes (org_name, sub_admin_id, class_name, subjects, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sissss", $org_name, $sub_admin_id, $class_name, $subjects_string, $start_time, $end_time);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Class created successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error creating class: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Please fill all required fields and select at least one subject.</div>";
    }
}
?>

<div class="container">
    <h2 class="text-center mb-4">Manage Class for <span class="text-primary"><?= htmlspecialchars($org_name) ?></span></h2>

    <?php if (!empty($subjects)): ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="class_name" class="form-label">Class Name</label>
            <input type="text" class="form-control" id="class_name" name="class_name" required placeholder="Enter class name">
        </div>

        <div class="mb-3">
        <label class="form-label d-flex align-items-center justify-content-between">
            <span>Subjects</span>
            <a href="add_subjects.php" class="text-decoration-none ms-2" title="Add New Subject">
            <i class="bi bi-plus-circle fs-5"></i>
         </a>
            </label>            
            <div class="subjects-container">
                <div class="row">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars(trim($subject)); ?>" id="subject_<?php echo htmlspecialchars(trim($subject)); ?>">
                                <label class="form-check-label" for="subject_<?php echo htmlspecialchars(trim($subject)); ?>">
                                    <?php echo htmlspecialchars(trim($subject)); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="mb-3 time-row">
            <div class="time-field">
                <label class="form-label">Time Duration</label>
                <input type="time" class="form-control" id="start_time" name="start_time" required>
            </div>
            <span class="dash">-</span>
            <div class="time-field">
                <label for="end_time" class="form-label">To</label>
                <input type="time" class="form-control" id="end_time" name="end_time" required>
            </div>
        </div>

        <div class="button-group">
            <a href="classes.php" class="btn btn-secondary">Done</a>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
    <?php endif; ?>
</div>
</div>

<?php
$conn->close();
include('includes/footer.php');
?>
</body>
</html>