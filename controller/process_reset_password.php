<?php
session_start(); // Start the session to use session variables
require __DIR__ . "/../model/connect.php";

$mysqli = require __DIR__ . "/../model/connect.php"; // Get database connection

if (!$mysqli) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$token = $_POST["token"];
$reset_password = $_POST["reset_password"];
$password_confirmation = $_POST["password_confirmation"];

// Validate input fields
if (empty($token) || empty($reset_password) || empty($password_confirmation)) {
    $_SESSION['error_message'] = "All fields are required.";
    header("Location: reset_password.php?token=$token");
    exit;
}

// Check if the password meets security requirements
if (strlen($reset_password) < 8) {
    $_SESSION['error_message'] = "Password must be at least 8 characters.";
    header("Location: reset_password.php?token=$token");
    exit;
}

if (!preg_match("/[a-z]/i", $reset_password)) {
    $_SESSION['error_message'] = "Password must contain at least one letter.";
    header("Location: reset_password.php?token=$token");
    exit;
}

if (!preg_match("/[0-9]/", $reset_password)) {
    $_SESSION['error_message'] = "Password must contain at least one number.";
    header("Location: reset_password.php?token=$token");
    exit;
}

if ($reset_password !== $password_confirmation) {
    $_SESSION['error_message'] = "Passwords must match.";
    header("Location: reset_password.php?token=$token");
    exit;
}

// Check if the token exists in the database
$token_hash = hash("sha256", $token);
$sql = "SELECT user_id, reset_token_hash, reset_token_expires_at FROM users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If no valid user or token, clear expired token
if (!$user) {
    $_SESSION['error_message'] = "Invalid or expired token.";
    header("Location: reset_password.php?token=$token");
    exit;
}

// Check if the token has expired
$current_time = time();
$expiry_time = strtotime($user["reset_token_expires_at"]);

if ($expiry_time <= $current_time) {
    // Clear expired token
    $sql = "UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE reset_token_hash = ?";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        die("Failed to prepare statement: " . $mysqli->error);
    }

    $stmt->bind_param("s", $token_hash);
    $stmt->execute();

    // Debugging: Check if the query was successful
    if ($stmt->affected_rows > 0) {
        error_log("Token fields cleared for token hash: $token_hash");
    } else {
        error_log("No rows affected for token hash: $token_hash");
    }

    $_SESSION['error_message'] = "Token has expired. Please request a new password reset link.";
    header("Location: reset_password.php?token=$token");
    exit;
}

// Hash the new password using bcrypt
$password_hash = password_hash($reset_password, PASSWORD_BCRYPT);

// Update the password and clear the reset token
$sql = "UPDATE users
        SET password = ?, 
            reset_token_hash = NULL, 
            reset_token_expires_at = NULL 
        WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("si", $password_hash, $user["user_id"]);
$stmt->execute();

// Debugging: Check if the query was successful
if ($stmt->affected_rows > 0) {
    error_log("Password updated and token fields cleared for user ID: " . $user["user_id"]);
} else {
    error_log("No rows affected for user ID: " . $user["user_id"]);
}

// Destroy the session to prevent further access to the reset password page
session_destroy();

$_SESSION['success_message'] = "Password updated successfully. You can now log in.";
header("Location: ../index.php"); // Redirect to the login page
exit;
?>