<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // ✅ start session only if not started
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // ✅ path matches t_dashboard.php (includes is in the same folder as comment.php)
    require_once 'includes/db_connect.php';

    // Check if teacher is logged in    
    if (!isset($_SESSION['teacher_id'])) {
        header("Location: t_login.php");
        exit;
    }

    // Get posted comment
    $commentText = trim($_POST['comment'] ?? '');
    $teacher_id = $_SESSION['teacher_id']; // this holds the reg_no

    // Get org_id and name from teacher table
    $stmt = $conn->prepare("SELECT org_id, name FROM teacher WHERE reg_no = ?");
    $stmt->bind_param("s", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Error: Teacher not found");
    }
    $teacher = $result->fetch_assoc();
    $org_id = $teacher['org_id'];
    $teacher_name = $teacher['name'];
    $stmt->close();

    if (!empty($commentText)) {
        
    $stmt = $conn->prepare(
        "INSERT INTO comment (org_id, teacher_id, name, comment, date) VALUES (?, ?, ?, ?, NOW())"
    );
        $stmt->bind_param("isss", $org_id, $teacher_id, $teacher_name, $commentText);

        if ($stmt->execute()) {
            
            header("Location: t_dashboard.php");
            exit;
        } else {
            echo "Error saving comment: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Comment cannot be empty.";
    }
    ?>