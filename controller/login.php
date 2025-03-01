<?php
session_start();

// Debugging: Check if session variables are being set correctly
error_log("Session variables before login:");
error_log("User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
error_log("Username: " . ($_SESSION['username'] ?? 'Not set'));
error_log("Role: " . ($_SESSION['role'] ?? 'Not set'));

// Include database connection
$CONN = require("../model/connect.php");

// Debugging: Log database connection status
error_log("Database connection status: " . ($CONN ? "Connected" : "Failed"));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signIn'])) {
    $identifier = $_POST['email']; // Accepts username or email
    $password = $_POST['password'];

    // Query to check if identifier matches username or email
    $query = $CONN->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    if (!$query) {
        error_log("Database query preparation failed: " . $CONN->error);
        die("Database query failed: " . $CONN->error);
    }

    $query->bind_param("ss", $identifier, $identifier);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password using bcrypt
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent session fixation

            // Store user details in session
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store user role in session
            $_SESSION['user_id'] = $user['user_id']; // Store user_id in session
            $_SESSION['alert_message'] = "Welcome, " . htmlspecialchars($user['username']) . "!";

            // Debugging: Check if session variables are being set correctly
            error_log("Session variables after login:");
            error_log("User ID: " . ($_SESSION['user_id'] ?? 'Not set'));
            error_log("Username: " . ($_SESSION['username'] ?? 'Not set'));
            error_log("Role: " . ($_SESSION['role'] ?? 'Not set'));

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                error_log('User role is admin. Username: ' . $user['username']); // For debugging
                header("Location: ../view/dashboard_pages/admin_dashboard.php");
                exit;
            } elseif ($user['role'] === 'user') {
                header("Location: ../view/homepages/homepage1.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid user role!";
                header("Location: ../index.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid username/email or password!";
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Invalid username/email or password!";
        header("Location: ../index.php");
        exit;
    }
}
?>