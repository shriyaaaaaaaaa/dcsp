<?php
session_start();

// Get session values
$org_name = $_SESSION['org_name'] ?? '';
$sub_admin_id = $_SESSION['sub_admin_id'] ?? '';

if (!$org_name || !$sub_admin_id) {
    die("Session not found. Please log in again.");
}

// Normalize table name using org name and session ID
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

// Connect to database
$conn = new mysqli("localhost", "root", "", "dcsp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and create sub-admin table or add columns if missing
$check_table = $conn->query("SHOW TABLES LIKE '$table_slug'");
if ($check_table->num_rows == 0) {
    $create_sql = "CREATE TABLE `$table_slug` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        org_name VARCHAR(255) NOT NULL,
        sub_admin_id INT NOT NULL,
        subjects TEXT, -- Stores JSON-encoded array of subjects
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($create_sql)) {
        die("Table creation failed: " . $conn->error);
    }
} else {
    // Check and add org_name column if missing
    $check_org_name = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'org_name'");
    if ($check_org_name->num_rows == 0) {
        $alter_sql = "ALTER TABLE `$table_slug` ADD org_name VARCHAR(255) NOT NULL";
        if (!$conn->query($alter_sql)) {
            die("Failed to add org_name column: " . $conn->error);
        }
    }
    // Check and add sub_admin_id column if missing
    $check_sub_admin_id = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'sub_admin_id'");
    if ($check_sub_admin_id->num_rows == 0) {
        $alter_sql = "ALTER TABLE `$table_slug` ADD sub_admin_id INT NOT NULL";
        if (!$conn->query($alter_sql)) {
            die("Failed to add sub_admin_id column: " . $conn->error);
        }
    }
    // Check and add subjects column if missing
    $check_subjects = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'subjects'");
    if ($check_subjects->num_rows == 0) {
        $alter_sql = "ALTER TABLE `$table_slug` ADD subjects TEXT";
        if (!$conn->query($alter_sql)) {
            die("Failed to add subjects column: " . $conn->error);
        }
    }
}

