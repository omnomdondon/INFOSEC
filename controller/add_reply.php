<?php
session_start();

// Include the database connection
require '../model/connect.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['comment_id'], $_POST['user_id'], $_POST['reply_content'])) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters.']);
        exit;
    }

    $comment_id = intval($_POST['comment_id']);
    $user_id = intval($_POST['user_id']);
    $reply_content = trim($_POST['reply_content']);

    if (empty($reply_content)) {
        echo json_encode(['success' => false, 'error' => 'Reply content cannot be empty.']);
        exit;
    }

    // Prepare and execute the SQL query to insert the reply
    $stmt = $CONN->prepare("INSERT INTO comment_replies (comment_id, user_id, reply_content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $comment_id, $user_id, $reply_content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reply added successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add reply: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

// Close the connection
$CONN->close();
?>