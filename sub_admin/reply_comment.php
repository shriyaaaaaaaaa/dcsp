<?php
include('includes/header.php');
include('includes/db_connect.php');

if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

$org_id = $_SESSION['sub_admin_id'];
$comment_id = $_GET['comment_id'] ?? null;

if (!$comment_id) {
    die("Invalid comment.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $reply = trim($_POST['reply'] ?? '');

    if ($name && $reply) {
        $stmt = $conn->prepare("INSERT INTO replies (comment_id, org_id, name, reply) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $comment_id, $org_id, $name, $reply);
        $stmt->execute();
        $stmt->close();

        header("Location: comments.php");
        exit;
    } else {
        $error = "Please fill all fields.";
    }
}

// Fetch original comment
$stmt = $conn->prepare("SELECT name, comment, date FROM comment WHERE id = ? AND org_id = ?");
$stmt->bind_param("ii", $comment_id, $org_id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
$stmt->close();

?>

<div class="container py-5">
    <h3>Reply to Comment</h3>

    <div class="card p-3 mb-4">
        <p><?= htmlspecialchars($comment['comment']) ?></p>
        <small><?= htmlspecialchars($comment['name']) ?> | <?= date("d M Y, h:i A", strtotime($comment['date'])) ?></small>
    </div>

    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
        <label>Your Name</label>
        <input type="text" name="name" class="form-control" 
        value="<?= isset($_SESSION['org_name']) ? htmlspecialchars($_SESSION['org_name']) : '' ?>">
        </div>

        <div class="mb-3">
            <label>Reply</label>
            <textarea name="reply" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Reply</button>
        <a href="comments.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('includes/footer.php'); ?>
