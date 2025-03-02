<?php
// Start session to access user information
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Include the database connection
include '../../model/connect.php';

// Check if the user is logged in (check if session has user data)
if (!isset($_SESSION['email']) || !isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../index.php");
    exit;
}

// Restrict access to users only (not admins)
if ($_SESSION['role'] === 'admin') {
    // Redirect admins to the admin dashboard or another appropriate page
    header("Location: admin_dashboard.php");
    exit;
}

// Retrieve the logged-in user's ID and role from the session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Query to fetch posts
$query = "SELECT post_id, title, content, created_at FROM posts ORDER BY created_at DESC";
$result = $CONN->query($query);

// Display success/error messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        echo '<script>alert("Comment added successfully!");</script>';
    } elseif ($_GET['status'] === 'error') {
        echo '<script>alert("Failed to add comment. Please try again.");</script>';
    }
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .post-card {
            position: relative;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .post-actions-top {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }

        .post-actions-top .btn {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        .post-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            margin-top: 0;
            padding-right: 120px;
        }

        .post-body {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .post-meta {
            font-size: 0.875rem;
            color: #777;
            margin-bottom: 15px;
        }

        .post-actions {
            position: relative;
            top: auto;
            right: auto;
            margin-top: 10px;
        }

        .comment {
            margin-top: 15px;
            padding: 10px;
            border-left: 3px solid #28a745;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .comment-author {
            font-weight: bold;
            color: #333;
        }

        .comment-text {
            color: #555;
            margin-top: 5px;
        }

        .comment-meta {
            font-size: 0.75rem;
            color: #777;
            margin-top: 5px;
        }

        .reply {
            margin-top: 10px;
            padding-left: 15px;
            border-left: 2px solid #6c757d;
        }

        .reply-author {
            font-weight: bold;
            color: #333;
        }

        .reply-text {
            color: #555;
            margin-top: 5px;
        }

        .reply-meta {
            font-size: 0.75rem;
            color: #777;
            margin-top: 5px;
        }

        .post-actions {
            position: relative;
            margin-top: 10px;
            text-align: left;
            /* Align the button to the left */
        }

        .post-actions .btn {
            margin-left: 5px;
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

        // Function to validate the comment form
        function validateCommentForm(postId) {
            const commentContent = document.getElementById(`commentText${postId}`).value.trim();

            if (!commentContent) {
                alert("Comment content cannot be empty.");
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }

        // Function to submit a comment
        function submitComment(postId) {
            const commentContent = document.getElementById(`commentText${postId}`).value.trim();
            const userId = <?php echo json_encode($user_id); ?>;
            const author = <?php echo json_encode($username); ?>;

            if (!commentContent) {
                alert("Comment content cannot be empty.");
                return;
            }

            // Submit the form data using fetch
            fetch('../../controller/add_comment_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&user_id=${userId}&author=${encodeURIComponent(author)}&comment=${encodeURIComponent(commentContent)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload(); // Reload the page to show the new comment
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit comment.');
                });
        }

        // Function to submit a reply
        function submitReply(commentId) {
            const replyContent = document.getElementById(`replyContent-${commentId}`).value;
            const comment_id = commentId;
            const user_id = <?php echo json_encode($user_id); ?>;

            fetch('../../controller/add_reply_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${comment_id}&user_id=${user_id}&reply_content=${encodeURIComponent(replyContent)}`
                })
                .then(response => response.json()) // Directly parse the response as JSON
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Reply added successfully!');
                        window.location.reload(); // Reload the page to show the new reply
                    } else {
                        alert(data.error || 'Failed to submit reply.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit reply.');
                });
        }

        // Function to fetch post data and populate the edit modal
        function fetchPostData(postId) {
            fetch(`../../controller/edit_post.php?id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById('editPostId').value = data.post_id;
                        document.getElementById('editTitle').value = data.title;
                        document.getElementById('editContent').value = data.content;

                        let modal = new bootstrap.Modal(document.getElementById('editPostModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error fetching post data:', error);
                    alert('Failed to fetch post data.');
                });
        }

        // Function to delete a post
        function deletePost(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                fetch('../../controller/delete_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `post_id=${postId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.success);
                            window.location.reload(); // Reload the page to reflect changes
                        } else {
                            alert(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete post.');
                    });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Add event listeners to all comment forms
            document.querySelectorAll("[id^='commentForm']").forEach(form => {
                form.addEventListener("submit", function(event) {
                    event.preventDefault();

                    const postId = this.querySelector("input[name='post_id']").value;
                    const userId = this.querySelector("input[name='user_id']").value;
                    const author = this.querySelector("input[name='author']").value;
                    const commentContent = this.querySelector("textarea[name='comment']").value.trim();

                    if (!commentContent) {
                        alert("Comment content cannot be empty.");
                        return;
                    }

                    // Submit the form data using fetch
                    fetch('../../controller/add_comment_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `post_id=${postId}&user_id=${userId}&author=${encodeURIComponent(author)}&comment=${encodeURIComponent(commentContent)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                window.location.reload(); // Reload the page to show the new comment
                            } else {
                                alert(data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to submit comment.');
                        });
                });
            });
        });
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
                    <a href="../../controller/logout.php" class="btn btn-danger">Logout</a>
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
                        <!-- Post Title -->
                        <div class="post-title"><?php echo htmlspecialchars($row['title']); ?></div>

                        <!-- Post Content -->
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>

                        <!-- Post Meta (Date) -->
                        <div class="post-meta">
                            Posted on <?php echo date("F j, Y, g:i A", strtotime($row['created_at'])); ?>
                        </div>

                        <!-- Move Comment Button Below the Date and Align Left -->
                        <div class="post-actions">
                            <button class="btn btn-primary btn-sm bg-success" data-bs-toggle="modal"
                                data-bs-target="#commentModal<?php echo $row['post_id']; ?>">
                                <i class="fas fa-comment"></i> Comment
                            </button>
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
                                        <button type="button" class="btn btn-sm btn-success mt-2" data-bs-toggle="modal"
                                            data-bs-target="#replyModal-<?php echo $comment['id']; ?>">
                                            <i class="fas fa-reply"></i> Reply
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
                                                            <input type="hidden" name="comment_id"
                                                                value="<?php echo $comment['id']; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                                            <div class="mb-3">
                                                                <label for="replyContent-<?php echo $comment['id']; ?>"
                                                                    class="form-label">Reply</label>
                                                                <textarea class="form-control"
                                                                    id="replyContent-<?php echo $comment['id']; ?>" name="reply_content"
                                                                    required></textarea>
                                                            </div>
                                                            <button type="button" class="btn btn-success"
                                                                onclick="submitReply(<?php echo $comment['id']; ?>)">Submit</button>
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
                                                        <div class="reply-text"><?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?>
                                                        </div>
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
                    <div class="modal fade" id="commentModal<?php echo $row['post_id']; ?>" tabindex="-1"
                        aria-labelledby="commentModalLabel<?php echo $row['post_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="commentModalLabel<?php echo $row['post_id']; ?>">Add a Comment
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="commentForm<?php echo $row['post_id']; ?>" onsubmit="return validateCommentForm(<?php echo $row['post_id']; ?>)">
                                        <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <input type="hidden" name="author" value="<?php echo $username; ?>">
                                        <div class="mb-3">
                                            <label for="commentText<?php echo $row['post_id']; ?>" class="form-label">Your
                                                Comment</label>
                                            <textarea class="form-control" id="commentText<?php echo $row['post_id']; ?>"
                                                name="comment" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">Submit</button>
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

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPostModalLabel">Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPostForm">
                        <input type="hidden" name="post_id" id="editPostId">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editContent" name="content" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="../../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>