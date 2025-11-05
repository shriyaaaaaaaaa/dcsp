<?php
// Include header (handles session start and validation)
include('includes/header.php');

// Fetch classes for the sub-admin
$stmt = $conn->prepare("SELECT id, class_name, subjects, CONCAT(TIME_FORMAT(start_time, '%h:%i %p'), ' - ', TIME_FORMAT(end_time, '%h:%i %p')) AS time_duration FROM classes WHERE sub_admin_id = ?");
$stmt->bind_param("i", $_SESSION['sub_admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="container py-5">
    <h1 class="text-center mb-4"> Classes of <span class="text-primary"><?= htmlspecialchars($org_name) ?></span></h1>
    <?php if (empty($classes)): ?>
        <div class="alert alert-info text-center">No classes found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($classes as $class): ?>
                <div class="col-md-4 mb-4">
                    <div class="class-card" onclick="window.location.href='edit_class.php?id=<?php echo htmlspecialchars($class['id']); ?>'">
                        <h3 class="class-title"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                        <p class="class-detail"><strong>Subjects:</strong> <?php echo htmlspecialchars($class['subjects']); ?></p>
                        <p class="class-detail"><strong>Time Duration:</strong> <?php echo htmlspecialchars($class['time_duration']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="manage_classes.php" class="btn btn-primary">Add New Class</a>
    </div>
</div>
            </div>
<?php include('includes/footer.php'); ?>
</body>
</html>