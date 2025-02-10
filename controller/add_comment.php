<?php
// Start the session to access the logged-in user's data
session_start();
var_dump($_SESSION);

// Include the database connection
include '../model/connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted data
    $post_id = intval($_POST['post_id']); // Post ID (foreign key)
    $comment = trim($_POST['comment']); 

    // Ensure the user is logged in and session variables are correctly set
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && !empty($_SESSION['user_id']) && !empty($_SESSION['username'])) {
        $user_id = $_SESSION['user_id']; // Get the logged-in user's ID from session
        $author = $_SESSION['username']; // Get the logged-in user's name from session
    } else {
        // If the user is not logged in or session variables are missing, handle the error
        echo "You need to be logged in to comment, or username is missing.";
        exit;
    }

    // Validate input
    if (empty($comment)) {
        echo "Comment cannot be empty.";
        exit;
    }

    // Check if the post_id exists in the posts table
    $stmt = $CONN->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_count);
    $stmt->fetch();
    $stmt->close();

    if ($post_count == 0) {
        echo "The post you are trying to comment on does not exist.";
        exit;
    }

    // Prepare and execute the SQL query to insert the comment
    $stmt = $CONN->prepare("INSERT INTO comments (author, user_id, comment, post_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $author, $user_id, $comment, $post_id);

    if ($stmt->execute()) {
        // Redirect back to the homepage with a success status
        header("Location: ../view/dashboard_pages/admin_dashboard.php?status=success&closeModal=commentModal" . $post_id);
        exit;
    } else {
        // Handle error and show a message
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$CONN->close();
?>
