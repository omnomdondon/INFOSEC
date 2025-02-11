<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// require __DIR__ . "/../vendor/autoload.php";
require dirname(__DIR__) . "/vendor/autoload.php";

$mail = new PHPMailer(true);

// Set up SMTP server
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
$mail->SMTPAuth = true;
$mail->Username = 'brandon1203kennethdc@gmail.com'; // Your Gmail address
$mail->Password = 'uhtf jasq hbwi xigc'; // Use app-specific password if 2FA is enabled
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
$mail->Port = 587; // TLS Port

$mail->isHTML(true);

return $mail;
