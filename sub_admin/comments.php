<?php
include('includes/header.php');
include('includes/db_connect.php');

if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit;
}

$org_id = $_SESSION['sub_admin_id'];

// Handle delete comment
if (isset($_GET['delete_comment_id'])) {
    $del_id = intval($_GET['delete_comment_id']);
    $stmt = $conn->prepare("DELETE FROM comment WHERE id = ? AND org_id = ?");
    $stmt->bind_param("ii", $del_id, $org_id);
    $stmt->execute();
    $stmt->close();
    header("Location: comments.php");
    exit;
}

// Handle delete reply
if (isset($_GET['delete_reply_id'])) {
    $del_reply_id = intval($_GET['delete_reply_id']);
    $stmt = $conn->prepare("DELETE FROM replies WHERE id = ? AND org_id = ?");
    $stmt->bind_param("ii", $del_reply_id, $org_id);
    $stmt->execute();
    $stmt->close();
    header("Location: comments.php");
    exit;
}

// Fetch comments
$stmt = $conn->prepare("SELECT * FROM comment WHERE org_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();

?>

<div class="container py-5">
    <h3 class="mb-4">💬 Comments</h3>

    <?php if ($comments->num_rows == 0): ?>
        <div class="alert alert-info">No comments yet.</div>
    <?php else: ?>
        <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <p class="mb-2" style="font-size:14pt;"><?= htmlspecialchars($comment['comment']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted" style="font-size:10pt;">
                            <?= htmlspecialchars($comment['name']) ?> | <?= date("d M Y, h:i A", strtotime($comment['date'])) ?>
                        </small>
                        <div>
                            <a href="reply_comment.php?comment_id=<?= $comment['id'] ?>" class="btn btn-sm btn-outline-primary">Reply</a>
                            <a href="comments.php?delete_comment_id=<?= $comment['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this comment?')">Delete</a>
                        </div>
                    </div>

                    <!-- Replies -->
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM replies WHERE comment_id = ? ORDER BY date ASC");
                    $stmt->bind_param("i", $comment['id']);
                    $stmt->execute();
                    $replies = $stmt->get_result();
                    $stmt->close();
                    ?>
                    <?php if ($replies->num_rows > 0): ?>
                        <div class="mt-3 ps-3 border-start">
                            <?php while ($reply = $replies->fetch_assoc()): ?>
                                <div class="card mb-2 shadow-sm">
                                    <div class="card-body py-2 px-3">
                                        <p class="mb-1" style="font-size:13pt;"><?= htmlspecialchars($reply['reply']) ?></p>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted" style="font-size:10pt;">
                                                <?= htmlspecialchars($reply['name']) ?> | <?= date("d M Y, h:i A", strtotime($reply['date'])) ?>
                                            </small>
                                            <a href="comments.php?delete_reply_id=<?= $reply['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this reply?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>
