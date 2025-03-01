<?php
session_start();

// Include the database connection
require '../model/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to reply.'); window.history.back();</script>";
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['comment_id'], $_POST['reply_content'])) {
        echo "<script>alert('Missing parameters.'); window.history.back();</script>";
        exit;
    }

    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];
    $reply_content = trim($_POST['reply_content']);

    if (empty($reply_content)) {
        echo "<script>alert('Reply content cannot be empty.'); window.history.back();</script>";
        exit;
    }

    // Insert the reply
    $stmt = $CONN->prepare("INSERT INTO comment_replies (comment_id, user_id, reply_content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $comment_id, $user_id, $reply_content);

    if ($stmt->execute()) {
        echo "<script>alert('Reply added successfully.'); window.location.href = '../../view/homepages/homepage1.php';</script>";
    } else {
        error_log("Failed to add reply: " . $stmt->error);
        echo "<script>alert('Failed to add reply: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}

$CONN->close();
?>
