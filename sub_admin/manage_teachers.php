<?php
include('includes/db_connect.php');
include('includes/header.php');

if (!isset($_SESSION['sub_admin_id'])) {
    header('Location: sa_login.php');
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];
$table_slug = str_replace(' ', '_', strtolower($org_name)) . '_' . $sub_admin_id;

// Fetch teachers
$teacher_query = $conn->prepare("SELECT reg_no, email, name, department, subjects, available, phone FROM teacher WHERE org_id = ?");
$teacher_query->bind_param("i", $sub_admin_id);
$teacher_query->execute();
$teachers = $teacher_query->get_result();
$teacher_query->close();

// Handle deletion
if (isset($_POST['reg_no'])) {
    $reg_no = $_POST['reg_no'];

    $stmt = $conn->prepare("DELETE FROM teacher WHERE reg_no = ? AND org_id = ?");
    $stmt->bind_param("si", $reg_no, $sub_admin_id);
    $stmt->execute();
    $stmt->close();

    $stmt_slug = $conn->prepare("DELETE FROM `$table_slug` WHERE teacher_reg = ?");
    $stmt_slug->bind_param("s", $reg_no);
    $stmt_slug->execute();
    $stmt_slug->close();

    header('Location: manage_teachers.php');
    exit;
}
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($org_name); ?>-> Teachers</h1>
  </div>

      <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered text-center">
          <thead class="table-dark">
            <tr>
              <th>Reg No</th>
              <th>Name</th>
              <th>Email</th>
              <th>Department</th>
              <th>Subjects</th>
              <th>Available</th>
              <th>Phone</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($teacher = $teachers->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($teacher['reg_no']); ?></td>
                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                <td><?php echo htmlspecialchars($teacher['subjects']); ?></td>
                <td><?php echo $teacher['available'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                <td>
                  <form method="POST" onsubmit="return confirm('Delete <?php echo htmlspecialchars($teacher['name']); ?>?');">
                    <input type="hidden" name="reg_no" value="<?php echo htmlspecialchars($teacher['reg_no']); ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($teachers->num_rows === 0): ?>
              <tr><td colspan="8" class="text-muted">No teachers found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
 </div><!-- END .page-wrapper -->

<?php include('includes/footer.php'); ?>
</body>
</html>