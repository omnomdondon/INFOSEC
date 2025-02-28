<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require Composer's autoloader
$autoloadPath = __DIR__ . "/../vendor/autoload.php";

if (!file_exists($autoloadPath)) {
    die("Autoload file not found! Path: $autoloadPath");
}

require $autoloadPath;

// Debug whether PHPMailer is loaded
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("PHPMailer class not found! Check Composer installation.");
}

// Create PHPMailer instance
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'brandon1203kennethdc@gmail.com';
$mail->Password = 'uhtf jasq hbwi xigc'; // App-specific password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->isHTML(true);

return $mail;
