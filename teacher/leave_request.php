<?php
// Enable errors during setup (you can disable later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['teacher_id'])) { header("Location: t_login.php"); exit; }

/* ----------------------------------------------------------------
   Resolve teacher identity (id or reg_no), load subject options,
   then handle submit and list prior requests.
-----------------------------------------------------------------*/
$teacher_key   = $_SESSION['teacher_id'];   // could be "8" or "r30056"
$teacher_db_id = null;                      // numeric teacher.id
$teacher_name  = 'Teacher';
$my_subjects   = [];

/* 1) Resolve numeric id + basic info and CSV subjects (NO get_result used) */
if (ctype_digit((string)$teacher_key)) {
    // session has numeric id
    $teacher_db_id = (int)$teacher_key;
    $q = $conn->prepare("SELECT name, subjects FROM teacher WHERE id = ? LIMIT 1");
    $q->bind_param("i", $teacher_db_id);
    $q->execute();
    $q->bind_result($name, $csv);
    if ($q->fetch()) {
        $teacher_name = $name ?: 'Teacher';
        $csv = trim((string)$csv);
        if ($csv !== '') {
            foreach (explode(',', $csv) as $s) {
                $s = trim($s);
                if ($s !== '') $my_subjects[] = $s;
            }
        }
    }
    $q->close();
} else {
    // session has reg_no
    $q = $conn->prepare("SELECT id, name, subjects FROM teacher WHERE reg_no = ? LIMIT 1");
    $q->bind_param("s", $teacher_key);
    $q->execute();
    $q->bind_result($tid, $name, $csv);
    if ($q->fetch()) {
        $teacher_db_id = (int)$tid;
        $teacher_name  = $name ?: 'Teacher';
        $csv = trim((string)$csv);
        if ($csv !== '') {
            foreach (explode(',', $csv) as $s) {
                $s = trim($s);
                if ($s !== '') $my_subjects[] = $s;
            }
        }
    }
    $q->close();
}

if (!$teacher_db_id) { die("Teacher account not resolved. Please re-login."); }

/* 2) Fallback to schedule if CSV empty (auto-detect subject column) */
if (!$my_subjects) {
    $col = null;
    foreach (['subject','subjects','subject_name'] as $c) {
        $chk = $conn->query("SHOW COLUMNS FROM schedule LIKE '{$c}'");
        if ($chk && $chk->num_rows) { $col = $c; break; }
    }
    if ($col) {
        $sql = "SELECT DISTINCT {$col} FROM schedule WHERE teacher_id = ?";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param("i", $teacher_db_id);
            $st->execute();
            $st->bind_result($subj);
            while ($st->fetch()) {
                $s = trim((string)$subj);
                if ($s !== '') $my_subjects[] = $s;
            }
            $st->close();
        }
    }
}

/* 3) Handle submission (save selected subjects too) */
$errors  = [];

