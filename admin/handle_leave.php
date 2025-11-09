<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
if (!isset($_SESSION['admin_id'])) { http_response_code(403); exit('Forbidden'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }

$id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action  = $_POST['action'] ?? '';
$comment = trim($_POST['admin_comment'] ?? '');

if (!$id || !in_array($action, ['APPROVE','REJECT','RESET'], true)) {
  http_response_code(400); exit('Bad Request');
}

$status = ($action === 'RESET') ? 'PENDING' : ($action === 'APPROVE' ? 'APPROVED' : 'REJECTED');

$stmt = $conn->prepare("UPDATE leave_requests
                        SET status = ?, admin_comment = ?, updated_at = NOW()
                        WHERE id = ?");
$stmt->bind_param("ssi", $status, $comment, $id);
if ($stmt->execute()) {
  header("Location: leave_view.php?id=" . $id);
  exit;
}
http_response_code(500);
echo "Database error: " . $conn->error;
