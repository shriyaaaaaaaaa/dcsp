<?php
// Protect page and load layout
include 'includes/header.php';
include 'includes/db_connect.php';
include 'includes/sidebar.php';

// Fetch pending and approved organizations (sub_admin entries)
$pending_orgs = $conn->query("
    SELECT id, org_name, email, address, org_type, created_at
    FROM sub_admin
    WHERE approval = 0
    ORDER BY created_at DESC
");

$approved_orgs = $conn->query("
    SELECT id, org_name, email, address, org_type, created_at
    FROM sub_admin
    WHERE approval = 1
    ORDER BY org_name ASC
");
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Organizations</h1>
  </div>

  <!-- Pending organizations -->
  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">
      Pending Organizations
    </div>
    <div class="card-body p-0">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>S.N</th>
            <th>Organization Name</th>
            <th>Email</th>
            <th>Address</th>
            <th>Type</th>
            <th>Requested At</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($pending_orgs && $pending_orgs->num_rows > 0) {
              $sn = 1;
              while ($row = $pending_orgs->fetch_assoc()) {
                  echo "<tr>
                          <td>{$sn}</td>
                          <td>{$row['org_name']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['address']}</td>
                          <td>{$row['org_type']}</td>
                          <td>{$row['created_at']}</td>
                        </tr>";
                  $sn++;
              }
          } else {
              echo '<tr><td colspan="6" class="text-center text-muted">No pending organizations.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Approved organizations -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">
      Approved Organizations
    </div>
    <div class="card-body p-0">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>S.N</th>
            <th>Organization Name</th>
            <th>Email</th>
            <th>Address</th>
            <th>Type</th>
            <th>Joined At</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($approved_orgs && $approved_orgs->num_rows > 0) {
              $sn = 1;
              while ($row = $approved_orgs->fetch_assoc()) {
                  echo "<tr>
                          <td>{$sn}</td>
                          <td>{$row['org_name']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['address']}</td>
                          <td>{$row['org_type']}</td>
                          <td>{$row['created_at']}</td>
                        </tr>";
                  $sn++;
              }
          } else {
              echo '<tr><td colspan="6" class="text-center text-muted">No approved organizations found.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
