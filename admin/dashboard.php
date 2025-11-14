<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include('includes/db_connect.php');

// -------------------- Counters --------------------
$teacher_count         = 0;
$student_count         = 0;
$pending_org_count     = 0;
$pending_teacher_count = 0;

// Total teachers
if ($stmt = $conn->prepare("SELECT COUNT(*) AS teacher_count FROM teacher")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $teacher_count = (int)$res->fetch_assoc()['teacher_count'];
    $stmt->close();
}

// Total students
if ($stmt = $conn->prepare("SELECT COUNT(*) AS student_count FROM student")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $student_count = (int)$res->fetch_assoc()['student_count'];
    $stmt->close();
}

// Pending org approvals (sub_admin.approval = 0)
if ($stmt = $conn->prepare("SELECT COUNT(*) AS pending_count FROM sub_admin WHERE approval = 0")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $pending_org_count = (int)$res->fetch_assoc()['pending_count'];
    $stmt->close();
}

// Pending teacher approvals (teacher.tick = 0)
if ($stmt = $conn->prepare("SELECT COUNT(*) AS pending_teacher_count FROM teacher WHERE tick = '0'")) {
    $stmt->execute();
    $res = $stmt->get_result();
    $pending_teacher_count = (int)$res->fetch_assoc()['pending_teacher_count'];
    $stmt->close();
}

// -------------------- Lists for tables --------------------

// Latest pending teachers (small table)
$pending_teachers = $conn->query(
    "SELECT id, name, reg_no, email, department
     FROM teacher
     WHERE tick = '0'
     ORDER BY id DESC
     LIMIT 5"
);

// All approved teachers
$all_teachers = $conn->query(
    "SELECT name, reg_no, email, department, phone
     FROM teacher
     WHERE tick = '1'
     ORDER BY name ASC"
);

// All approved students
$all_students = $conn->query(
    "SELECT name, reg_no, email, class
     FROM student
     WHERE tick = 1
     ORDER BY name ASC"
);
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<style>
  /* small dashboard polish */
  .dashboard-card {
      border-radius: 14px;
      box-shadow: 0 12px 25px rgba(15, 23, 42, 0.10);
  }

  .table-wrap {
      max-height: 420px;
      overflow-y: auto;
  }

  .table-wrap thead th {
      position: sticky;
      top: 0;
      background: #f8fafc;
      z-index: 5;
  }

  .table-hover tbody tr:hover {
      background-color: #eef2ff;
  }

  .table-search-input {
      max-width: 220px;
  }
</style>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Dashboard</h1>
  </div>

  <!-- Cards row -->
  <div class="row">
    <div class="col-md-3">
      <div class="card text-white bg-primary mb-3 dashboard-card">
        <div class="card-body">
          <h5 class="card-title">Total Teachers</h5>
          <p class="card-text h4 mb-0"><?= htmlspecialchars($teacher_count) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-white bg-success mb-3 dashboard-card">
        <div class="card-body">
          <h5 class="card-title">Total Students</h5>
          <p class="card-text h4 mb-0"><?= htmlspecialchars($student_count) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <a href="pending_approvals.php" class="text-decoration-none">
        <div class="card text-white bg-warning mb-3 dashboard-card">
          <div class="card-body">
            <h5 class="card-title">Pending Orgs</h5>
            <p class="card-text h4 mb-0"><?= htmlspecialchars($pending_org_count) ?></p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-3">
      <a href="teacher_approvals.php" class="text-decoration-none">
        <div class="card text-white bg-danger mb-3 dashboard-card">
          <div class="card-body">
            <h5 class="card-title">Pending Teachers</h5>
            <p class="card-text h4 mb-0"><?= htmlspecialchars($pending_teacher_count) ?></p>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Latest pending teachers table -->
  <div class="card mt-4">
    <div class="card-header">
    Pending Teacher Registrations
    </div>
    <div class="card-body p-0">
      <table class="table mb-0 table-hover">
        <thead>
          <tr>
            <th>S.N</th>
            <th>Name</th>
            <th>Reg No</th>
            <th>Email</th>
            <th>Department</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($pending_teachers && $pending_teachers->num_rows > 0): ?>
          <?php $i = 1; while ($row = $pending_teachers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['reg_no']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['department']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center py-3">No pending teacher registrations.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- All teachers & students -->
  <div class="row mt-4">
    <!-- All Teachers -->
    <div class="col-md-6 mb-4">
      <div class="card dashboard-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><strong>All Teachers</strong></span>
          <input type="text" id="teacherSearch" class="form-control form-control-sm table-search-input" placeholder="Search teachers...">
        </div>
        <div class="card-body p-0 table-wrap">
          <table class="table table-hover mb-0" id="teacherTable">
            <thead>
              <tr>
                <th>S.N</th>
                <th>Name</th>
                <th>Reg No</th>
                <th>Email</th>
                <th>Department</th>
                <th>Phone</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($all_teachers && $all_teachers->num_rows > 0): ?>
              <?php $i = 1; while ($t = $all_teachers->fetch_assoc()): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($t['name']) ?></td>
                  <td><?= htmlspecialchars($t['reg_no']) ?></td>
                  <td><?= htmlspecialchars($t['email']) ?></td>
                  <td><?= htmlspecialchars($t['department']) ?></td>
                  <td><?= htmlspecialchars($t['phone']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center py-3">No teachers found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- All Students -->
    <div class="col-md-6 mb-4">
      <div class="card dashboard-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><strong>All Students</strong></span>
          <input type="text" id="studentSearch" class="form-control form-control-sm table-search-input" placeholder="Search students...">
        </div>
        <div class="card-body p-0 table-wrap">
          <table class="table table-hover mb-0" id="studentTable">
            <thead>
              <tr>
                <th>S.N</th>
                <th>Name</th>
                <th>Reg No</th>
                <th>Email</th>
                <th>Class</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($all_students && $all_students->num_rows > 0): ?>
              <?php $i = 1; while ($s = $all_students->fetch_assoc()): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td><?= htmlspecialchars($s['reg_no']) ?></td>
                  <td><?= htmlspecialchars($s['email']) ?></td>
                  <td><?= htmlspecialchars($s['class']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center py-3">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  // Simple client-side search for teachers and students
  function setupTableSearch(inputId, tableId) {
      const input = document.getElementById(inputId);
      const table = document.getElementById(tableId);
      if (!input || !table) return;

      input.addEventListener('keyup', function () {
          const filter = this.value.toLowerCase();
          const rows = table.querySelectorAll('tbody tr');
          rows.forEach(row => {
              const text = row.textContent.toLowerCase();
              row.style.display = text.includes(filter) ? '' : 'none';
          });
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
      setupTableSearch('teacherSearch', 'teacherTable');
      setupTableSearch('studentSearch', 'studentTable');
  });
</script>

<?php include('includes/footer.php'); ?>
