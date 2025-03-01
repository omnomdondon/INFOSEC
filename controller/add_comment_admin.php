<?php
session_start();
include '../model/connect.php';

header('Content-Type: application/json'); // Set response header to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if password is confirmed
    if (!isset($_SESSION['password_confirmed']) || !$_SESSION['password_confirmed']) {
        echo json_encode(['error' => 'Password confirmation required.']);
        exit;
    }

    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment']);

    // Check if the user is logged in as an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['error' => 'You need to be logged in as an admin to comment.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $author = $_SESSION['username'];

    if (empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty.']);
        exit;
    }

    // Check if the post exists
    $stmt = $CONN->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_count);
    $stmt->fetch();
    $stmt->close();

    if ($post_count == 0) {
        echo json_encode(['error' => 'The post you are trying to comment on does not exist.']);
        exit;
    }

    // Insert the comment
    $stmt = $CONN->prepare("INSERT INTO comments (author, user_id, comment, post_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $author, $user_id, $comment, $post_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment submitted successfully.']);
    } else {
        error_log("Error in add_comment_admin.php: " . $stmt->error);
        echo json_encode(['error' => 'Failed to submit comment: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

$CONN->close();
?>