<?php
session_start();
include('connect.php');

// Assuming the admin is logged in and their ID is stored in the session
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page if the admin is not logged in
    header('Location: login.php');
    exit();
}

// Fetch the admin's hashed password from the database
$query = "SELECT password FROM users WHERE role = 'admin' LIMIT 1"; // Assuming 'admin' role is used
$result = $CONN->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $adminPasswordHash = $row['password']; // The password is stored as a hash
} else {
    die("Admin user not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adminPassword'])) {
    $inputPassword = $_POST['adminPassword'];

    // Check if the entered password matches the hashed password in the database
    if (password_verify($inputPassword, $adminPasswordHash)) {
        // Password is correct, proceed with the action
        echo "<script>alert('Password confirmed. Proceeding with action.');</script>";
    } else {
        // Incorrect password
        echo "<script>alert('Incorrect password. Please try again.');</script>";
    }
}
?>

<!-- Bootstrap Modal for Password Confirmation -->
<div class="modal fade" id="confirmPasswordModal" tabindex="-1" aria-labelledby="confirmPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmPasswordModalLabel">Confirm Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>To proceed with this action, please confirm your password.</p>
                <form action="admin_confirm_password.php" method="POST">
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Admin Password</label>
                        <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Button to trigger the modal -->
<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#confirmPasswordModal">
    Perform Admin Action
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Close the database connection
$CONN->close();
?>