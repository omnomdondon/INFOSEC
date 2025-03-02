<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Database connection
include '../../model/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if password is confirmed
    if (!isset($_SESSION['password_confirmed'])) {
        echo json_encode(['success' => false, 'error' => 'Password not confirmed.']);
        exit;
    }

    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $CONN->prepare("INSERT INTO posts (title, content, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        // Reset password confirmation flag
        unset($_SESSION['password_confirmed']);
        echo json_encode(['success' => true, 'message' => 'Post created successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create post: ' . $stmt->error]);
    }
    exit; // Ensure no further output is sent
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> <!-- Font Awesome for icons -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        /* Prevent resizing of the textarea */
        textarea.form-control {
            resize: none;
        }

        .btn-success {
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Toggle password button */
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }

        .password-toggle i {
            color: #666;
        }

        .password-toggle:hover i {
            color: #333;
        }

        .password-input-container {
            position: relative;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comments_dashboard.php">Comments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../controller/dashboard_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <h2 class="text-center mb-4">Create a New Post</h2>
                    <form id="createPostForm" method="POST" action="post_dashboard.php">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter post title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="6" placeholder="Write your post content here..." required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="button" class="btn btn-success" id="createPostButton">Create Post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Confirmation Modal -->
    <div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordConfirmModalLabel">Confirm Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordConfirmForm">
                        <div class="mb-3 password-input-container">
                            <label for="adminPassword" class="form-label">Enter Your Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i> <!-- Eye icon from Font Awesome -->
                            </button>
                        </div>
                        <div id="passwordError" class="text-danger mb-3"></div>
                        <button type="submit" class="btn btn-success">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('adminPassword');
            const icon = this.querySelector('i');

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

        // Show password confirmation modal
        document.getElementById('createPostButton').addEventListener('click', function() {
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
            passwordModal.show();
        });

        // Handle password confirmation form submission
        document.getElementById('passwordConfirmForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            const adminPassword = document.getElementById('adminPassword').value;
            const passwordError = document.getElementById('passwordError');

            // Send password to server for validation
            fetch('../../controller/admin_confirm_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `adminPassword=${encodeURIComponent(adminPassword)}`,
                })
                .then(response => {
                    console.log(response); // Log the response for debugging
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    if (data.success) {
                        // Password confirmed, submit the post form
                        submitPostForm();
                    } else {
                        // Show error message
                        passwordError.textContent = data.error || 'Incorrect password.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    passwordError.textContent = 'An error occurred. Please try again.';
                });
        });

        // Function to submit the post form
        function submitPostForm() {
            const form = document.getElementById('createPostForm');
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message); // Display success message
                        // Optionally, reset the form or redirect the user
                        form.reset();
                    } else {
                        alert(data.error); // Display error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating the post. Please try again.');
                });
        }
    </script>
</body>

</html>