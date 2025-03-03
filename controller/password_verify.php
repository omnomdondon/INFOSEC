<?php
// Simulated stored hashed password (e.g., from database)
$stored_hash = '$2y$10$Af4T4LZP1mLxxJBBvoX7w.vJDspTgWiPgGFrYqq5N2gTsTQtQ9Ham'; // Example bcrypt hash

// User-provided password (from login form)
$input_password = 'mypassword123'; // Change this to test

// Verify if the input password matches the stored hash
if (password_verify($input_password, $stored_hash)) {
    echo "Password is correct!";
} else {
    echo "Invalid password.";
}
?>
