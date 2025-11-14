<?php
include('includes/header.php'); // includes db_connect.php & session handling

// Ensure sub_admin is logged in
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];

// --- Get org_name for this sub_admin ---
$stmt = $conn->prepare("SELECT org_name FROM sub_admin WHERE id = ?");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$stmt->bind_result($org_name);
$stmt->fetch();
$stmt->close();

// Dynamic member table name for this organization
$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

// --- Teacher & Student counts from member table ---
$total_teachers = 0;
$total_students = 0;
$total_members  = 0;

if (!empty($table_slug)) {
    $checkTable = $conn->query("SHOW TABLES LIKE '$table_slug'");
    if ($checkTable && $checkTable->num_rows > 0) {
        // Make sure columns exist
        $colTeacher = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'teacher_reg'");
        $colStudent = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'student_reg'");

        if ($colTeacher && $colTeacher->num_rows > 0 && $colStudent && $colStudent->num_rows > 0) {
            // Count teachers and students separately
            $query = "
                SELECT
                    (SELECT COUNT(*) FROM `$table_slug` WHERE teacher_reg IS NOT NULL AND teacher_reg <> '') AS total_teachers,
                    (SELECT COUNT(*) FROM `$table_slug` WHERE student_reg IS NOT NULL AND student_reg <> '') AS total_students
            ";
            $result = $conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                $total_teachers = (int)($row['total_teachers'] ?? 0);
                $total_students = (int)($row['total_students'] ?? 0);
                $total_members  = $total_teachers + $total_students;
            }
        }
    }
}

// --- Total Comments for this organization ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_comments FROM comment WHERE org_id = ?");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_comments = (int)($row['total_comments'] ?? 0);
$stmt->close();
?>

<!-- Hero / banner -->
<div class="text-center mb-4"
     style="background-image: url('img/dcspsa.jpg'); background-size: cover; background-position: center;
            height: 100vh; width: 100%; margin: 0; padding: 0;">
</div>

<main class="col-md-10 mx-auto px-4 py-4">
    <div class="row row-cols-1 row-cols-md-3 g-3 justify-content-center">

        <!-- Total Teachers -->
        <div class="col">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="manage_teachers.php" class="text-white text-decoration-none">Teachers</a>
                    </h5>
                    <p class="card-text fs-2">
                        <?= htmlspecialchars($total_teachers) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="col">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="manage_students.php" class="text-white text-decoration-none">Students</a>
                    </h5>
                    <p class="card-text fs-2">
                        <?= htmlspecialchars($total_students) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Comments -->
        <div class="col">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="comments.php" class="text-white text-decoration-none">Comments</a>
                    </h5>
                    <p class="card-text fs-2">
                        <?= htmlspecialchars($total_comments) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- OPTIONAL: extra row for Total Members & Output JSON, keep if useful -->
    <div class="row row-cols-1 row-cols-md-2 g-3 justify-content-center mt-4">

        <!-- Total Members -->
        <div class="col">
            <div class="card text-white bg-dark h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="member_list.php" class="text-white text-decoration-none">Total Members</a>
                    </h5>
                    <p class="card-text fs-2">
                        <?= htmlspecialchars($total_members) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Output JSON -->
        <div class="col">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="output.php" class="text-white text-decoration-none">Output JSON</a>
                    </h5>
                    <p class="card-text fs-2">
                        0
                        <!-- later you can show actual number of generated timetables -->
                    </p>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include('includes/footer.php'); ?>
</body>
</html>
