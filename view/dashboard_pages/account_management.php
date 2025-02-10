<?php
session_start();
include '../../model/connect.php';

// Ensure the user is logged in and has 'admin' role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all users from the database
$query = "SELECT user_id, username, email, role, created_at FROM users"; // Added role before created_at
$result = $CONN->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Account Management</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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
    <!-- Navbar -->
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
                        <a class="nav-link" href="post_dashboard.php">Post Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comments_dashboard.php">Comments Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account_management.php">Account Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../controller/dashboard_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Account Management</h2>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#signupModal">Create New
            Account</button>

        <!-- User Table -->
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th> <!-- Added Role column -->
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($user = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['role']) . "</td>"; // Display role
                        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>"; // Display created_at
                        echo "<td>
                        <button class='btn btn-warning btn-sm' onclick='openEditModal(" . $user['user_id'] . ")'>Edit</button>
                        <a href='../../controller/delete_account.php?id=" . $user['user_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this account?\")'>Delete</a>
                            </td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <input type="hidden" id="edit_user_id">
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="edit_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_role" class="form-label">Role</label>
                                <select class="form-control" id="edit_role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous code remains the same until the Create Account Modal -->

        <!-- Create Account Modal -->
        <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="signupModalLabel">Create New Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../controller/register.php" method="POST">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Password Strength Indicator -->
                                <div id="password-strength" class="mt-2">
                                    <div id="password-strength-text" class="small"></div>
                                    <div id="password-strength-bar" class="progress mt-1">
                                        <div id="password-strength-progress" class="progress-bar" role="progressbar"
                                            style="width: 0%;"></div>
                                    </div>
                                </div>
                                <!-- Password Requirements -->
                                <div id="password-requirements" class="small mt-2">
                                    <ul class="list-unstyled">
                                        <li id="length" class="text-danger">At least 12 characters</li>
                                        <li id="uppercase" class="text-danger">Contains an uppercase letter</li>
                                        <li id="lowercase" class="text-danger">Contains a lowercase letter</li>
                                        <li id="number" class="text-danger">Contains a number</li>
                                        <li id="special" class="text-danger">Contains a special character</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="role" value="user">
                            <button type="submit" class="btn btn-primary" name="create_account">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrengthText = document.getElementById('password-strength-text');
        const passwordStrengthProgress = document.getElementById('password-strength-progress');
        const passwordRequirements = document.getElementById('password-requirements').querySelectorAll('li');

        passwordInput.addEventListener('input', function () {
            const password = passwordInput.value;

            // Define password requirements
            const minLength = 12;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            // Calculate password strength
            let strength = 0;
            if (password.length >= minLength) strength += 20;
            if (hasUppercase) strength += 20;
            if (hasLowercase) strength += 20;
            if (hasNumber) strength += 20;
            if (hasSpecial) strength += 20;

            // Update progress bar and text
            passwordStrengthProgress.style.width = `${strength}%`;
            if (strength < 40) {
                passwordStrengthProgress.classList.remove('bg-success', 'bg-warning');
                passwordStrengthProgress.classList.add('bg-danger');
                passwordStrengthText.textContent = 'Weak';
            } else if (strength < 80) {
                passwordStrengthProgress.classList.remove('bg-danger', 'bg-success');
                passwordStrengthProgress.classList.add('bg-warning');
                passwordStrengthText.textContent = 'Moderate';
            } else {
                passwordStrengthProgress.classList.remove('bg-danger', 'bg-warning');
                passwordStrengthProgress.classList.add('bg-success');
                passwordStrengthText.textContent = 'Strong';
            }

            // Update password requirements list
            passwordRequirements[0].classList.toggle('text-success', password.length >= minLength);
            passwordRequirements[1].classList.toggle('text-success', hasUppercase);
            passwordRequirements[2].classList.toggle('text-success', hasLowercase);
            passwordRequirements[3].classList.toggle('text-success', hasNumber);
            passwordRequirements[4].classList.toggle('text-success', hasSpecial);
        });

        function openEditModal(userId) {
            // Fetch user data via AJAX
            fetch(`../../controller/edit_account.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById("edit_user_id").value = data.user_id;
                        document.getElementById("edit_username").value = data.username;
                        document.getElementById("edit_email").value = data.email;
                        document.getElementById("edit_role").value = data.role;
                        new bootstrap.Modal(document.getElementById("editUserModal")).show();
                    }
                })
                .catch(error => console.error("Error fetching user data:", error));
        }

        // // Toggle password visibility
        // const togglePassword = document.getElementById('togglePassword');
        // const passwordInput = document.getElementById('password');
        // const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        // const confirmPasswordInput = document.getElementById('confirm_password');

        // togglePassword.addEventListener('click', function () {
        //     const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        //     passwordInput.setAttribute('type', type);
        //     this.querySelector('i').classList.toggle('bi-eye');
        //     this.querySelector('i').classList.toggle('bi-eye-slash');
        // });

        // toggleConfirmPassword.addEventListener('click', function () {
        //     const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        //     confirmPasswordInput.setAttribute('type', type);
        //     this.querySelector('i').classList.toggle('bi-eye');
        //     this.querySelector('i').classList.toggle('bi-eye-slash');
        // });

        // Handle form submission
        document.getElementById("editUserForm").addEventListener("submit", function (event) {
            event.preventDefault();

            const userId = document.getElementById("edit_user_id").value;
            const username = document.getElementById("edit_username").value;
            const email = document.getElementById("edit_email").value;
            const role = document.getElementById("edit_role").value;

            fetch("../../controller/register.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ user_id: userId, username: username, email: email, role: role })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.success);
                        location.reload(); // Refresh the page to reflect changes
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => console.error("Error updating account:", error));
        });
    </script>

</body>

</html>