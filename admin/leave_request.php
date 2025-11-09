<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$status  = $_GET['status'] ?? 'PENDING';
$allowed = ['ALL','PENDING','APPROVED','REJECTED'];
if (!in_array($status, $allowed, true)) $status = 'PENDING';

$sql = "SELECT lr.id, lr.date_from, lr.date_to, lr.reason, lr.status, lr.created_at,
               t.name AS teacher_name, t.reg_no
        FROM leave_requests lr
        JOIN teacher t ON t.id = lr.teacher_id";
if ($status !== 'ALL') $sql .= " WHERE lr.status = ?";
$sql .= " ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($sql);
if ($status !== 'ALL') $stmt->bind_param("s", $status);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/includes/header.php';
?>
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Teacher Leave Requests</h4>
    <div class="btn-group">
      <a class="btn btn-sm btn-primary" href="leave_view.php?id=<?= (int)$r['id'] ?>">View</a>
      <a class="btn btn-outline-secondary btn-sm <?= $status==='ALL'?'active':'' ?>" href="?status=ALL">All</a>
      <a class="btn btn-outline-warning  btn-sm <?= $status==='PENDING'?'active':'' ?>" href="?status=PENDING">Pending</a>
      <a class="btn btn-outline-success  btn-sm <?= $status==='APPROVED'?'active':'' ?>" href="?status=APPROVED">Approved</a>
      <a class="btn btn-outline-danger   btn-sm <?= $status==='REJECTED'?'active':'' ?>" href="?status=REJECTED">Rejected</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Teacher</th>
            <th>Reg No</th>
            <th>Dates</th>
            <th>Status</th>
            <th>Requested</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center py-4">No requests.</td></tr>
        <?php else: foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['teacher_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['reg_no'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['date_from']) ?> → <?= htmlspecialchars($r['date_to']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td><a class="btn btn-sm btn-primary" href="leave_view.php?id=<?= (int)$r['id'] ?>">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