// Handle AJAX requests for fetching faculties and subjects
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'get_faculties') {
        $category = $_GET['category'] ?? '';
        $stmt = $conn->prepare("SELECT DISTINCT faculty FROM courses_subjects WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $faculties = [];
        while ($row = $result->fetch_assoc()) {
            $faculties[] = $row['faculty'];
        }
        echo json_encode($faculties);
        exit;
    }

    if ($_GET['action'] === 'get_subjects') {
        $faculty = $_GET['faculty'] ?? '';
        $stmt = $conn->prepare("SELECT subject FROM courses_subjects WHERE faculty = ?");
        $stmt->bind_param("s", $faculty);
        $stmt->execute();
        $result = $stmt->get_result();
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
        echo json_encode($subjects);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $error = "";
    
    if (isset($_POST['submit'])) {
        $category = $_POST['category'] ?? '';
        $faculty = $_POST['faculty'] ?? '';
        $subjects = json_decode($_POST['subjects'] ?? '[]', true);
        $custom_subjects = json_decode($_POST['custom_subjects'] ?? '[]', true);

        if (empty($category) || empty($faculty)) {
            $error = "Please select a category and faculty.";
        } elseif (empty($subjects) && empty($custom_subjects)) {
            $error = "Please select or add at least one course.";
        } else {
            // Add custom subjects to courses_subjects table if they don't exist
            if (!empty($custom_subjects)) {
                $stmt = $conn->prepare("INSERT IGNORE INTO courses_subjects (category, faculty, subject) VALUES (?, ?, ?)");
                foreach ($custom_subjects as $subject) {
                    $stmt->bind_param("sss", $category, $faculty, $subject);
                    $stmt->execute();
                }
            }

            // Combine selected and custom subjects
            $all_subjects = array_merge($subjects, $custom_subjects);
            $subjects_json = json_encode($all_subjects);

            // Insert into sub-admin table with org_name and sub_admin_id
            $stmt = $conn->prepare("INSERT INTO `$table_slug` (org_name, sub_admin_id, subjects) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $org_name, $sub_admin_id, $subjects_json);
            if ($stmt->execute()) {
                $success = "Courses added successfully.";
            } else {
                $error = "Error adding courses: " . $conn->error;
            }
        }

       
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage College Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="css/sa_dashboard.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card-form { padding: 20px; border-radius: 10px; background: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .fade-out { animation: fadeOut 3s forwards; animation-delay: 2s; }
        @keyframes fadeOut { to { opacity: 0; visibility: hidden; } }
        .subject-tag { background: #e9ecef; padding: 5px 10px; margin: 5px; border-radius: 5px; }
    </style>
</head>
<body>
   <div class="page-wrapper">
    <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="sa_dashboard.php"><?php echo htmlspecialchars($org_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="sa_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="sa_profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="member.php">Member</a></li>
                    <li class="nav-item"><a class="nav-link" href="display_schedule.php">Schedule</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" data-bs-toggle="dropdown" aria-expanded="false">Manage</a>
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
        <h2 class="text-center mb-4">Add Available Courses</h2>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success fade-out"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger fade-out"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form id="courseForm" method="POST" class="card-form">
            <div class="mb-3">
                <label for="category" class="form-label">Select Category</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <option value="Business">Business</option>
                    <option value="Technology">Technology</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Medical">Medical</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="mb-3" id="facultySection" style="display:none;">
                <label for="faculty" class="form-label">Select Faculty</label>
                <select id="faculty" name="faculty" class="form-select" disabled>
                    <option value="">-- Select Faculty --</option>
                </select>
            </div>

            <div class="mb-3" id="subjectSection" style="display:none;">
                <label for="subjectSelect" class="form-label">Select Courses</label>
                <select id="subjectSelect" name="subjects[]" class="form-control" multiple></select>
                <input type="text" id="customSubject" class="form-control mt-2" placeholder="Or type new course">
                <button type="button" id="addCustomSubject" class="btn btn-sm btn-outline-primary mt-2">Add Course</button>
                <div class="mt-3">
                    <strong>Selected Courses:</strong>
                    <div id="subjectList" class="d-flex flex-wrap"></div>
                    <input type="hidden" id="finalSubjects" name="subjects">
                    <input type="hidden" id="customSubjects" name="custom_subjects">
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" name="submit" class="btn btn-success">Save Courses</button>
                <a href="sa_dashboard.php" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    </div>
       
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('#subjectSelect').select2({
                placeholder: "Select or search courses",
                allowClear: true
            });

            // Track custom subjects
            let customSubjects = [];

            // Category change: Fetch faculties
            $('#category').on('change', function () {
                const category = $(this).val();
                const facultySelect = $('#faculty');
                facultySelect.empty().append('<option value="">-- Select Faculty --</option>');
                $('#facultySection, #subjectSection').hide();
                
                if (category) {
                    $.get('?action=get_faculties&category=' + encodeURIComponent(category), function (data) {
                        data.forEach(faculty => {
                            facultySelect.append(`<option value="${faculty}">${faculty}</option>`);
                        });
                        facultySelect.prop('disabled', false);
                        $('#facultySection').show();
                    });
                }
            });

            // Faculty change: Fetch subjects
            $('#faculty').on('change', function () {
                const faculty = $(this).val();
                const subjectSelect = $('#subjectSelect');
                subjectSelect.empty();
                $('#subjectSection').hide();

                if (faculty) {
                    $.get('?action=get_subjects&faculty=' + encodeURIComponent(faculty), function (data) {
                        data.forEach(subject => {
                            subjectSelect.append(`<option value="${subject}">${subject}</option>`);
                        });
                        $('#subjectSection').show();
                        subjectSelect.select2({
                            placeholder: "Select or search courses",
                            allowClear: true
                        });
                    });
                }
            });

            // Add custom subject
            $('#addCustomSubject').on('click', function () {
                const customSubject = $('#customSubject').val().trim();
                if (customSubject && !$('#subjectList .subject-tag').filter(function () {
                    return $(this).text() === customSubject;
                }).length && !customSubjects.includes(customSubject)) {
                    $('#subjectList').append(`<span class="subject-tag">${customSubject}</span>`);
                    customSubjects.push(customSubject);
                    $('#customSubject').val('');
                    $('#customSubjects').val(JSON.stringify(customSubjects));
                } else if (customSubject) {
                    alert('Course already added!');
                }
            });

            // Form submission: Collect subjects
            $('#courseForm').on('submit', function () {
                const selected = $('#subjectSelect').val() || [];
                const custom = customSubjects;
                $('#finalSubjects').val(JSON.stringify(selected));
                $('#customSubjects').val(JSON.stringify(custom));
            });
        });
    </script>
     </div>
        <?php include('includes/footer.php') ?>
</body>
</html>