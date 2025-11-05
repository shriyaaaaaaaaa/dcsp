<?php
include('includes/header.php');

// Ensure org_name and sub_admin_id are available
if (empty($org_name) || empty($_SESSION['sub_admin_id'])) {
    $_SESSION['error'] = "Error: Organization or sub-admin ID not found.";
    header("Location: sa_dashboard.php");
    exit();
}

// Table name using session data
$table_name = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_" . $_SESSION['sub_admin_id'];

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type']; // 'teacher' or 'student'
    $id = (int)$_GET['id'];
    $conn->query("SET SESSION sql_mode = ''");
    if ($type === 'teacher') {
        $conn->query("DELETE FROM `$table_name` WHERE id = $id AND teacher_reg IS NOT NULL LIMIT 1");
    } elseif ($type === 'student') {
        $conn->query("DELETE FROM `$table_name` WHERE id = $id AND student_reg IS NOT NULL LIMIT 1");
    }
    header("Location: member_list.php"); // Refresh page after deletion
    exit();
}

$conn->query("SET SESSION sql_mode = ''"); // Avoid strict mode issues
$teachers = $conn->query("SELECT id, teacher_reg FROM `$table_name` WHERE teacher_reg IS NOT NULL");
$students = $conn->query("SELECT id, student_reg FROM `$table_name` WHERE student_reg IS NOT NULL");
?>

<!-- Link to External CSS -->
<link rel="stylesheet" href="styles/members_list.css">

<div class="container member-section">
    <h2 class="text-center mb-4">Members of <?php echo htmlspecialchars($org_name); ?></h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Teachers -->
        <div class="col-md-6">
            <div class="member-card">
                <h3>List of Teachers</h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Teacher Reg</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $teachers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['teacher_reg']); ?></td>
                                <td class="action-cell">
                                    <a href="?delete=1&type=teacher&id=<?php echo htmlspecialchars($row['id']); ?>" 
                                       class="delete-btn" onclick="return confirm('Are you sure you want to delete this teacher?');">🔴</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($teachers->num_rows == 0): ?>
                            <tr><td colspan="2">No teachers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-actions">
                    <a href="member.php" class="btn btn-primary btn-sm me-2">➕ Add</a>
                </div>
        </div>

        <!-- Students -->
        <div class="col-md-6">
            <div class="member-card">
                <h3>List of Students</h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student Reg</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_reg']); ?></td>
                                <td class="action-cell">
                                    <a href="?delete=1&type=student&id=<?php echo htmlspecialchars($row['id']); ?>" 
                                       class="delete-btn" onclick="return confirm('Are you sure you want to delete this student?');">🔴</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($students->num_rows == 0): ?>
                            <tr><td colspan="2">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-actions">
                    <a href="member.php" class="btn btn-primary btn-sm me-2">➕ Add</a>
                </div>
        </div>
    </div>

    <!-- Done Button -->
    <div class="text-center mt-4">
        <a href="sa_dashboard.php" class="btn btn-success px-5">Done</a>
    </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>