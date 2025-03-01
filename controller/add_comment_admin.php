<?php
session_start();
include '../model/connect.php';

// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Set response header to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

// Check if database connection exists
if (!isset($CONN)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Check session authentication
if (!isset($_SESSION['password_confirmed']) || !$_SESSION['password_confirmed']) {
    http_response_code(401);
    echo json_encode(['error' => 'Password confirmation required.']);
    exit;
}

// Validate inputs
if (!isset($_POST['post_id'], $_POST['comment']) || empty(trim($_POST['comment']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

$post_id = intval($_POST['post_id']);
$comment = trim($_POST['comment']);

// Verify admin status
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'You need to be logged in as an admin to comment.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$author = $_SESSION['username'];

// Check if post exists
$stmt = $CONN->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($post_count);
$stmt->fetch();
$stmt->close();

if ($post_count == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'The post you are trying to comment on does not exist.']);
    exit;
}

// Insert comment
$stmt = $CONN->prepare("INSERT INTO comments (author, user_id, comment, post_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("sisi", $author, $user_id, $comment, $post_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment submitted successfully.']);
} else {
    error_log("Error in add_comment_admin.php: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit comment.']);
}

$stmt->close();
$CONN->close();
?>