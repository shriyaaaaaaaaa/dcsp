<?php
include('includes/db_connect.php');

$org_id = $_POST['org_id'];
$reg_no = trim($_POST['reg_no']);
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
    echo "<script>alert('Passwords do not match.'); history.back();</script>";
    exit;
}



// Create table name
$table_result = $conn->query("SELECT org_name FROM sub_admin WHERE id = $org_id");
$row = $table_result->fetch_assoc();
$org_name = $row['org_name'];
$table_name = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($org_name)) . "_$org_id";

// Check if student_reg exists
$check = $conn->prepare("SELECT * FROM `$table_name` WHERE student_reg = ?");
$check->bind_param("s", $reg_no);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
    echo "<script>alert('Invalid Registration Number. Contact your organization.'); history.back();</script>";
    exit;
}

// Check for duplicate registration
$dup = $conn->prepare("SELECT * FROM student WHERE reg_no = ? AND org_id = ?");
$dup->bind_param("si", $reg_no, $org_id);
$dup->execute();
$dup_result = $dup->get_result();
if ($dup_result->num_rows > 0) {
    echo "<script>alert('You have already registered.'); history.back();</script>";
    exit;
}

// Insert into student table
$insert = $conn->prepare("INSERT INTO student (reg_no, name, email, password, org_id, tick) VALUES (?, ?, ?, ?, ?, 0)");
$insert->bind_param("ssssi", $reg_no, $name, $email, $password, $org_id);
if ($insert->execute()) {
    echo "<script>alert('Registration successful!'); window.location.href='s_login.php';</script>";
} else {
    echo "<script>alert('Registration failed! Please try again.'); history.back();</script>";
}
?>