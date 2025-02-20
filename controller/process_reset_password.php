<?php
session_start(); // Start the session to use session variables
require __DIR__ . "/../model/connect.php";

$mysqli = require __DIR__ . "/../model/connect.php"; // Get database connection

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
$sql = "SELECT user_id, reset_token_hash, reset_token_expires_at FROM users WHERE reset_token_hash IS NOT NULL";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$validUser = null;

while ($user = $result->fetch_assoc()) {
    // Compare the plain token to the hashed version in the database
    if (hash("sha256", $token) === $user["reset_token_hash"]) {
        $validUser = $user;
        break;
    }
}

// If no valid user or token, clear expired token
if (!$validUser || strtotime($validUser["reset_token_expires_at"]) <= time()) {
    // Clear expired or invalid token
    $sql = "UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE reset_token_hash IS NOT NULL";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();

    $_SESSION['error_message'] = "Invalid or expired token.";
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
$stmt->bind_param("si", $password_hash, $validUser["user_id"]);
$stmt->execute();

$_SESSION['success_message'] = "Password updated successfully. You can now log in.";
header("Location: ../index.php"); // Redirect to the login page
exit;
?>
