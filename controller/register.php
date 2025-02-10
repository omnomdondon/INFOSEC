<?php
session_start();
include '../model/connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signUp'])) {
    $username = trim($_POST['username']);
    $firstName = trim($_POST['fName']);
    $lastName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate passwords
    if ($password !== $confirmPassword) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header("Location: ../index.php#signupForm");
        exit;
    }

    // Password strength validation
    if (!preg_match("/^(?=.{12,})/", $password) || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[a-z]/", $password) || 
        !preg_match("/[0-9]/", $password) || 
        !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $_SESSION['error_message'] = "Password must be at least 12 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
        header("Location: ../index.php#signupForm");
        exit;
    }

    // Hash password using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if username or email already exists
    $checkQuery = $CONN->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $checkQuery->bind_param("ss", $username, $email);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Username or Email already exists!";
        header("Location: ../index.php#signupForm");
        exit;
    }

    // Insert the new user with hashed password
    $insertQuery = $CONN->prepare("INSERT INTO users (username, firstName, lastName, email, password) VALUES (?, ?, ?, ?, ?)");
    $insertQuery->bind_param("sssss", $username, $firstName, $lastName, $email, $hashedPassword);

    if ($insertQuery->execute()) {
        $_SESSION['success_message'] = "Registration successful! You can now log in.";
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['error_message'] = "An error occurred during registration. Please try again.";
        header("Location: ../index.php#signupForm");
        exit;
    }

    // Close database connections
    $checkQuery->close();
    $insertQuery->close();
    $CONN->close();
}