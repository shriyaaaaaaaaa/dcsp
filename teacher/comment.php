<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session only if not started
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'includes/db_connect.php';

// Check if teacher is logged in    
if (!isset($_SESSION['teacher_id'])) {
    header("Location: t_login.php");
    exit;
}

// Get posted comment
$commentText = trim($_POST['comment'] ?? '');
$teacher_reg_no = $_SESSION['teacher_id']; // this holds the reg_no (string)

// Get teacher's id (integer), org_id and name from teacher table
$stmt = $conn->prepare("SELECT id, org_id, name FROM teacher WHERE reg_no = ?");
$stmt->bind_param("s", $teacher_reg_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Teacher not found");
}

$teacher = $result->fetch_assoc();
$teacher_id = $teacher['id']; // INTEGER id from teacher table
$org_id = $teacher['org_id'];
$teacher_name = $teacher['name'];
$stmt->close();

if (!empty($commentText)) {
    // Now insert with INTEGER teacher_id
    $stmt = $conn->prepare(
        "INSERT INTO comment (org_id, teacher_id, name, comment, date) VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("iiss", $org_id, $teacher_id, $teacher_name, $commentText);

    if ($stmt->execute()) {
        $_SESSION['comment_success'] = "Comment posted successfully!";
        header("Location: t_dashboard.php");
        exit;
    } else {
        echo "Error saving comment: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['comment_error'] = "Comment cannot be empty.";
    header("Location: t_dashboard.php");
    exit;
}
?>