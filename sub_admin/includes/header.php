<?php
session_start();
include('includes/db_connect.php');

// Check if sub_admin is logged in
if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sa_login.php");
    exit();
}

// Fetch org_name from sub_admin table
$stmt = $conn->prepare("SELECT org_name FROM sub_admin WHERE id = ?");
$stmt->bind_param("i", $_SESSION['sub_admin_id']);
$stmt->execute();
$stmt->bind_result($org_name);
$stmt->fetch();
$stmt->close();
$_SESSION['org_name'] = $org_name;

// Determine the current page for conditional CSS and body class
$current_page = basename($_SERVER['PHP_SELF']);
$body_class = str_replace('.php', '-page', $current_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($org_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="css/sa_dashboard.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
    <?php if ($current_page === 'manage_classes.php'): ?>
        <link href="css/manage_classes.css" rel="stylesheet">
    <?php elseif ($current_page === 'manage_teachers.php'): ?>
        <link href="css/manage_teachers.css" rel="stylesheet">
    <?php elseif ($current_page === 'subjects.php'): ?>
        <link href="css/subjects.css" rel="stylesheet">
    <?php elseif ($current_page === 'member_list.php'): ?>
        <link href="css/member_list.css" rel="stylesheet">    
    <?php elseif ($current_page === 'manage_students.php'): ?>
        <link href="css/manage_students.css" rel="stylesheet">
    <?php elseif ($current_page === 'classes.php'): ?>
        <link href="css/classes.css" rel="stylesheet">
    <?php elseif ($current_page === 'edit_class.php'): ?>
        <link href="css/edit_class.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="<?php echo htmlspecialchars($body_class); ?>">
<div class="page-wrapper">
    <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="sa_dashboard.php"><?php echo htmlspecialchars($org_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="sa_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="sa_profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="member.php">Member</a></li>
                    <li class="nav-item"><a class="nav-link" href="display_schedule.php">Schedule</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" data-bs-toggle="dropdown" aria-expanded="false">Manage</a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="manage_teachers.php">Teachers</a></li>
                            <li><a class="dropdown-item" href="manage_students.php">Students</a></li>
                            <li><a class="dropdown-item" href="classes.php">Classes</a></li>
                            <li><a class="dropdown-item" href="subjects.php">Subjects</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="btn btn-danger ms-2" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>