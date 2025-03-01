<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Check if password is confirmed
if (!isset($_SESSION['password_confirmed']) || !$_SESSION['password_confirmed']) {
    http_response_code(403);
    echo json_encode(['error' => 'Password confirmation required.']);
    exit;
}

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

include '../model/connect.php';

// Check database connection
if (!$CONN) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Handle POST request to update a post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = intval($_POST['post_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate input
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content cannot be empty.']);
        exit;
    }

    if (strlen($title) > 255) {
        http_response_code(400);
        echo json_encode(['error' => 'Title is too long.']);
        exit;
    }

    $updateQuery = "UPDATE posts SET title = ?, content = ? WHERE post_id = ?";
    $updateStmt = $CONN->prepare($updateQuery);
    $updateStmt->bind_param('ssi', $title, $content, $postId);

    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(['success' => 'Post updated successfully.']);
        } else {
            http_response_code(304);
            echo json_encode(['message' => 'No changes made.']);
        }
    } else {
        error_log("Error updating post: " . $CONN->error);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update post.']);
    }
    $updateStmt->close();
    exit;
}

// Handle GET request to fetch post data for editing
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $postId = intval($_GET['id']);

    $postQuery = "SELECT post_id, title, content FROM posts WHERE post_id = ?";
    $stmt = $CONN->prepare($postQuery);
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode($post); // Return post data as JSON
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found.']);
    }
    $stmt->close();
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request.']);
?>