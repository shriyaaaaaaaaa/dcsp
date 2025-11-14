<?php
include('includes/header.php'); // starts session + db_connect

// Ensure sub-admin is logged in
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];

// Load sub_admin info
$stmt = $conn->prepare("
    SELECT org_name, email, address, org_type, created_at, approval
    FROM sub_admin
    WHERE id = ?
");
$stmt->bind_param("i", $sub_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$sub = $result->fetch_assoc();
$stmt->close();

if (!$sub) {
    die("Sub-admin not found.");
}
?>

<main class="col-md-10 mx-auto px-4 py-4">
    <h3 class="mb-4">Profile</h3>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <?= htmlspecialchars($sub['org_name']) ?>
            </h5>

            <p><strong>Email:</strong> <?= htmlspecialchars($sub['email']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($sub['address']) ?></p>
            <p><strong>Organization Type:</strong> <?= htmlspecialchars($sub['org_type']) ?></p>

            <p><strong>Registered On:</strong>
                <?= htmlspecialchars($sub['created_at']) ?>
            </p>

            <p>
                <strong>Approval Status:</strong>
                <?php if ((int)$sub['approval'] === 1): ?>
                    <span class="badge bg-success">Approved</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
            </p>

            <!-- Certificate section removed -->
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>
</body>
</html>
