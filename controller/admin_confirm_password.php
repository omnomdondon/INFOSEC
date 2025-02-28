<?php
session_start();

// Set response header to JSON
header('Content-Type: application/json');

// Debugging: Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

// Correct file path dynamically
$connect_path = realpath(__DIR__ . '/../model/connect.php');

// Debugging: Log the resolved path
error_log("Resolved path to connect.php: " . $connect_path);

if (!$connect_path || !file_exists($connect_path)) {
    echo json_encode(['success' => false, 'error' => 'Database connection file not found.', 'path' => $connect_path]);
    exit();
}

require $connect_path;

try {
    // Validate POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['adminPassword'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        exit();
    }

    // Sanitize input
    $inputPassword = trim($_POST['adminPassword']);

    // Prepare SQL query to fetch the admin's hashed password
    $query = "SELECT password FROM users WHERE role = 'admin' LIMIT 1";
    $stmt = $CONN->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database query preparation failed.']);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if an admin user was found
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminPasswordHash = $row['password'];

        // Verify password
        if (password_verify($inputPassword, $adminPasswordHash)) {
            // Password is correct
            echo json_encode(['success' => true]);
        } else {
            // Password is incorrect
            echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
        }
    } else {
        // No admin user found
        echo json_encode(['success' => false, 'error' => 'Admin user not found.']);
    }

    // Close statement
    $stmt->close();
} catch (Exception $e) {
    // Handle any exceptions
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    // Close database connection
    if (isset($CONN)) {
        $CONN->close();
    }
}

exit();
?>