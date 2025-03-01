<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Include database connection
include '../../model/connect.php';

// Query to fetch users data with created_at column
$userQuery = "SELECT user_id, username, email, role, created_at FROM users";
$userResult = $CONN->query($userQuery);
if (!$userResult) {
    die("Error fetching users: " . $CONN->error);
}

// Query to fetch posts data
$postQuery = "SELECT post_id, title, content, created_at FROM posts";
$postResult = $CONN->query($postQuery);
if (!$postResult) {
    die("Error fetching posts: " . $CONN->error);
}

// Query to fetch comments data
$commentQuery = "SELECT c.id, c.comment, c.created_at, p.title AS post_title, u.username AS user_name 
                 FROM comments c 
                 JOIN posts p ON c.post_id = p.post_id
                 JOIN users u ON c.user_id = u.user_id";
$commentResult = $CONN->query($commentQuery);
if (!$commentResult) {
    die("Error fetching comments: " . $CONN->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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

        .comment-meta {
            color: #adb5bd;
        }

        .reply-meta {
            color: #adb5bd;
        }

        .reply-author {
            font-weight: bold;
        }
    </style>

    <script>
        // Function to toggle password visibility
        document.addEventListener("DOMContentLoaded", function() {
            const togglePassword = document.getElementById('togglePassword');
            const adminPassword = document.getElementById('adminPassword');

            if (togglePassword && adminPassword) {
                togglePassword.addEventListener('click', function() {
                    // Toggle the type attribute of the password input
                    const type = adminPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    adminPassword.setAttribute('type', type);

                    // Toggle the eye icon
                    const eyeIcon = togglePassword.querySelector('i');
                    if (type === 'password') {
                        eyeIcon.classList.remove('bi-eye-slash');
                        eyeIcon.classList.add('bi-eye');
                    } else {
                        eyeIcon.classList.remove('bi-eye');
                        eyeIcon.classList.add('bi-eye-slash');
                    }
                });
            }
        });

        let timeout;

        function startTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logoutUser, 300000); // 5 minutes (300000ms) timeout
        }

        function logoutUser() {
            alert("Session expired due to inactivity. Redirecting to login page.");
            window.location.href = "../../controller/dashboard_logout.php";
        }

        // Logout Confirmation Modal Handling
        document.addEventListener("DOMContentLoaded", function() {
            const logoutModal = document.getElementById('logoutConfirmationModal');
            const logoutLink = document.querySelector('a[data-bs-target="#logoutConfirmationModal"]');

            logoutLink.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                const modal = new bootstrap.Modal(logoutModal);
                modal.show();
            });

            // Handle the logout button click inside the modal
            const logoutButton = document.querySelector('#logoutConfirmationModal .btn-danger');
            logoutButton.addEventListener('click', function() {
                window.location.href = '../../controller/dashboard_logout.php'; // Redirect to logout page
            });
        });

        // Reset timer on user activity
        document.addEventListener("mousemove", startTimer);
        document.addEventListener("keydown", startTimer);
        document.addEventListener("mousedown", startTimer); // Detects clicks
        document.addEventListener("wheel", startTimer); // Detects scrolling
        document.addEventListener("touchstart", startTimer); // Detects mobile touch

        startTimer(); // Initialize timer on page load

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

        function submitReply(commentId) {
            const replyContent = document.getElementById(`replyContent-${commentId}`).value;

            fetch('../../controller/add_reply.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${commentId}&user_id=<?php echo $_SESSION['user_id']; ?>&reply_content=${encodeURIComponent(replyContent)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.success); // Show success message as an alert
                        window.location.reload(); // Reload the page to show the new reply
                    } else {
                        alert(data.error); // Show error message as an alert
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit reply.'); // Show generic error message as an alert
                });
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('editPostForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('../../controller/edit_post.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.success);
                            window.location.reload();
                        } else if (data.error) {
                            alert(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating post:', error);
                        alert('Failed to update post.');
                    });
            });
        });

        function deletePost(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                fetch('../../controller/delete_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `post_id=${postId}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
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

        let pendingAction = null; // Store the pending action (comment or reply)
        let pendingActionData = null; // Store the data for the pending action (e.g., post ID or comment ID)

        // Function to confirm password before showing the comment or reply modal
        function confirmPasswordBeforeAction(action, data) {
            pendingAction = action; // Store the action (e.g., 'comment' or 'reply')
            pendingActionData = data; // Store the data (e.g., post ID or comment ID)
            document.getElementById('passwordError').style.display = 'none'; // Hide error message
            document.getElementById('adminPassword').value = ''; // Clear password input
            const modal = new bootstrap.Modal(document.getElementById('passwordConfirmationModal'));
            modal.show();
        }

        // Handle password confirmation form submission
        document.addEventListener("DOMContentLoaded", function() {
            const passwordConfirmationForm = document.getElementById('passwordConfirmationForm');
            if (passwordConfirmationForm) {
                passwordConfirmationForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const password = document.getElementById('adminPassword').value;

                    fetch('../../controller/admin_confirm_password.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `adminPassword=${encodeURIComponent(password)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Password confirmed, proceed with the pending action
                                if (pendingAction === 'comment') {
                                    const commentModal = new bootstrap.Modal(document.getElementById(`commentModal-${pendingActionData}`));
                                    commentModal.show();
                                } else if (pendingAction === 'reply') {
                                    const replyModal = new bootstrap.Modal(document.getElementById(`replyModal-${pendingActionData}`));
                                    replyModal.show();
                                }
                                // Hide the password confirmation modal
                                const passwordModal = bootstrap.Modal.getInstance(document.getElementById('passwordConfirmationModal'));
                                passwordModal.hide();
                            } else {
                                // Show error message
                                document.getElementById('passwordError').style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to confirm password.');
                        });
                });
            } else {
                console.error('Password confirmation form not found.');
            }
        });

        // Function to submit the comment after password confirmation
        function submitComment(postId) {
            const commentContent = document.getElementById(`commentText-${postId}`).value;

            fetch('../../controller/add_comment_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&comment=${encodeURIComponent(commentContent)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit comment.');
                });
        }

        // function submitReply(commentId) {
        //     const replyContent = document.getElementById(`replyContent-${commentId}`).value;

        //     fetch('../../controller/add_reply_admin.php', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/x-www-form-urlencoded',
        //             },
        //             body: `comment_id=${commentId}&reply_content=${encodeURIComponent(replyContent)}`
        //         })
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.success) {
        //                 alert(data.message);
        //                 window.location.reload();
        //             } else {
        //                 alert(data.error);
        //             }
        //         })
        //         .catch(error => {
        //             console.error('Error:', error);
        //             alert('Failed to submit reply.');
        //         });
        // }

        // Function to submit the reply after password confirmation
        function submitReply(commentId) {
            const replyContent = document.getElementById(`replyContent-${commentId}`).value;

            fetch('../../controller/add_reply_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${commentId}&reply_content=${encodeURIComponent(replyContent)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit reply.');
                });
        }

        // Inside the password confirmation success block
        if (pendingAction === 'comment') {
            console.log(`Showing comment modal for post ID: ${pendingActionData}`);
            const commentModal = new bootstrap.Modal(document.getElementById(`commentModal-${pendingActionData}`));
            commentModal.show();
        } else if (pendingAction === 'reply') {
            console.log(`Showing reply modal for comment ID: ${pendingActionData}`);
            const replyModal = new bootstrap.Modal(document.getElementById(`replyModal-${pendingActionData}`));
            replyModal.show();
        }
    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_dashboard.php">Post Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comments_dashboard.php">Comments Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account_management.php">Account Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal"
                            data-bs-target="#logoutConfirmationModal">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h2>Admin Dashboard - Manage Accounts, Posts, and Comments</h2>
        <p>Below is the list of all user accounts, posts, and comments in the system.</p>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="users-tab" data-bs-toggle="tab" href="#users" role="tab"
                    aria-controls="users" aria-selected="true">Users</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="posts-tab" data-bs-toggle="tab" href="#posts" role="tab" aria-controls="posts"
                    aria-selected="false">Posts</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="comments-tab" data-bs-toggle="tab" href="#comments" role="tab"
                    aria-controls="comments" aria-selected="false">Comments</a>
            </li>
        </ul>

        <div class="tab-content mt-4" id="adminTabsContent">
            <!-- Users Table -->
            <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                <h4>Users Table</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">User ID</th>
                            <th scope="col">Username</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($userResult->num_rows > 0) {
                            while ($row = $userResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['user_id']) . "</td>
                                    <td>" . htmlspecialchars($row['username']) . "</td>
                                    <td>" . htmlspecialchars($row['email']) . "</td>
                                    <td>" . htmlspecialchars($row['role']) . "</td>
                                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Posts Table -->
            <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                <h4>Posts Table</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Post ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Content</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($postResult->num_rows > 0) {
                            while ($row = $postResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['post_id']) . "</td>
                                    <td>" . htmlspecialchars($row['title']) . "</td>
                                    <td>" . htmlspecialchars($row['content']) . "</td>
                                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No posts found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Comments Table -->
            <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                <h4>Comments Table</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Comment ID</th>
                            <th scope="col">Post Title</th>
                            <th scope="col">User</th>
                            <th scope="col">Comment</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($commentResult->num_rows > 0) {
                            while ($row = $commentResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['post_title']) . "</td>
                                    <td>" . htmlspecialchars($row['user_name']) . "</td>
                                    <td>" . htmlspecialchars($row['comment']) . "</td>
                                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No comments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- News Feed Section -->
        <div class="mt-5">
            <h2>News Feed</h2>
            <div class="posts mt-4">
                <?php
                $newsFeedQuery = "SELECT post_id, title, content, created_at FROM posts ORDER BY created_at DESC";
                $newsFeedResult = $CONN->query($newsFeedQuery);

                if ($newsFeedResult->num_rows > 0):
                    while ($row = $newsFeedResult->fetch_assoc()):
                ?>
                        <div class="post-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="post-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div>
                                    <button class="btn btn-light btn-sm"
                                        onclick="fetchPostData(<?php echo $row['post_id']; ?>)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-light btn-sm text-danger"
                                        onclick="deletePost(<?php echo $row['post_id']; ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="post-body">
                                <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                            </div>
                            <div class="post-meta">
                                Posted on <?php echo date("F j, Y, g:i A", strtotime($row['created_at'])); ?>
                            </div>
                            <!-- Comment Button -->
                            <button type="button" class="btn btn-primary mt-2 bg-success" onclick="submitComment(<?php echo $row['post_id']; ?>)">
                                Comment
                            </button>

                            <!-- Comment Modal -->
                            <div class="modal fade" id="commentModal-<?php echo $row['post_id']; ?>" tabindex="-1"
                                aria-labelledby="commentModalLabel-<?php echo $row['post_id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="commentModalLabel-<?php echo $row['post_id']; ?>">Add Comment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="commentForm-<?php echo $row['post_id']; ?>">
                                                <div class="mb-3">
                                                    <label for="commentText-<?php echo $row['post_id']; ?>" class="form-label">Comment</label>
                                                    <textarea class="form-control" id="commentText-<?php echo $row['post_id']; ?>" name="comment" required></textarea>
                                                </div>
                                                <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>" />
                                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" />
                                                <button type="button" class="btn btn-success" onclick="submitComment(<?php echo $row['post_id']; ?>)">Submit</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments Section -->
                            <div class="comments mt-3">
                                <?php
                                $commentQuery = "SELECT c.id, c.comment, u.username AS author, c.created_at 
                     FROM comments c 
                     JOIN users u ON c.user_id = u.user_id 
                     WHERE c.post_id = ? 
                     ORDER BY c.created_at ASC";

                                $stmt = $CONN->prepare($commentQuery);
                                $stmt->bind_param('i', $row['post_id']);
                                $stmt->execute();
                                $commentsResult = $stmt->get_result();

                                if ($commentsResult->num_rows > 0):
                                    while ($comment = $commentsResult->fetch_assoc()): ?>
                                        <div class="comment">
                                            <div class="comment-author">
                                                <?php echo htmlspecialchars($comment['author']); ?>:
                                            </div>
                                            <div class="comment-text">
                                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                            </div>
                                            <div class="comment-meta">
                                                Commented on <?php echo date("F j, Y, g:i A", strtotime($comment['created_at'])); ?>
                                            </div>

                                            <!-- Reply Button -->
                                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="submitReply(<?php echo $comment['id']; ?>)">
                                                Reply
                                            </button>

                                            <!-- Reply Modal -->
                                            <div class="modal fade" id="replyModal-<?php echo $comment['id']; ?>" tabindex="-1"
                                                aria-labelledby="replyModalLabel-<?php echo $comment['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="replyModalLabel-<?php echo $comment['id']; ?>">Reply to Comment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form id="replyForm-<?php echo $comment['id']; ?>">
                                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="replyContent-<?php echo $comment['id']; ?>" class="form-label">Reply</label>
                                                                    <textarea class="form-control" id="replyContent-<?php echo $comment['id']; ?>" name="reply_content" required></textarea>
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
                                                $replyQuery = "SELECT r.reply_content, u.username AS replier, r.created_at 
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
                                                            <div class="reply-author">
                                                                <?php echo htmlspecialchars($reply['replier']); ?>:
                                                            </div>
                                                            <div class="reply-text">
                                                                <?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?>
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
                                endif;
                                ?>
                            </div>


                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Post Modal -->
        <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel"
            aria-hidden="true">
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
                                <textarea class="form-control" id="editContent" name="content" rows="3"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutConfirmationModal" tabindex="-1"
            aria-labelledby="logoutConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoutConfirmationModalLabel">Confirm Logout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to log out?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="../../controller/dashboard_logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Confirmation Modal -->
        <div class="modal fade" id="passwordConfirmationModal" tabindex="-1" aria-labelledby="passwordConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordConfirmationModalLabel">Confirm Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="passwordConfirmationForm">
                            <div class="mb-3">
                                <label for="adminPassword" class="form-label">Enter your password to continue:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye"></i> <!-- Bootstrap Icons (eye icon) -->
                                    </button>
                                </div>
                            </div>
                            <div id="passwordError" class="text-danger mb-3" style="display: none;">Incorrect password. Please try again.</div>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include Bootstrap JS -->
        <script src="../../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>