<?php
// TEMP: show all errors so we can see what's wrong
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include('includes/db_connect.php');

// ---------------- Handle approve / reject actions ----------------
if (isset($_GET['action'], $_GET['id']) && ctype_digit($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $action = $_GET['action'];
    $msg    = '';

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE teacher SET tick = 1, available = 1 WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $msg = "Teacher approved successfully.";
        } else {
            $msg = "Error approving teacher: " . $stmt->error;
        }
        $stmt->close();

    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM teacher WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $msg = "Teacher registration rejected and removed.";
        } else {
            $msg = "Error rejecting teacher: " . $stmt->error;
        }
        $stmt->close();
    }

    header("Location: teacher_approvals.php?msg=" . urlencode($msg));
    exit();
}

// ---------------- Fetch pending teachers ----------------
$sql = "
    SELECT 
        t.id,
        t.name,
        t.reg_no,
        t.email,
        t.department,
        s.org_name
    FROM teacher t
    LEFT JOIN sub_admin s ON t.org_id = s.id
    WHERE t.tick = 0
    ORDER BY t.id DESC
";

$pending_teachers = $conn->query($sql);
if ($pending_teachers === false) {
    die("Query error: " . $conn->error);
}
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pending Teacher Approvals</h1>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-info">
      <?= htmlspecialchars($_GET['msg']) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      Teachers awaiting approval
    </div>
    <div class="card-body p-0">
      <table class="table mb-0 table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Reg No</th>
            <th>Email</th>
            <th>Department</th>
            <th>Organization</th>
            <th style="width:160px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($pending_teachers->num_rows > 0): ?>
          <?php $i = 1; while ($row = $pending_teachers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['reg_no']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['department']) ?></td>
              <td><?= htmlspecialchars($row['org_name'] ?? '-') ?></td>
              <td>
                <a href="teacher_approvals.php?action=approve&id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-success"
                   onclick="return confirm('Approve this teacher?');">
                  Approve
                </a>
                <a href="teacher_approvals.php?action=reject&id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Reject and delete this teacher registration?');">
                  Reject
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center py-3">
              No pending teacher registrations.
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include('includes/footer.php'); ?>