// NOTE: we no longer use $success directly; we set a flash and redirect on success.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from   = $_POST['date_from'] ?? '';
    $to     = $_POST['date_to'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    // Build CSV of selected subjects (keep only ones we offered)
    $subjects_csv = '';
    if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
        $subjects_csv = implode(',', array_intersect($my_subjects, array_map('trim', $_POST['subjects'])));
    }

    if (!$from || !$to) { $errors[] = "Please select both start and end dates."; }
    if ($from && $to && strtotime($from) > strtotime($to)) { $errors[] = "Start date must be before end date."; }
    if (strlen($reason) < 5) { $errors[] = "Reason must be at least 5 characters."; }

    if (!$errors) {
        $stmt = $conn->prepare(
            "INSERT INTO leave_requests (teacher_id, date_from, date_to, reason, subjects)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issss", $teacher_db_id, $from, $to, $reason, $subjects_csv);
        if ($stmt->execute()) {
            // PRG: set flash + redirect so refresh doesn't re-post
            $_SESSION['flash_success'] = "Leave request submitted.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

/* 4) Fetch my requests (NO get_result) */
$requests = [];
if ($rq = $conn->prepare(
    "SELECT id, date_from, date_to, reason, subjects, status, admin_comment, created_at
     FROM leave_requests
     WHERE teacher_id = ?
     ORDER BY created_at DESC"
)) {
    $rq->bind_param("i", $teacher_db_id);
    $rq->execute();
    $rq->bind_result($rid,$rfrom,$rto,$rreason,$rsubs,$rstatus,$rcomment,$rcreated);
    while ($rq->fetch()) {
        $requests[] = [
            'id'=>$rid,
            'date_from'=>$rfrom,
            'date_to'=>$rto,
            'reason'=>$rreason,
            'subjects'=>$rsubs,
            'status'=>$rstatus,
            'admin_comment'=>$rcomment,
            'created_at'=>$rcreated,
        ];
    }
    $rq->close();
}

include __DIR__ . "/includes/header.php";
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  :root{
    --bg:#0b1020;
    --card:#ffffff;
    --muted:#6b7280;
    --ink:#0f172a;
    --ink-2:#1f2937;
    --primary:#2563eb;
    --primary-2:#4f46e5;
    --ring:rgba(37,99,235,.18);
    --ok:#16a34a;
    --warn:#d97706;
    --bad:#dc2626;
    --chip:#f1f5f9;
  }

  body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif; }

  /* Page header */
  .hero {
    background: radial-gradient(1200px 500px at 10% -10%, rgba(79,70,229,.20), transparent 55%),
                radial-gradient(1200px 500px at 90% -10%, rgba(37,99,235,.18), transparent 55%);
    border-radius: 20px;
    padding: 28px 28px 18px;
    margin-bottom: 18px;
  }
  .page-title { font-weight: 800; letter-spacing: .2px; color: var(--ink); }
  .subtitle { color: var(--muted); font-weight: 500; }

  /* Cards */
  .card { border: 0; border-radius: 18px; box-shadow: 0 12px 30px rgba(2,6,23,.06); overflow: hidden; }
  .card-header { border-bottom: 1px solid #eef2f7; background: linear-gradient(180deg,#fff, #fafbff); padding: 16px 20px; }
  .card-header strong { letter-spacing: .3px; color: var(--ink-2); }

  /* Inputs */
  .form-control, .form-select, textarea {
    border-radius: 12px !important;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 0 rgba(2,6,23,.02);
  }
  .form-control:focus, .form-select:focus, textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px var(--ring);
  }

  /* Button */
  .btn-primary {
    background: linear-gradient(90deg, var(--primary), var(--primary-2));
    border: 0;
    border-radius: 12px;
    padding: 10px 18px;
    font-weight: 600;
    box-shadow: 0 10px 22px rgba(79,70,229,.25);
  }
  .btn-primary:hover { filter: brightness(1.05); }

  /* Status badges */
  .badge-soft { padding: .45rem .7rem; border-radius: 999px; font-weight: 600; letter-spacing:.2px; }
  .badge-pending  { background: #fff7ed; color: var(--warn);  border: 1px solid #ffedd5; }
  .badge-approved { background: #ecfdf5; color: var(--ok);    border: 1px solid #d1fae5; }
  .badge-rejected { background: #fef2f2; color: var(--bad);   border: 1px solid #fee2e2; }

  /* Subject chips */
  .chip {
    display:inline-block; background: var(--chip); color: #0f172a; border:1px solid #e2e8f0;
    padding:.25rem .55rem; border-radius: 999px; font-size:.78rem; margin:1px 4px 1px 0; white-space:nowrap;
  }

  /* Table */
  .table thead th { color: var(--muted); font-weight: 700; text-transform: uppercase; font-size: .75rem; letter-spacing: .5px; }
  .table tbody tr { border-color:#f0f3f8; }
  .table tbody td { vertical-align: middle; }
  .muted { color: var(--muted); }

  /* Page container */
  .page-wrap { max-width: 1100px; margin: 0 auto; }
</style>
  <div class="container-fluid py-4 page-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="page-title mb-0">Leave Requests</h2>
      <div class="muted">Signed in as <strong><?= htmlspecialchars($teacher_name) ?></strong></div>
    </div>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger mb-3">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header bg-white">
      <strong>New leave request</strong>
    </div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">From</label>
          <input type="date" name="date_from" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">To</label>
          <input type="date" name="date_to" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" rows="2" placeholder="Explain why you need leave" required></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">Subjects affected (Ctrl/⌘ to select multiple)</label>
          <!-- Visible multi-select; shows options even without clicking -->
          <select name="subjects[]" class="form-select" multiple size="5">
            <?php if ($my_subjects): ?>
              <?php foreach ($my_subjects as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
              <?php endforeach; ?>
            <?php else: ?>
              <option disabled>(No subjects found — ask admin to assign subjects to your profile)</option>
            <?php endif; ?>
          </select>
          <div class="form-text">
            <?= $my_subjects ? "Hold Ctrl (or ⌘ on Mac) to select multiple." : "Submit is allowed without subjects if needed." ?>
          </div>
        </div>

        <div class="col-12">
          <button class="btn btn-primary px-4">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-white">
      <strong>My previous requests</strong>
    </div>
    <div class="card-body table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th style="width:200px">Dates</th>
            <th>Reason</th>
            <th style="width:220px">Subjects</th>
            <th style="width:140px">Status</th>
            <th style="width:240px">Admin Comment</th>
            <th style="width:180px">Requested</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$requests): ?>
            <tr><td colspan="7" class="text-center py-4">No requests yet.</td></tr>
          <?php else: $i=1; foreach($requests as $r): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($r['date_from']) ?> → <?= htmlspecialchars($r['date_to']) ?></td>
              <td><?= nl2br(htmlspecialchars($r['reason'])) ?></td>
              <td><?= $r['subjects'] ? htmlspecialchars($r['subjects']) : '—' ?></td>
              <td>
                <?php
                  $cls = 'badge-soft badge-pending';
                  if ($r['status'] === 'APPROVED') $cls = 'badge-soft badge-approved';
                  if ($r['status'] === 'REJECTED') $cls = 'badge-soft badge-rejected';
                ?>
                <span class="<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></span>
              </td>
              <td><?= $r['admin_comment'] ? nl2br(htmlspecialchars($r['admin_comment'])) : '—' ?></td>
              <td class="muted"><?= htmlspecialchars($r['created_at']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . "/includes/footer.php"; ?>
