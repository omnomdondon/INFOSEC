<?php
session_start();
require '../model/connect.php'; // Ensure correct path

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['comment_id'], $_POST['user_id'], $_POST['reply_content'])) {
        echo json_encode(["error" => "Missing parameters."]);
        exit;
    }

    $comment_id = intval($_POST['comment_id']);
    $user_id = intval($_POST['user_id']);
    $reply_content = trim($_POST['reply_content']);

    if (empty($reply_content)) {
        echo json_encode(["error" => "Reply content cannot be empty."]);
        exit;
    }

    $stmt = $CONN->prepare("INSERT INTO comment_replies (comment_id, user_id, reply_content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $comment_id, $user_id, $reply_content);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Reply added successfully."]);
    } else {
        echo json_encode(["error" => "Failed to add reply."]);
    }

    $stmt->close();
    $CONN->close();
} else {
    echo json_encode(["error" => "Invalid request method."]);
}
?>
