<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response header to JSON
header('Content-Type: application/json');

// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

// Include database connection
$connectPath = __DIR__ . '/../model/connect.php'; // Correct path
error_log("Attempting to include: " . $connectPath); // Debugging: Log the path

if (!file_exists($connectPath)) {
    error_log("File not found: " . $connectPath); // Log the missing file
    echo json_encode(['success' => false, 'error' => 'Database connection file not found.']);
    exit();
}

require $connectPath; // Include the database connection file

// Check if database connection is successful
if (!$CONN) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

// Handle GET request (fetch post data)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['post_id'])) {
        echo json_encode(['success' => false, 'error' => 'Post ID is missing.']);
        exit();
    }

    $post_id = intval($_GET['post_id']); // Sanitize input

    try {
        $query = "SELECT * FROM posts WHERE post_id = ?";
        $stmt = $CONN->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $CONN->error);
        }
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc()); // Return post data as JSON
        } else {
            echo json_encode(['success' => false, 'error' => 'Post not found.']); // Return error as JSON
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]); // Return error as JSON
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Handle POST request (update post data)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['post_id']) || empty($_POST['title']) || empty($_POST['content'])) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']); // Return error as JSON
        exit();
    }

    $post_id = intval($_POST['post_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    try {
        // Update the post in the database
        $query = "UPDATE posts SET title = ?, content = ? WHERE post_id = ?";
        $stmt = $CONN->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $CONN->error);
        }
        $stmt->bind_param('ssi', $title, $content, $post_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Debugging: Log the success response
            error_log("Post updated successfully. Response: " . json_encode(['success' => true, 'message' => 'Post updated successfully.']));
            echo json_encode(['success' => true, 'message' => 'Post updated successfully.']); // Return success message as JSON
        } else {
            // Debugging: Log the error response
            error_log("Failed to update post. Response: " . json_encode(['success' => false, 'error' => 'Failed to update post.']));
            echo json_encode(['success' => false, 'error' => 'Failed to update post.']); // Return error as JSON
        }
    } catch (Exception $e) {
        // Debugging: Log the exception
        error_log("Exception occurred: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]); // Return error as JSON
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Handle invalid request methods
else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']); // Return error as JSON
}

// Close the database connection
if (isset($CONN)) {
    $CONN->close();
}
?>