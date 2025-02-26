<?php
// Start session to access user information
session_start();

// Include the database connection
include '../../model/connect.php';

// Check if the user is logged in (check if session has user data)
if (!isset($_SESSION['email']) || !isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: ../../index.php");
    exit;
}

// Retrieve the logged-in user's ID and name from the session
$user_id = $_SESSION['user_id']; // Ensure this is the correct session variable for user ID
$username = $_SESSION['username'];

// Query to fetch posts
$query = "SELECT post_id, title, content, created_at FROM posts ORDER BY created_at DESC"; // Include id for reference
$result = $CONN->query($query);

// Display success/error messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        echo '<div class="alert alert-success text-center">Comment added successfully!</div>';
    } elseif ($_GET['status'] === 'error') {
        echo '<div class="alert alert-danger text-center">Failed to add comment. Please try again.</div>';
    }
}

$result = $CONN->query($query);
if (!$result) {
    die("Query failed: " . $CONN->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Display</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .post-card {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .post-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .post-body {
            font-size: 1rem;
            color: #495057;
            margin-bottom: 15px;
        }

        .post-meta {
            font-size: 0.85rem;
            color: #adb5bd;
            margin-top: 10px;
        }

        .comment {
            margin-top: 15px;
            padding-left: 20px;
            border-left: 3px solid #28a745;
        }

        .comment-author {
            font-weight: bold;
        }

        .replies {
            margin-top: 10px;
            padding-left: 20px;
            border-left: 2px solid #6c757d;
        }

        .reply-author {
            font-weight: bold;
        }
    </style>

    <script>
        let timeout;

        function startTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logoutUser, 300000); // 5 minutes (300000ms) timeout
        }

        function logoutUser() {
            alert("Session expired due to inactivity. Redirecting to login page.");
            window.location.href = "../../controller/dashboard_logout.php";
        }

        // Reset timer on user activity
        document.addEventListener("mousemove", startTimer);
        document.addEventListener("keydown", startTimer);
        document.addEventListener("mousedown", startTimer); // Detects clicks
        document.addEventListener("wheel", startTimer); // Detects scrolling
        document.addEventListener("touchstart", startTimer); // Detects mobile touch

        startTimer(); // Initialize timer on page load

        // Function to submit a reply
        function submitReply(commentId) {
            const replyContent = document.getElementById(`replyContent-${commentId}`).value;
            const comment_id = commentId;
            const user_id = <?php echo json_encode($user_id); ?>;

            fetch('../../controller/add_reply.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `comment_id=${comment_id}&user_id=${user_id}&reply_content=${encodeURIComponent(replyContent)}`
            })
            .then(response => response.text()) // First, get the raw response as text
            .then(text => {
                console.log("Raw response:", text); // Log the raw response
                return JSON.parse(text); // Then try to parse it as JSON
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    window.location.reload(); // Reload the page to show the new reply
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit reply.');
            });
        }
    </script>
</head>

<body>
    <!-- Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">Simple Blog Website</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="ms-auto dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Hello, <?php echo htmlspecialchars($username); ?>!
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#logoutModal">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../../controller/logout.php" class="btn btn-success">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Body -->
    <div class="container mt-4">
        <h1>Simple Blog Website</h1>
        <div class="posts mt-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="post-card">
                        <div class="post-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>
                        <button class="btn btn-success mt-3" data-bs-toggle="modal"
                            data-bs-target="#commentModal<?php echo $row['post_id']; ?>">
                            Comment
                        </button>
                        <div class="post-meta">
                            Posted on <?php echo date("F j, Y, g:i A", strtotime($row['created_at'])); ?>
                        </div>

                        <!-- Fetch and display comments -->
                        <div class="comments mt-3">
                            <?php
                            // Query to fetch comments for the current post
                            $commentQuery = "SELECT id, comment, author, created_at FROM comments WHERE post_id = ? ORDER BY created_at ASC";
                            $stmt = $CONN->prepare($commentQuery);
                            $stmt->bind_param('i', $row['post_id']);
                            $stmt->execute();
                            $commentsResult = $stmt->get_result();
                            if ($commentsResult->num_rows > 0):
                                while ($comment = $commentsResult->fetch_assoc()):
                            ?>
                                    <div class="comment">
                                        <div class="comment-author"><?php echo htmlspecialchars($comment['author']); ?>:</div>
                                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                                        <div class="comment-meta">
                                            Commented on <?php echo date("F j, Y, g:i A", strtotime($comment['created_at'])); ?>
                                        </div>

                                        <!-- Reply Button -->
                                        <button type="button" class="btn btn-sm btn-secondary mt-2" data-bs-toggle="modal"
                                            data-bs-target="#replyModal-<?php echo $comment['id']; ?>">
                                            Reply
                                        </button>

                                        <!-- Reply Modal -->
                                        <div class="modal fade" id="replyModal-<?php echo $comment['id']; ?>" tabindex="-1"
                                            aria-labelledby="replyModalLabel-<?php echo $comment['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="replyModalLabel-<?php echo $comment['id']; ?>">
                                                            Reply to Comment
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form id="replyForm-<?php echo $comment['id']; ?>">
                                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                            <div class="mb-3">
                                                                <label for="replyContent-<?php echo $comment['id']; ?>"
                                                                    class="form-label">Reply</label>
                                                                <textarea class="form-control"
                                                                    id="replyContent-<?php echo $comment['id']; ?>"
                                                                    name="reply_content" required></textarea>
                                                            </div>
                                                            <button type="button" class="btn btn-success" onclick="submitReply(<?php echo $comment['id']; ?>)">Submit</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Replies Section -->
                                        <div class="replies mt-2 ms-4">
                                            <?php
                                            // Query to fetch replies for the current comment, joining with the users table to get the username
                                            $replyQuery = "SELECT r.reply_content, u.username AS author, r.created_at 
                                                           FROM comment_replies r 
                                                           JOIN users u ON r.user_id = u.user_id 
                                                           WHERE r.comment_id = ? 
                                                           ORDER BY r.created_at ASC";
                                            $replyStmt = $CONN->prepare($replyQuery);
                                            $replyStmt->bind_param('i', $comment['id']);
                                            $replyStmt->execute();
                                            $replyResult = $replyStmt->get_result();

                                            if ($replyResult->num_rows > 0):
                                                while ($reply = $replyResult->fetch_assoc()): ?>
                                                    <div class="reply">
                                                        <div class="reply-author"><?php echo htmlspecialchars($reply['author']); ?>:</div>
                                                        <div class="reply-text"><?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?></div>
                                                        <div class="reply-meta">
                                                            Replied on <?php echo date("F j, Y, g:i A", strtotime($reply['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                <?php endwhile;
                                            endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile;
                            endif; ?>
                        </div>
                    </div>

                    <!-- Comment Modal -->
                    <div class="modal fade" id="commentModal<?php echo $row['post_id']; ?>" tabindex="-1" aria-labelledby="commentModalLabel<?php echo $row['post_id']; ?>"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="commentModalLabel<?php echo $row['post_id']; ?>">Add a Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="../../controller/add_comment.php" method="POST">
                                        <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <input type="hidden" name="author" value="<?php echo $username; ?>">
                                        <div class="mb-3">
                                            <label for="commentText<?php echo $row['post_id']; ?>" class="form-label">Your Comment</label>
                                            <textarea class="form-control" id="commentText<?php echo $row['post_id']; ?>" name="comment" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">Submit Comment</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="../../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>