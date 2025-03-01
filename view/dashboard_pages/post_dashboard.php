<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Database connection
include '../../model/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $CONN->prepare("INSERT INTO posts (title, content, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        echo "Post created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Dashboard</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> <!-- Font Awesome -->

    <script>
        let timeout;

        function startTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logoutUser, 300000); // 5 minutes (300000ms) timeout
        }

        function logoutUser() {
            alert("Session expired due to inactivity. Redirecting to login page.");
            window.location.href = "../../controller/dashboard_logout.php";
        }

        // Logout Confirmation Modal Handling
        document.addEventListener("DOMContentLoaded", function() {
            const logoutModal = document.getElementById('logoutConfirmationModal');
            const logoutLink = document.querySelector('a[data-bs-target="#logoutConfirmationModal"]');

            logoutLink.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                const modal = new bootstrap.Modal(logoutModal);
                modal.show();
            });

            // Handle the logout button click inside the modal
            const logoutButton = document.querySelector('#logoutConfirmationModal .btn-danger');
            logoutButton.addEventListener('click', function() {
                window.location.href = '../../controller/dashboard_logout.php'; // Redirect to logout page
            });
        });

        // Reset timer on user activity
        document.addEventListener("mousemove", startTimer);
        document.addEventListener("keydown", startTimer);
        document.addEventListener("mousedown", startTimer); // Detects clicks
        document.addEventListener("wheel", startTimer); // Detects scrolling
        document.addEventListener("touchstart", startTimer); // Detects mobile touch

        startTimer(); // Initialize timer on page load
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comments_dashboard.php">Comments Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal"
                            data-bs-target="#logoutConfirmationModal">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Create a New Post</h2>
        <form method="POST" action="post_dashboard.php">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <button type="button" class="btn btn-success" id="openPasswordModal">Create Post</button>
        </form>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutConfirmationModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../../controller/dashboard_logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Confirmation Modal -->
    <div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordConfirmModalLabel">Confirm Your Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordConfirmForm">
                        <div class="mb-3 position-relative">
                            <label for="adminPassword" class="form-label">Enter Your Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fa fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div id="passwordError" class="text-danger"></div>
                        <button type="submit" class="btn btn-success">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

<script>
    document.getElementById('openPasswordModal').addEventListener('click', function() {
        var passwordModal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
        passwordModal.show();
    });

    document.getElementById('passwordConfirmForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        let adminPassword = document.getElementById('adminPassword').value;
        let passwordError = document.getElementById('passwordError');

        fetch('admin_confirm_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'adminPassword=' + encodeURIComponent(adminPassword)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('passwordConfirmModal').querySelector('.btn-close').click();
                    document.getElementById('passwordError').textContent = ''; // Clear error

                    // Submit the post form after password confirmation
                    document.querySelector('form').submit();
                } else {
                    passwordError.textContent = data.error;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                passwordError.textContent = 'Something went wrong. Please try again.';
            });
    });

    document.getElementById('openPasswordModal').addEventListener('click', function() {
        var passwordModal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
        passwordModal.show();
    });

    document.getElementById('togglePassword').addEventListener('click', function() {
        let passwordInput = document.getElementById('adminPassword');
        let icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.getElementById('passwordConfirmModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('adminPassword').value = ''; // Clear password field
        document.getElementById('passwordError').textContent = ''; // Clear error message
        let icon = document.getElementById('togglePassword').querySelector('i');
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye'); // Reset toggle icon
        document.getElementById('adminPassword').type = 'password'; // Reset input type
    });
</script>

</html>