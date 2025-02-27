<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

include '../model/connect.php'; // Include the database connection

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