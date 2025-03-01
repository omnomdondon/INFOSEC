<?php
// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

// Include database connection
require '../model/connect.php'; // Corrected file path

// Check if database connection is successful
if (!$CONN) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

try {
    // Validate POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['adminPassword'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        exit();
    }

    // Sanitize input
    $inputPassword = trim($_POST['adminPassword']);

    // Fetch the admin's hashed password from the database
    $query = "SELECT password FROM users WHERE username = ? AND role = 'admin'";
    $stmt = $CONN->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database query preparation failed.']);
        exit();
    }

    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($adminPasswordHash);
    $stmt->fetch();
    $stmt->close();

    // Verify password
    if (password_verify($inputPassword, $adminPasswordHash)) {
        // Password is correct, set a session flag
        $_SESSION['password_confirmed'] = true;
        echo json_encode(['success' => true]);
    } else {
        // Password is incorrect
        echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
    }
} catch (Exception $e) {
    // Handle any exceptions
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    // Close database connection
    if (isset($CONN)) {
        $CONN->close();
    }
}
?>