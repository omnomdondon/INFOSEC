<?php
session_start();

// Include the database connection
require '../model/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to comment.'); window.history.back();</script>";
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['post_id'], $_POST['comment_content'])) {
        echo "<script>alert('Missing parameters.'); window.history.back();</script>";
        exit;
    }

    $post_id = intval($_POST['post_id']);
    $user_id = $_SESSION['user_id'];
    $comment_content = trim($_POST['comment_content']);

    if (empty($comment_content)) {
        echo "<script>alert('Comment content cannot be empty.'); window.history.back();</script>";
        exit;
    }

    // Insert the comment
    $stmt = $CONN->prepare("INSERT INTO comments (post_id, user_id, comment_content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $user_id, $comment_content);

    if ($stmt->execute()) {
        echo "<script>alert('Comment added successfully.'); window.location.href = '../../view/homepages/homepage1.php';</script>";
    } else {
        error_log("Failed to add comment: " . $stmt->error);
        echo "<script>alert('Failed to add comment: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}

$CONN->close();
?>
