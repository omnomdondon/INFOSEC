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

// Handle form submission for account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user'; // Automatically set role as 'user'

        // Insert new user into the database
        $insert_query = "INSERT INTO users (firstName, lastName, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $CONN->prepare($insert_query);
        $stmt->bind_param('ssssss', $first_name, $last_name, $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Account created successfully!');</script>";
        } else {
            echo "<script>alert('Error creating account. Please try again.');</script>";
        }
    }
}
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

    </div>

    <script>
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

        // Handle form submission
        document.getElementById("editUserForm").addEventListener("submit", function (event) {
            event.preventDefault();

            const userId = document.getElementById("edit_user_id").value;
            const username = document.getElementById("edit_username").value;
            const email = document.getElementById("edit_email").value;
            const role = document.getElementById("edit_role").value;

            fetch("../../controller/edit_account.php", {
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