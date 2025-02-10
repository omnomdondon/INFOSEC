<?php

$email = $_POST["email"];

// Generate a random token
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);

// Set expiry for the token (5 minutes)
$expiry = date("Y-m-d H:i:s", time() + 60 * 5);

$mysqli = require __DIR__ . "/../model/connect.php";

if (!$mysqli instanceof mysqli) {
    die("Failed to establish a database connection.");
}

// Update the database with the reset token and its expiry
$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($mysqli->affected_rows) {

    // Include PHPMailer to send email
    require __DIR__ . "/mailer.php";

    $mail->setFrom("noreply@example.com", "No Reply");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Request";
    $mail->Body = <<<END
        Click <a href="http://127.0.0.1/php/controller/reset_password.php?token=$token">here</a> 
        to reset your password.
        END;

    try {
        $mail->send();
        // Set success message in session
        session_start();
        $_SESSION['success_message'] = "Message sent, please check your inbox.";
    } catch (Exception $e) {
        // Set error message in session
        session_start();
        $_SESSION['error_message'] = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
} else {
    // Set error message in session if no user is found
    session_start();
    $_SESSION['error_message'] = "No user found with this email address.";
}

// Redirect back to the password reset page
header("Location: ../view/password_reset_page.php");
exit;
?>