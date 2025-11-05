<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include('includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    // Check if student exists
    $stmt = $conn->prepare("SELECT id, name, password FROM student WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {

        // --- If you store plain text passwords (not recommended but often done in student projects) ---
        // Just compare directly:
        if ($password === $row['password']) {

            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_name'] = $row['name'];

            echo "
            <script>
                alert('Login successful!');
                window.location.href='s_dashboard.php';
            </script>";
        } 
        // --- If you used password_hash() when registering, use password_verify() ---
        elseif (password_verify($password, $row['password'])) {

            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_name'] = $row['name'];

            echo "
            <script>
                alert('Login successful!');
                window.location.href='s_dashboard.php';
            </script>";
        }
        else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='s_login.php';</script>";
}
$conn->close();
?>
