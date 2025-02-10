<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include '../../model/connect.php';

// Handle POST request to update a post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate input
    if (empty($title) || empty($content)) {
        echo json_encode(['error' => 'Title and content cannot be empty.']);
        exit;
    }

    if (strlen($title) > 255) {
        echo json_encode(['error' => 'Title is too long.']);
        exit;
    }

    $updateQuery = "UPDATE posts SET title = ?, content = ? WHERE post_id = ?";
    $updateStmt = $CONN->prepare($updateQuery);
    $updateStmt->bind_param('ssi', $title, $content, $postId);

    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['success' => 'Post updated successfully.']);
        } else {
            echo json_encode(['message' => 'No changes made.']);
        }
    } else {
        error_log("Error updating post: " . $CONN->error);
        echo json_encode(['error' => 'Failed to update post.']);
    }
    exit;
}

// Handle GET request to fetch post data for editing
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    $postQuery = "SELECT * FROM posts WHERE post_id = ?";
    $stmt = $CONN->prepare($postQuery);
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo json_encode($post); // Return post data as JSON
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request.']);
?>
