<?php
session_start();
include('includes/db_connect.php');

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
$errors = [];
$maxFileSizeMB  = 5;
$maxFileSizeB   = $maxFileSizeMB * 1024 * 1024;
$input_data = [
    'org_name' => isset($_POST['org_name']) ? htmlspecialchars(trim($_POST['org_name'])) : '',
    'email' => isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '',
    'org_type' => isset($_POST['org_type']) ? htmlspecialchars(trim($_POST['org_type'])) : ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['general'] = "Invalid CSRF token.";
    } else {
        $org_name = filter_var(trim($_POST['org_name']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $org_type = filter_var(trim($_POST['org_type']), FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($org_name)) {
            $errors['org_name'] = "Organization name is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "A valid email is required.";
        }
        if (empty($org_type)) {
            $errors['org_type'] = "Organization type is required.";
        }
        if (empty($password)) {
            $errors['password'] = "Password is required.";
        } elseif (strlen($password) < 8 || !preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
            $errors['password'] = "Password must be at least 8 characters long, with at least one capital letter, one number, and one symbol.";
        }
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match.";
        }
        if (empty($_FILES['certificate']['name'])) {
            $errors['certificate'] = "Registration certificate is required.";
        }

        // Check if email already exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM sub_admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors['email'] = "Email is already registered.";
            }
            $stmt->close();
        }

        // Handle file upload
       // Handle file upload
if (empty($errors)) {
    $target_dir = "../admin/Uploads/certificates/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $certificate = $_FILES['certificate'];
    $file_type = strtolower(pathinfo($certificate["name"], PATHINFO_EXTENSION));
    
    // Sanitize org_name to create a safe file name
    $sanitized_org_name = preg_replace("/[^a-zA-Z0-9]/", "_", $org_name);
    $target_file = $target_dir . $sanitized_org_name . '_' . uniqid() . '.' . $file_type;

    // Validate file
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($file_type, $allowed_types)) {
        $errors['certificate'] = "Invalid file format. Only PDF, JPG, JPEG, and PNG are allowed.";
    } elseif ($certificate['size'] > $maxFileSizeB) {
    $errors['certificate'] = "File size exceeds {$maxFileSizeMB}MB.";
} else {

        if (move_uploaded_file($certificate["tmp_name"], $target_file)) {
            // Insert into database with approval set to 0
            $approval = 0;
            $stmt = $conn->prepare("INSERT INTO sub_admin (org_name, email, org_type, password, certificate, approval) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $org_name, $email, $org_type, $password, $target_file, $approval);

            if ($stmt->execute()) {
                // Set session variable for success message
                $_SESSION['success_message'] = "Registration successful! Awaiting admin approval.";
                $stmt->close();
                $conn->close();
                // Ensure session data is written before redirect
                session_write_close();
                header("Location: sa_login.php");
                exit();
            } else {
                $errors['general'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors['certificate'] = "Error uploading file.";
        }
    }
}
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/register-bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.97);
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
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #0d6efd;
        }
        .error {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<section class="register-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="register-card">
                    <h3 class="text-center mb-4">Sub-Admin Registration</h3>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($errors['general']) ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="mb-3">
                            <label for="org_name" class="form-label">Organization Name</label>
                            <input type="text" class="form-control <?= isset($errors['org_name']) ? 'is-invalid' : '' ?>" id="org_name" name="org_name" value="<?= $input_data['org_name'] ?>" required>
                            <?php if (isset($errors['org_name'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['org_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Official Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= $input_data['email'] ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                            <div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <input type="text" 
           class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
           id="address" 
           name="address" 
           value="<?= htmlspecialchars($input_data['address'] ?? '') ?>" 
           required>
    
    <?php if (isset($errors['address'])): ?>
        <div class="invalid-feedback">
            <?= htmlspecialchars($errors['address']) ?>
        </div>
    <?php endif; ?>
</div>
                        <div class="mb-3">
                            <label for="org_type" class="form-label">Organization Type</label>
                            <select class="form-control <?= isset($errors['org_type']) ? 'is-invalid' : '' ?>" id="org_type" name="org_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="university" <?= $input_data['org_type'] === 'university' ? 'selected' : '' ?>>University</option>
                                <option value="college" <?= $input_data['org_type'] === 'college' ? 'selected' : '' ?>>College</option>
                                <option value="school" <?= $input_data['org_type'] === 'school' ? 'selected' : '' ?>>School</option>
                                <option value="organization" <?= $input_data['org_type'] === 'organization' ? 'selected' : '' ?>>Other Organization</option>
                            </select>
                            <?php if (isset($errors['org_type'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['org_type']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required>
                                <span class="password-toggle bi bi-eye" onclick="togglePassword('password', this)"></span>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                                <span class="password-toggle bi bi-eye" onclick="togglePassword('confirm_password', this)"></span>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="certificate" class="form-label">Upload Registration Certificate <small class="text-muted">(PDF/JPG/PNG, max 100MB)</small></label>
                            <input type="file" class="form-control <?= isset($errors['certificate']) ? 'is-invalid' : '' ?>" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required>
                            <?php if (isset($errors['certificate'])): ?>
                                <div class="error"><?= htmlspecialchars($errors['certificate']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                        <div class="text-center mt-3">
                            Already have an account? <a href="sa_login.php" class="text-primary">Login here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
<script>
function togglePassword(id, icon) {
    const field = document.getElementById(id);
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>