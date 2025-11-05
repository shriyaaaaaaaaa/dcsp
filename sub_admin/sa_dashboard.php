<?php
include('includes/header.php'); // includes db_connect.php & session handling

// Ensure sub_admin is logged in
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];

// --- Total Members Calculation ---
$stmt = $conn->prepare("SELECT org_name FROM sub_admin WHERE id = ?");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$stmt->bind_result($org_name);
$stmt->fetch();
$stmt->close();

$table_slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $sub_admin_id;

$total_members = 0;
if (!empty($table_slug)) {
    $checkTable = $conn->query("SHOW TABLES LIKE '$table_slug'");
    if ($checkTable && $checkTable->num_rows > 0) {
        $colTeacher = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'teacher_reg'");
        $colStudent = $conn->query("SHOW COLUMNS FROM `$table_slug` LIKE 'student_reg'");
        if ($colTeacher && $colTeacher->num_rows > 0 && $colStudent && $colStudent->num_rows > 0) {
            $query = "
                SELECT 
                    (SELECT COUNT(*) FROM `$table_slug` WHERE teacher_reg IS NOT NULL) + 
                    (SELECT COUNT(*) FROM `$table_slug` WHERE student_reg IS NOT NULL) AS total_members
            ";
            $result = $conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                $total_members = $row['total_members'] ?? 0;
            }
        }
    }
}

// --- Total Comments ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_comments FROM comment WHERE org_id = ?");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_comments = $row['total_comments'] ?? 0;
$stmt->close();


?>
<div class="text-center mb-4" style="background-image: url('img/dcspsa.jpg'); background-size: cover; background-position: center; height: 100vh; width: 100%; margin: 0; padding: 0;"></div>
<main class="col-md-10 mx-auto px-4 py-4">
    <div class="row row-cols-1 row-cols-md-3 g-3 justify-content-center">
        <!-- Total Members -->
        <div class="col">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="member_list.php" class="text-white text-decoration-none">Total Members</a>
                    </h5>
                    <p class="card-text fs-2"><?= htmlspecialchars($total_members) ?></p>
                </div>
            </div>
        </div>

        <!-- Output JSON -->
        <div class="col">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <h5 class="card-title">
                        <a href="output.php" class="text-white text-decoration-none">Output JSON</a>
                    </h5>
                    <p class="card-text fs-2">0</p>
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
                    <p class="card-text fs-2"><?= htmlspecialchars($total_comments) ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>
</body>
</html>
