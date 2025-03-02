<?php
session_start();
require '../model/connect.php';

header('Content-Type: application/json'); // Set response header to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields are present
    if (!isset($_POST['comment_id'], $_POST['reply_content'])) {
        echo json_encode(['error' => 'Missing parameters.']);
        exit;
    }

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'You need to be logged in to reply.']);
        exit;
    }

    $comment_id = intval($_POST['comment_id']);
    $reply_content = trim($_POST['reply_content']);
    $user_id = $_SESSION['user_id'];

    // Validate reply content
    if (empty($reply_content)) {
        echo json_encode(['error' => 'Reply content cannot be empty.']);
        exit;
    }

    // Insert the reply into the database
    $stmt = $CONN->prepare("INSERT INTO comment_replies (comment_id, user_id, reply_content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $comment_id, $user_id, $reply_content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reply added successfully.']);
    } else {
        error_log("Error in add_reply_user.php: " . $stmt->error);
        echo json_encode(['error' => 'Failed to add reply: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

$CONN->close();
?>