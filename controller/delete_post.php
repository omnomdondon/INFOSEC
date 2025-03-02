<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response header to JSON
header('Content-Type: application/json');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

// Include database connection
include '../model/connect.php';

// Function to delete a post
function deletePost($postId, $conn) {
    // Prepare query to delete the post
    $deleteQuery = "DELETE FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $postId);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];

    // Call the delete function
    if (deletePost($postId, $CONN)) {
        echo json_encode(['success' => 'Post deleted successfully.']);
    } else {
        echo json_encode(['error' => 'Error deleting post.']);
    }
    exit;
} else {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}
?>