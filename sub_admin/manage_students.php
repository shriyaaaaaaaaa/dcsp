<?php
include('includes/db_connect.php');
include('includes/header.php');
if (!isset($_SESSION['sub_admin_id'])) {
    header('Location: sa_login.php');
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];
$table_slug = str_replace(' ', '_', strtolower($org_name)) . '_' . $sub_admin_id;

// Fetch students for the current sub-admin
$student_query = $conn->prepare("SELECT reg_no, name, email FROM student WHERE org_id = ?");
$student_query->bind_param("i", $sub_admin_id);
$student_query->execute();
$students = $student_query->get_result();
$student_query->close();

if (isset($_POST['reg_no'])) {
    $reg_no = $_POST['reg_no'];
    $sub_admin_id = $_SESSION['sub_admin_id'];
    $table_slug = str_replace(' ', '_', strtolower($org_name)) . '_' . $sub_admin_id;

    // Delete from student table
    $stmt = $conn->prepare("DELETE FROM student WHERE reg_no = ? AND org_id = ?");
    $stmt->bind_param("si", $reg_no, $sub_admin_id);
    $stmt->execute();
    $stmt->close();

    // Delete from table_slug table
    $stmt_slug = $conn->prepare("DELETE FROM `$table_slug` WHERE student_reg = ?");
    $stmt_slug->bind_param("s", $reg_no);
    $stmt_slug->execute();
    $stmt_slug->close();

    header('Location: manage_students.php');
    exit;
}


// include('includes/sidebar.php');
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($org_name); ?>-> Students</h1>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle text-center" id="<?php echo htmlspecialchars($table_slug); ?>">
      <thead class="table-dark">
        <tr>
          <th>Reg No</th>
          <th>Name</th>
          <th>Email</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($student = $students->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
            <td><?php echo htmlspecialchars($student['name']); ?></td>
            <td><?php echo htmlspecialchars($student['email']); ?></td>
            <td>
              <form action="manage_students.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($student['name']); ?>?');">
                <input type="hidden" name="reg_no" value="<?php echo htmlspecialchars($student['reg_no']); ?>">
                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                  <span class="material-icons">delete</span>
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        <?php if ($students->num_rows === 0): ?>
          <tr>
            <td colspan="5" class="text-muted">No Students found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
</main>
</div>
<?php include('includes/footer.php'); ?>
</body>
</html>