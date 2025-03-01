<?php
session_start();

// Include the database connection
require '../model/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to comment.']);
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required parameters
    if (!isset($_POST['post_id'], $_POST['user_id'], $_POST['author'], $_POST['comment'])) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters.']);
        exit;
    }

    $post_id = intval($_POST['post_id']);
    $user_id = intval($_POST['user_id']);
    $author = trim($_POST['author']);
    $comment_content = trim($_POST['comment']);

    // Validate comment content
    if (empty($comment_content)) {
        echo json_encode(['success' => false, 'error' => 'Comment content cannot be empty.']);
        exit;
    }

    // Insert the comment into the database
    $stmt = $CONN->prepare("INSERT INTO comments (post_id, user_id, author, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $post_id, $user_id, $author, $comment_content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment added successfully.']);
    } else {
        error_log("Failed to add comment: " . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Failed to add comment.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$CONN->close();
?>