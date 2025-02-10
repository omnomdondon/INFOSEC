<?php
$plainPassword = "hashed_password"; // Replace with your desired admin password
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashedPassword;
?>