<?php
session_start();
include '../../model/connect.php';

// Ensure the user is logged in and has 'admin' role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all users from the database with a limit of 5 rows
$query = "SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at ASC LIMIT 5";
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

    <style>
        /* General Styling */
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        p {
            color: #666;
        }

        /* Scrollable table container */
        .scrollable-table {
            max-height: 300px;
            /* Adjust the height as needed */
            overflow-y: auto;
            /* Enable vertical scrolling */
            border: 1px solid #dee2e6;
            /* Optional: Add a border */
            border-radius: 8px;
            /* Optional: Add rounded corners */
            margin-bottom: 20px;
            /* Optional: Add some spacing */
        }

        /* Sticky table header */
        .table thead th {
            position: sticky;
            top: 0;
            /* Stick to the top of the scrollable container */
            background-color: #198754;
            /* Match the header background color */
            color: #fff;
            /* Match the header text color */
            z-index: 1;
            /* Ensure the header stays above the table rows */
        }

        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Modal Styling */
        .modal-header {
            color: #fff;
        }

        .modal-title {
            font-weight: bold;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
        }

        /* Password Strength Checker */
        .progress-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #password-strength-bar {
            flex-grow: 1;
        }

        #tooltipIcon {
            position: static;
            transform: none;
            transition: none;
        }
    </style>
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
                        <a class="nav-link" href="#" data-bs-toggle="modal"
                            data-bs-target="#logoutConfirmationModal">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Account Management</h2>
        <!-- Create Account Button -->
        <button class="btn btn-success mb-3" onclick="confirmPasswordBeforeAction('create_account', {})">Create New
            Account</button>

        <!-- User Table -->
        <div class="scrollable-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
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
                            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' onclick=\"confirmPasswordBeforeAction('edit_account', { userId: " . $user['user_id'] . " })\">Edit</button>
                                <button class='btn btn-danger btn-sm' onclick=\"confirmPasswordBeforeAction('delete_account', { userId: " . $user['user_id'] . " })\">Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="editUserModalLabel">Edit Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <input type="hidden" id="edit_user_id" name="user_id">
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_role" class="form-label">Role</label>
                                <select class="form-control" id="edit_role" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Account Modal -->
        <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="signupModalLabel">Create New Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createAccountForm">
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
                                    <button type="button" class="btn btn-outline-secondary" id="toggleCreatePassword">
                                        <i id="toggleCreatePasswordIcon" class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Password Strength Indicator -->
                                <div class="progress-container mt-2">
                                    <div id="password-strength-text" class="small"></div>
                                    <div id="password-strength-bar" class="progress mt-1">
                                        <div id="password-strength-progress" class="progress-bar" role="progressbar"
                                            style="width: 0%;"></div>
                                    </div>
                                    <!-- Tooltip Icon -->
                                    <button type="button" class="btn p-0" id="tooltipIcon" data-bs-toggle="tooltip"
                                        data-bs-html="true"
                                        title="<ul class='mb-0 text-start'><li>At least 12 characters</li><li>Include uppercase letter</li><li>Include lowercase letter</li><li>Include numbers</li><li>Include special character</li></ul>">
                                        <i class="bi bi-info-circle fs-6 text-secondary"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
                                        <i id="toggleConfirmPasswordIcon" class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="role" value="user">
                            <button type="submit" class="btn btn-success" name="create_account">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
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
    <div class="modal fade" id="passwordConfirmationModal" tabindex="-1"
        aria-labelledby="passwordConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="passwordConfirmationModalLabel">Confirm Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordConfirmationForm">
                        <input type="hidden" id="actionType" name="actionType" value="">
                        <input type="hidden" id="actionData" name="actionData" value="">
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Enter your password to continue:</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="adminPassword" name="adminPassword"
                                    required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="passwordError" class="text-danger mb-3" style="display: none;">Incorrect password.
                            Please try again.</div>
                        <button type="submit" class="btn btn-success">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this account? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
                </div>
            </div>
        </div>
    </div>

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
        let userIdToDelete = null; // Store the user ID to delete
        let userIdToEdit = null; // Store the user ID to edit

        // Function to confirm password before performing an action
        function confirmPasswordBeforeAction(action, data) {
            const actionTypeElement = document.getElementById('actionType');
            const actionDataElement = document.getElementById('actionData');
            const passwordModalElement = document.getElementById('passwordConfirmationModal');

            if (!actionTypeElement || !actionDataElement || !passwordModalElement) {
                console.error("Required elements not found!");
                return;
            }

            actionTypeElement.value = action;
            actionDataElement.value = JSON.stringify(data);

            // Set userIdToDelete if the action is delete_account
            if (action === 'delete_account') {
                userIdToDelete = data.userId;
            }

            const passwordModal = new bootstrap.Modal(passwordModalElement);
            passwordModal.show();
        }

        // DOMContentLoaded event listener
        document.addEventListener("DOMContentLoaded", function () {
            // Password Strength Checker
            const passwordInput = document.getElementById("password");
            const passwordStrengthText = document.getElementById("password-strength-text");
            const passwordStrengthProgress = document.getElementById("password-strength-progress");
            const tooltipIcon = document.getElementById("tooltipIcon");

            if (passwordInput) {
                passwordInput.addEventListener("input", function () {
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
                        passwordStrengthProgress.classList.remove("bg-success", "bg-warning");
                        passwordStrengthProgress.classList.add("bg-danger");
                        passwordStrengthText.textContent = "Weak";
                    } else if (strength < 80) {
                        passwordStrengthProgress.classList.remove("bg-danger", "bg-success");
                        passwordStrengthProgress.classList.add("bg-warning");
                        passwordStrengthText.textContent = "Moderate";
                    } else {
                        passwordStrengthProgress.classList.remove("bg-danger", "bg-warning");
                        passwordStrengthProgress.classList.add("bg-success");
                        passwordStrengthText.textContent = "Strong";
                    }
                });
            }

            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Toggle Password Visibility for Create Account Modal
            const toggleCreatePassword = document.getElementById("toggleCreatePassword");
            const createPasswordField = document.getElementById("password");

            if (toggleCreatePassword && createPasswordField) {
                toggleCreatePassword.addEventListener("click", function () {
                    const type = createPasswordField.getAttribute("type") === "password" ? "text" : "password";
                    createPasswordField.setAttribute("type", type);

                    const eyeIcon = toggleCreatePassword.querySelector("i");
                    if (type === "password") {
                        eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
                    } else {
                        eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
                    }
                });
            }

            // Toggle Password Visibility for Password Confirmation Modal
            const togglePassword = document.getElementById("togglePassword");
            const adminPasswordField = document.getElementById("adminPassword");

            if (togglePassword && adminPasswordField) {
                togglePassword.addEventListener("click", function () {
                    const type = adminPasswordField.getAttribute("type") === "password" ? "text" : "password";
                    adminPasswordField.setAttribute("type", type);

                    const eyeIcon = togglePassword.querySelector("i");
                    if (type === "password") {
                        eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
                    } else {
                        eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
                    }
                });
            }

            // Toggle Password Visibility for Confirm Password Field
            const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
            const confirmPasswordField = document.getElementById("confirm_password");

            if (toggleConfirmPassword && confirmPasswordField) {
                toggleConfirmPassword.addEventListener("click", function () {
                    const type = confirmPasswordField.getAttribute("type") === "password" ? "text" : "password";
                    confirmPasswordField.setAttribute("type", type);

                    const eyeIcon = toggleConfirmPassword.querySelector("i");
                    if (type === "password") {
                        eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
                    } else {
                        eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
                    }
                });
            }

            // Password Confirmation Form Handling
            const passwordConfirmationForm = document.getElementById('passwordConfirmationForm');
            const passwordError = document.getElementById('passwordError');
            const adminPasswordInput = document.getElementById('adminPassword');
            const passwordConfirmationModal = new bootstrap.Modal(document.getElementById('passwordConfirmationModal'));

            if (passwordConfirmationForm) {
                passwordConfirmationForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const adminPassword = adminPasswordInput.value;

                    fetch('../../controller/admin_confirm_password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `adminPassword=${encodeURIComponent(adminPassword)}`,
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                passwordError.style.display = 'none';
                                passwordConfirmationModal.hide(); // Close the password confirmation modal

                                // Perform the action based on the actionType
                                const actionType = document.getElementById('actionType').value;
                                const actionData = JSON.parse(document.getElementById('actionData').value);

                                if (actionType === 'create_account') {
                                    const signupModal = new bootstrap.Modal(document.getElementById('signupModal'));
                                    signupModal.show(); // Open the Create Account Modal
                                } else if (actionType === 'edit_account') {
                                    fetch(`../../controller/edit_account.php?id=${actionData.userId}`)
                                        .then(response => response.json())
                                        .then(userData => {
                                            if (userData.error) {
                                                alert(userData.error);
                                            } else if (userData.success && userData.data) {
                                                // Populate the Edit Account Modal with user data
                                                document.getElementById('edit_user_id').value = userData.data.user_id;
                                                document.getElementById('edit_username').value = userData.data.username;
                                                document.getElementById('edit_email').value = userData.data.email;
                                                document.getElementById('edit_role').value = userData.data.role;

                                                // Open the Edit Account Modal
                                                const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                                                editUserModal.show();
                                            } else {
                                                alert("Failed to fetch user data.");
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert("An error occurred while fetching user data.");
                                        });
                                } else if (actionType === 'delete_account') {
                                    const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                                    deleteConfirmationModal.show(); // Open the Delete Confirmation Modal
                                }
                            } else {
                                passwordError.style.display = 'block'; // Show error if password is incorrect
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                });
            }

            // Clear the password field and error message when the modal is hidden
            document.getElementById('passwordConfirmationModal').addEventListener('hidden.bs.modal', function () {
                adminPasswordInput.value = '';
                passwordError.style.display = 'none';

                const eyeIcon = document.querySelector('#togglePassword i');
                if (eyeIcon) {
                    eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
                }
                if (adminPasswordField) {
                    adminPasswordField.setAttribute("type", "password");
                }
            });

            // Handle Create Account Form Submission
            const createAccountForm = document.getElementById('createAccountForm');

            if (createAccountForm) {
                createAccountForm.addEventListener('submit', function (e) {
                    e.preventDefault(); // Prevent default form submission

                    // Create FormData object
                    const formData = new FormData(createAccountForm);

                    // Send form data to the server using fetch
                    fetch('../../controller/admin_register.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert("An error occurred while creating the account.");
                        });
                });
            }

            // Handle Edit Account Form Submission
            const editUserForm = document.getElementById('editUserForm');

            if (editUserForm) {
                editUserForm.addEventListener('submit', function (e) {
                    e.preventDefault(); // Prevent default form submission

                    // Create FormData object
                    const formData = new FormData(editUserForm);

                    // Send form data to the server using fetch
                    fetch('../../controller/edit_account.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message || "Account updated successfully!");
                                if (data.redirect) {
                                    window.location.href = data.redirect; // Redirect if redirect URL is provided
                                } else {
                                    window.location.reload(); // Fallback to reload the page
                                }
                            } else {
                                alert(data.error || "An error occurred while updating the account.");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert("An error occurred while updating the account.");
                        });
                });
            }

            // Handle the Delete Confirmation Button Click
            document.getElementById('confirmDeleteButton').addEventListener('click', function () {
                if (!userIdToDelete) {
                    alert("No user selected for deletion.");
                    return;
                }

                fetch(`../../controller/delete_account.php?id=${userIdToDelete}`, {
                    method: 'GET'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || "Account deleted successfully!");
                            window.location.reload();
                        } else {
                            alert(data.message || "Failed to delete account.");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert("An error occurred while deleting the account.");
                    });

                // Hide the Delete Confirmation Modal
                const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                deleteConfirmationModal.hide();
            });
        });
    </script>
</body>

</html>