<?php
session_start();
include '../model/connect.php';

// Ensure the user is logged in and has 'admin' role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Check if the ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Error: No ID provided.']);
    exit;
}

$id = $_GET['id'];

// Debugging: Log the received ID
error_log("Received ID for deletion: " . $id);

// Prepare the SQL query
$query = "DELETE FROM users WHERE user_id = ?";
$stmt = $CONN->prepare($query);

// Check if the query preparation was successful
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing query: ' . $CONN->error]);
    exit;
}

// Bind the parameter and execute the query
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account deleted successfully.']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
    exit;
}
?>