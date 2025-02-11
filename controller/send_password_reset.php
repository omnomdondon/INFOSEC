<?php

session_start();

// Ensure email is provided
if (empty($_POST["email"])) {
    $_SESSION['error_message'] = "Email address is required.";
    header("Location: ../view/password_reset_page.php");
    exit;
}

$email = $_POST["email"];

// Connect to the database
$mysqli = require __DIR__ . "/../model/connect.php";
if (!$mysqli instanceof mysqli) {
    die("Failed to establish a database connection.");
}

// Check if email exists
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['error_message'] = "No user found with this email address.";
    header("Location: ../view/password_reset_page.php");
    exit;
}

// Generate a random token
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);

// Set expiry for the token (5 minutes - 60 * 5)
$expiry = date("Y-m-d H:i:s", time() + 30);

// Update the database with the reset token and its expiry
$sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
if ($stmt->execute() && $stmt->affected_rows > 0) {

    // Include PHPMailer to send email
    require __DIR__ . "/mailer.php";
    $mail->setFrom("noreply@example.com", "No Reply");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Request";
    $mail->Body = <<<END
        Click <a href="http://127.0.0.1/INFOSEC/controller/reset_password.php?token=$token">here</a> 
        to reset your password.
        END;

    try {
        $mail->send();
        $_SESSION['success_message'] = "Message sent, please check your inbox.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }

} else {
    $_SESSION['error_message'] = "Failed to update reset token in the database.";
}

header("Location: ../view/password_reset_page.php");
exit;

?>