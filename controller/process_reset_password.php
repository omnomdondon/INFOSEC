<?php
require __DIR__ . "/../model/connect.php";

$token = $_POST["token"];
$reset_password = $_POST["reset_password"];
$password_confirmation = $_POST["password_confirmation"];

// Validate input fields
if (empty($token) || empty($reset_password) || empty($password_confirmation)) {
    die("All fields are required.");
}

// Check if the password meets security requirements
if (strlen($reset_password) < 8) {
    die("Password must be at least 8 characters.");
}

if (!preg_match("/[a-z]/i", $reset_password)) {
    die("Password must contain at least one letter.");
}

if (!preg_match("/[0-9]/", $reset_password)) {
    die("Password must contain at least one number.");
}

if ($reset_password !== $password_confirmation) {
    die("Passwords must match.");
}

// Check if the token exists in the database
$sql = "SELECT id, reset_token_hash, reset_token_expires_at FROM users WHERE reset_token_hash IS NOT NULL";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$validUser = null;

while ($user = $result->fetch_assoc()) {
    if (password_verify($token, $user["reset_token_hash"])) {
        $validUser = $user;
        break;
    }
}

if (!$validUser) {
    die("Invalid or expired token.");
}

// Check if the token has expired
if (strtotime($validUser["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}

// Hash the new password using bcrypt
$password_hash = password_hash($reset_password, PASSWORD_BCRYPT);

// Update the password and clear the reset token
$sql = "UPDATE users
        SET password = ?, 
            reset_token_hash = NULL, 
            reset_token_expires_at = NULL 
        WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("si", $password_hash, $validUser["id"]);
$stmt->execute();

echo "Password updated successfully. You can now log in.";

// $token = $_POST["token"];
// $token_hash = hash("sha256", $token);

// $mysqli = require __DIR__ . "/../model/connect.php";

// // Check if the token exists in the database
// $sql = "SELECT * FROM users WHERE reset_token_hash = ?";
// $stmt = $mysqli->prepare($sql);
// $stmt->bind_param("s", $token_hash);
// $stmt->execute();

// $result = $stmt->get_result();
// $user = $result->fetch_assoc();

// if ($user === null) {
//     die("Token not found or invalid.");
// }

// if (strtotime($user["reset_token_expires_at"]) <= time()) {
//     die("Token has expired.");
// }

// if (strlen($_POST["reset_password"]) < 8) {
//     die("Password must be at least 8 characters.");
// }

// if (!preg_match("/[a-z]/i", $_POST["reset_password"])) {
//     die("Password must contain at least one letter.");
// }

// if (!preg_match("/[0-9]/", $_POST["reset_password"])) {
//     die("Password must contain at least one number.");
// }

// if ($_POST["reset_password"] !== $_POST["password_confirmation"]) {
//     die("Passwords must match.");
// }

// $password_hash = password_hash($_POST["reset_password"], PASSWORD_DEFAULT);

// // Update the password and reset token details in the database
// $sql = "UPDATE users
//         SET password = ?,
//             reset_token_hash = NULL,
//             reset_token_expires_at = NULL
//         WHERE id = ?";
// $stmt = $mysqli->prepare($sql);
// $stmt->bind_param("ss", $password_hash, $user["id"]);
// $stmt->execute();

// echo "Password updated. You can now log in.";
?>