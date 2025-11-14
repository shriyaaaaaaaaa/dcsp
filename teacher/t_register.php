<?php
// Show errors while we debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('includes/db_connect.php');

// Handle AJAX request for faculty data
if (isset($_GET['action']) && $_GET['action'] === 'get_faculties') {
    header('Content-Type: application/json');
    $query = "SELECT DISTINCT faculty FROM courses_subjects WHERE faculty IS NOT NULL ORDER BY faculty ASC";
    $result = $conn->query($query);
    $faculties = [];
    while ($row = $result->fetch_assoc()) {
        $faculties[] = $row['faculty'];
    }
    echo json_encode($faculties);
    exit();
}

$error   = '';
$success = '';

if (!isset($_GET['org_id']) || !is_numeric($_GET['org_id'])) {
    die('<div style="padding:20px; font-size:18px;">Invalid access. Please select your organization from the homepage.</div>');
}

$org_id = (int)$_GET['org_id'];

// Fetch org_name
$stmt = $conn->prepare("SELECT org_name FROM sub_admin WHERE id = ? AND approval = 1");
$stmt->bind_param('i', $org_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('<div style="padding:20px; font-size:18px;">Organization not found or not approved.</div>');
}

$org_data   = $result->fetch_assoc();
$org_name   = $org_data['org_name'];
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $org_id;

// Check if sub-admin table exists (you can keep this if you still want)
$check_table = $conn->query("SHOW TABLES LIKE '$table_slug'");
if ($check_table->num_rows === 0) {
    // You can comment this out if you *never* want to depend on this list
    // die("<div style='padding:20px; font-size:18px;'>⚠️ Waiting for <strong>$org_name</strong> to upload their member list.</div>");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reg_no     = trim($_POST['reg_no'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? ''; // NOTE: use password_hash() in production
    $department = trim($_POST['department'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');

    if (
        $reg_no === '' || $name === '' || $email === '' ||
        $password === '' || $department === '' || $phone === ''
    ) {
        $error = "Please fill in all the fields.";
    } else {
        // OPTIONAL: check if this reg_no already exists to avoid duplicates
        $chk = $conn->prepare("SELECT id FROM teacher WHERE reg_no = ? AND org_id = ? LIMIT 1");
        $chk->bind_param("si", $reg_no, $org_id);
        $chk->execute();
        $chk_res = $chk->get_result();
        if ($chk_res->num_rows > 0) {
            $error = "A teacher with this registration number is already registered.";
    
        } else {
            // Open registration – no teacher-list restriction
            $subjects  = '';  // default empty, admin can add subjects later
            $available = 1;   // 1 = available by default (you can change to 0 if you prefer)

            $insert = $conn->prepare(
                "INSERT INTO teacher (
                    reg_no,
                    name,
                    email,
                    password,
                    department,
                    phone,
                    subjects,
                    org_id,
                    available,
                    tick
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)"
            );

            if (!$insert) {
                die('Prepare failed: ' . $conn->error);
            }

        // 7 strings + 2 integers (org_id, available)
            $insert->bind_param(
                'sssssssii',
                $reg_no,
                $name,
                $email,
                $password,   // later: password_hash(...)
                $department,
                $phone,
                $subjects,
                $org_id,
                $available
            );
            if ($insert->execute()) {
                $success = "✅ Registration successful! You can now log in.";
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Teacher Registration - <?= htmlspecialchars($org_name) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        .register-page {
            min-height: 100vh;
            background: url('img/tregister.jpg') no-repeat center center/cover;
            padding: 30px;
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-card {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            width: 100%;
            max-width: 800px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.6);
            border: none;
            color: #000;
        }

        .form-control::placeholder, .form-select::placeholder {
            color: #333;
        }

        .btn-primary {
            font-weight: bold;
            background-color: #00695c;
            border-color: #00695c;
        }

        .btn-primary:hover {
            background-color: #004d40;
            border-color: #004d40;
        }

        @media (max-width: 768px) {
            .register-card {
                padding: 20px;
            }
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: none;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            color: #000;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #333;
        }
    </style>
</head>
<body>

<div class="register-page">
    <div class="register-card">
        <h3 class="text-center mb-4">
            Teacher Registration for <br>
            <span class="text-primary"><?= htmlspecialchars($org_name) ?></span>
        </h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?><br>
                <a href="t_login.php" class="btn btn-sm btn-outline-primary mt-2">Go to Login</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Registration Number</label>
                <input type="text" name="reg_no" class="form-control"
                       placeholder="Enter your registration number" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Enter your full name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="Enter email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter password" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-select" id="facultySelect" required>
                    <option value="" disabled selected>Select a department</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control"
                       placeholder="Enter phone number" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>

            <div class="text-center mt-3">
                Already have an account?
                <a href="t_login.php" class="text-primary">Login here</a>
            </div>
        </form>
    </div>
</div>

<!-- jQuery (required for Select2) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#facultySelect').select2({
            placeholder: "Search for a department",
            allowClear: true,
            width: '100%'
        });

        $.ajax({
            url: '<?= htmlspecialchars($_SERVER['PHP_SELF']) . "?org_id=" . $org_id . "&action=get_faculties"; ?>',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(function(faculty) {
                    $('#facultySelect').append(
                        $('<option>', {
                            value: faculty,
                            text: faculty
                        })
                    );
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching faculty data:', error);
                alert('Failed to load department data. Please try again.');
            }
        });
    });
</script>

</body>
</html>
