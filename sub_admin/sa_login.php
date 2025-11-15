<?php
session_start();

// ✅ Show PHP errors on this page (very important while debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';

$error = '';
$input_email = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Check DB connection
    if (!isset($conn) || $conn->connect_error) {
        $error = "Database connection error.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, org_name, password, approval 
             FROM sub_admin 
             WHERE email = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $org_name, $stored_password, $approval);
            $stmt->fetch();

            // Cast approval to int to be safe
            $approval = (int)$approval;

            if ($approval != 1) {
                $error = "Waiting for Admin Approval.";
            } elseif ($password === $stored_password) { // 🔒 plain-text for now (as in your DB)
                $_SESSION['sub_admin_id'] = $id;
                $_SESSION['org_name'] = $org_name;
                header("Location: sa_dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .login-section {
            background: linear-gradient(
                rgba(0, 0, 0, 0.6),
                rgba(0, 0, 0, 0.6)
            ), url('images/login-bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            width: 100%;
            padding: 0.6rem;
            font-weight: 600;
        }
        .text-primary {
            font-weight: 500;
        }
        .text-primary:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<section class="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card position-relative p-4 shadow" style="border-radius: 1rem;">

                    <!-- Close Button Inside Top Right -->
                    <a href="../index.php"
                       class="btn btn-sm btn-light position-absolute"
                       style="top: 10px; right: 10px; border-radius: 50%; width: 32px; height: 32px; text-align: center; padding: 0;">
                        &times;
                    </a>

                    <h3 class="text-center mb-4">Login to DCSP</h3>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success text-center">
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="sa_login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   value="<?= $input_email ?>"
                                   required>
                        </div>

                        <div class="mb-3 password-wrapper">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required>
                            <i class="bi bi-eye password-toggle" onclick="togglePassword()"></i>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Login</button>

                        <div class="text-center mt-3">
                            <p class="mb-0">
                                Don’t have an account?
                                <a href="sa_register.php" class="text-primary">Register here</a>
                            </p>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.querySelector('.password-toggle');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>
