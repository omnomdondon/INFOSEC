<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include '../model/connect.php'; // Assuming the DB connection file is here

function deletePost($postId, $conn) {
    // Prepare query to delete the post
    $deleteQuery = "DELETE FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $postId);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];

    // Call the delete function
    if (deletePost($postId, $CONN)) {
        header('Location: ../view/dashboard_pages/admin_dashboard.php?success=Post deleted successfully');
    } else {
        header('Location: ../view/dashboard_pages/admin_dashboard.php?error=Error deleting post');
    }
    exit;
} else {
    header('Location: ../view/dashboard_pages/admin_dashboard.php?error=Invalid request');
    exit;
}
?>
