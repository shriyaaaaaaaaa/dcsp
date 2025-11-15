<?php
// sub_admin/sa_leave_request.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

// Use organization_id from session if you ever set it,
// otherwise default to sub_admin_id (which matches teacher.organization_id)
$org_id = !empty($_SESSION['organization_id'])
    ? (int) $_SESSION['organization_id']
    : (int) $_SESSION['sub_admin_id'];

$status_filter = $_GET['status'] ?? 'ALL';

/* ---------- 1) Handle Approve / Reject ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $req_id = (int) $_POST['request_id'];
    $action = ($_POST['action'] === 'APPROVED') ? 'APPROVED' : 'REJECTED';

    // IMPORTANT: check via teacher.organization_id, not leave_requests.organization_id
    $sql = "UPDATE leave_requests lr
            JOIN teacher t ON lr.teacher_id = t.id
            SET lr.status = ?
            WHERE lr.id = ? AND t.organization_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $action, $req_id, $org_id);
    $stmt->execute();
    $stmt->close();

    header("Location: sa_leave_request.php?status=" . urlencode($status_filter));
    exit;
}

/* ---------- 2) Load leave requests for THIS subadmin ---------- */
$whereStatus = "";
$params = [$org_id];
$types  = "i";

if ($status_filter !== 'ALL') {
    $whereStatus = " AND lr.status = ?";
    $types      .= "s";
    $params[]    = $status_filter;
}

$sql = "SELECT lr.id,
               t.name AS teacher_name,
               t.reg_no AS reg_no,
               lr.date_from,
               lr.date_to,
               lr.reason,
               lr.status,
               lr.created_at
        FROM leave_requests lr
        JOIN teacher t ON lr.teacher_id = t.id
        WHERE t.org_id = ? $whereStatus
        ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

/* ---------- 3) Use same header as subadmin dashboard ---------- */
include __DIR__ . "/includes/header.php";
?>
<div class="container mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Teacher Leave Requests</h3>
        <div>
            <a href="sa_leave_request.php?status=ALL" class="btn btn-sm btn-primary<?= $status_filter==='ALL' ? ' active' : '' ?>">All</a>
            <a href="sa_leave_request.php?status=PENDING" class="btn btn-sm btn-warning<?= $status_filter==='PENDING' ? ' active' : '' ?>">Pending</a>
            <a href="sa_leave_request.php?status=APPROVED" class="btn btn-sm btn-success<?= $status_filter==='APPROVED' ? ' active' : '' ?>">Approved</a>
            <a href="sa_leave_request.php?status=REJECTED" class="btn btn-sm btn-danger<?= $status_filter==='REJECTED' ? ' active' : '' ?>">Rejected</a>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Teacher</th>
                <th>Reg No</th>
                <th>Dates</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="8" class="text-center">No leave requests found.</td></tr>
        <?php else: $i = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                <td><?= htmlspecialchars($row['reg_no']) ?></td>
                <td><?= htmlspecialchars($row['date_from']) ?> → <?= htmlspecialchars($row['date_to']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <?php if ($row['status'] === 'PENDING'): ?>
                        <form method="post" style="display:inline-block">
                            <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                            <button name="action" value="APPROVED" class="btn btn-sm btn-success">
                                Approve
                            </button>
                        </form>
                        <form method="post" style="display:inline-block">
                            <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                            <button name="action" value="REJECTED" class="btn btn-sm btn-danger">
                                Reject
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">No action</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>
<?php
include __DIR__ . "/includes/footer.php"; // if you have it
?>
