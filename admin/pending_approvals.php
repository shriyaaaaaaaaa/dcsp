
<?php

include('includes/db_connect.php');

// Ensure admin is logged in (adjust as per your admin authentication)
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }

// Fetch pending sub-admins
$stmt = $conn->prepare("SELECT id, org_name, email, org_type, certificate FROM sub_admin WHERE approval = 0");
$stmt->execute();
$result = $stmt->get_result();
$pending_sub_admins = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - DCSP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .approvals-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/login-bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .approvals-card {
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
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: scale(1.1);
        }
        .focused-row {
            background-color: #e9ecef;
        }
        .certificate-link {
            color: #0d6efd;
            text-decoration: none;
        }
        .certificate-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>


<section class="approvals-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="approvals-card">
                    <h3 class="text-center mb-4">Pending Sub-Admin Approvals</h3>

                    <?php if (empty($pending_sub_admins)): ?>
                        <div class="alert alert-info text-center">No pending approvals.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Organization Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_sub_admins as $sub_admin): ?>
                                        <tr class="sub-admin-row" data-id="<?= htmlspecialchars($sub_admin['id']) ?>" tabindex="0">
                                            <td><?= htmlspecialchars($sub_admin['id']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($sub_admin['org_name']) ?>
                                                <a href="#" class="view-details ms-2 text-primary" 
                                                   data-id="<?= htmlspecialchars($sub_admin['id']) ?>"
                                                   data-org-name="<?= htmlspecialchars($sub_admin['org_name']) ?>"
                                                   data-email="<?= htmlspecialchars($sub_admin['email']) ?>"
                                                   data-org-type="<?= htmlspecialchars($sub_admin['org_type']) ?>"
                                                   data-certificate="<?= htmlspecialchars($sub_admin['certificate']) ?>"
                                                   data-bs-toggle="modal" data-bs-target="#detailsModal">
                                                   View Details
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($sub_admin['email']) ?></td>
                                            <td>
                                                <button class="btn btn-success btn-sm action-btn approve-btn" 
                                                        data-id="<?= htmlspecialchars($sub_admin['id']) ?>" 
                                                        title="Approve (A)">
                                                    Approve
                                                </button>
                                                <button class="btn btn-danger btn-sm action-btn reject-btn" 
                                                        data-id="<?= htmlspecialchars($sub_admin['id']) ?>" 
                                                        title="Reject (R)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Sub-Admin Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="modal-id"></span></p>
                <p><strong>Organization Name:</strong> <span id="modal-org-name"></span></p>
                <p><strong>Email:</strong> <span id="modal-email"></span></p>
                <p><strong>Organization Type:</strong> <span id="modal-org-type"></span></p>
                <p><strong>Certificate:</strong> <a id="modal-certificate" href="#" target="_blank" class="certificate-link">View Certificate</a></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle View Details modal
    document.querySelectorAll('.view-details').forEach(link => {
        link.addEventListener('click', function () {
            const modal = document.getElementById('detailsModal');
            modal.querySelector('#modal-id').textContent = this.dataset.id;
            modal.querySelector('#modal-org-name').textContent = this.dataset.orgName;
            modal.querySelector('#modal-email').textContent = this.dataset.email;
            modal.querySelector('#modal-org-type').textContent = this.dataset.orgType;
            modal.querySelector('#modal-certificate').href = this.dataset.certificate;
            modal.querySelector('#modal-certificate').textContent = this.dataset.certificate.split('/').pop();
        });
    });

    // Handle Approve/Reject via AJAX
    function handleAction(button, action) {
        const id = button.dataset.id;
        const row = button.closest('tr');
        fetch('handle_approval.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                const tbody = document.querySelector('tbody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No pending approvals.</td></tr>';
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }

    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => handleAction(btn, 'approve'));
    });

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', () => handleAction(btn, 'reject'));
    });

    // Keyboard shortcuts
    let focusedRow = null;
    document.querySelectorAll('.sub-admin-row').forEach(row => {
        row.addEventListener('focus', () => {
            if (focusedRow) focusedRow.classList.remove('focused-row');
            focusedRow = row;
            row.classList.add('focused-row');
        });
        row.addEventListener('blur', () => {
            if (focusedRow === row) {
                row.classList.remove('focused-row');
                focusedRow = null;
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (!focusedRow) return;
        if (e.key.toLowerCase() === 'a') {
            e.preventDefault();
            const approveBtn = focusedRow.querySelector('.approve-btn');
            if (approveBtn) approveBtn.click();
        } else if (e.key.toLowerCase() === 'r') {
            e.preventDefault();
            const rejectBtn = focusedRow.querySelector('.reject-btn');
            if (rejectBtn) rejectBtn.click();
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>
