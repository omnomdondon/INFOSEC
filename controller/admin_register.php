<?php
session_start();
include '../model/connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Function to send JSON response
function sendResponse($success, $message = '', $redirect = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = trim($_POST['username']);
    $firstName = trim($_POST['firstName'] ?? $_POST['fName']); // Handle both admin and user forms
    $lastName = trim($_POST['lastName'] ?? $_POST['lName']); // Handle both admin and user forms
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'] ?? $_POST['confirmPassword']; // Handle both admin and user forms
    $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // Default role is 'user'

    // Validate passwords
    if ($password !== $confirmPassword) {
        sendResponse(false, "Passwords do not match!");
    }

    // Password strength validation
    if (!preg_match("/^(?=.{12,})/", $password) || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[a-z]/", $password) || 
        !preg_match("/[0-9]/", $password) || 
        !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        sendResponse(false, "Password must be at least 12 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.");
    }

    // Hash password using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if username or email already exists
    $checkQuery = $CONN->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $checkQuery->bind_param("ss", $username, $email);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        sendResponse(false, "Username or Email already exists!");
    }

    // Insert the new user with hashed password
    $insertQuery = $CONN->prepare("INSERT INTO users (username, firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $insertQuery->bind_param("ssssss", $username, $firstName, $lastName, $email, $hashedPassword, $role);

    if ($insertQuery->execute()) {
        // Set the correct redirect URL
        $redirectUrl = "../view/dashboard_pages/account_management.php"; // Adjust this path as needed
        sendResponse(true, "Account created successfully!", $redirectUrl);
    } else {
        sendResponse(false, "An error occurred during registration. Please try again.");
    }
}
?>