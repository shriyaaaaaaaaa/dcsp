<?php
session_start();
include('includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: place.php');
    exit;
}

$org_id = $_POST['org_id'];
$reg_no = trim($_POST['reg_no']);
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate passwords match
if ($password !== $confirm_password) {
    $_SESSION['registration_error'] = "Passwords do not match. Please try again.";
    $_SESSION['attempted_reg_no'] = $reg_no;
    $_SESSION['attempted_name'] = $name;
    $_SESSION['attempted_email'] = $email;
    header("Location: s_register.php?org_id=$org_id");
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
    $_SESSION['registration_error'] = "Invalid Registration Number. This registration number is not registered with your organization. Please check the format and try again, or contact your organization's admin for assistance.";
    $_SESSION['attempted_reg_no'] = $reg_no;
    $_SESSION['attempted_name'] = $name;
    $_SESSION['attempted_email'] = $email;
    header("Location: s_register.php?org_id=$org_id");
    exit;
}

// Check if email already exists
$email_check = $conn->prepare("SELECT * FROM student WHERE email = ?");
$email_check->bind_param("s", $email);
$email_check->execute();
$email_result = $email_check->get_result();

if ($email_result->num_rows > 0) {
    $_SESSION['registration_error'] = "This email address is already registered. Please use a different email or try logging in.";
    $_SESSION['attempted_reg_no'] = $reg_no;
    $_SESSION['attempted_name'] = $name;
    header("Location: s_register.php?org_id=$org_id");
    exit;
}


$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert student data
$insert = $conn->prepare("INSERT INTO student (org_id, name, email, password, reg_no) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param("issss", $org_id, $name, $email, $password, $reg_no);

if ($insert->execute()) {
    $_SESSION['registration_success'] = "Registration successful! You can now login with your credentials.";
    header("Location: s_login.php");
    exit;
} else {
    $_SESSION['registration_error'] = "Registration failed. Please try again later or contact support.";
    $_SESSION['attempted_reg_no'] = $reg_no;
    $_SESSION['attempted_name'] = $name;
    $_SESSION['attempted_email'] = $email;
    header("Location: s_register.php?org_id=$org_id");
    exit;
}
?>