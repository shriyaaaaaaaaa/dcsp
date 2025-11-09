<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/includes/db_connect.php';
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: leave_request.php"); exit; }

$sql = "SELECT lr.*, t.name AS teacher_name, t.reg_no, t.email, t.department
        FROM leave_requests lr
        JOIN teacher t ON t.id = lr.teacher_id
        WHERE lr.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$req = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$req) { header("Location: leave_request.php"); exit; }

include __DIR__ . '/includes/header.php';
?>
<div class="container-fluid p-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Leave Request #<?= (int)$req['id']; ?></h5>
      <span class="badge bg-<?= $req['status']==='PENDING'?'warning':($req['status']==='APPROVED'?'success':'danger') ?>">
        <?= htmlspecialchars($req['status']) ?>
      </span>
    </div>

    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6"><strong>Teacher:</strong> <?= htmlspecialchars($req['teacher_name']) ?> (<?= htmlspecialchars($req['reg_no']) ?>)</div>
        <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($req['email']) ?></div>
        <div class="col-md-6"><strong>Department:</strong> <?= htmlspecialchars($req['department']) ?></div>
        <div class="col-md-6"><strong>Dates:</strong> <?= htmlspecialchars($req['date_from']) ?> → <?= htmlspecialchars($req['date_to']) ?></div>
        <div class="col-12 mt-2"><strong>Reason:</strong><br><?= nl2br(htmlspecialchars($req['reason'])) ?></div>
        <?php if (!empty($req['subjects'])): ?>
          <div class="col-12 mt-2"><strong>Subjects:</strong> <?= htmlspecialchars($req['subjects']) ?></div>
        <?php endif; ?>
      </div>

      <h6 class="mb-3">Admin Decision</h6>
      <form method="post" action="handle_leave.php" class="row g-3">
        <input type="hidden" name="id" value="<?= (int)$req['id'] ?>">
        <div class="col-12">
          <label class="form-label">Comment (optional)</label>
          <textarea name="admin_comment" class="form-control" rows="3"><?= htmlspecialchars($req['admin_comment'] ?? '') ?></textarea>
        </div>

        <div class="col-12 d-flex gap-2">
          <button class="btn btn-success"
                  name="action"
                  value="APPROVE"
                  <?= $req['status']!=='PENDING' ? 'disabled' : '' ?>>Approve</button>

          <button class="btn btn-danger"
                  name="action"
                  value="REJECT"
                  <?= $req['status']!=='PENDING' ? 'disabled' : '' ?>>Reject</button>

          <?php if ($req['status']!=='PENDING'): ?>
            <button class="btn btn-outline-secondary" name="action" value="RESET">Reset to Pending</button>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card-footer text-muted">
      Requested: <?= htmlspecialchars($req['created_at']) ?>
      <?php if (!empty($req['updated_at'])): ?> | Updated: <?= htmlspecialchars($req['updated_at']) ?><?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
