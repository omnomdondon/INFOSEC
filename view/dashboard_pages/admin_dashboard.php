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
            position: relative;
            /* Ensure the post card is a positioning context */
            margin-bottom: 20px;
            padding: 15px;
            /* Adjusted padding to make better use of space */
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .post-actions-top {
            position: absolute;
            /* Position the buttons absolutely within the post card */
            top: 15px;
            /* Align with the top padding of the post card */
            right: 15px;
            /* Align with the right padding of the post card */
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
            /* Remove margin-top to align with buttons */
            padding-right: 120px;
            /* Add padding to prevent overlap with buttons */
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
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .post-actions .btn {
            padding: 5px 10px;
            font-size: 0.875rem;
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

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>

    <script>
        // Function to toggle password visibility
        document.addEventListener("DOMContentLoaded", function() {
            const togglePassword = document.getElementById('togglePassword');
            const adminPassword = document.getElementById('adminPassword');

            if (togglePassword && adminPassword) {
                togglePassword.addEventListener('click', function() {
                    const type = adminPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    adminPassword.setAttribute('type', type);

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

            // Clear password field and reset the toggle button when modal is closed
            const passwordConfirmationModal = document.getElementById('passwordConfirmationModal');
            passwordConfirmationModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('adminPassword').value = '';
                document.getElementById('passwordError').style.display = 'none';

                const eyeIcon = document.querySelector('#togglePassword i');
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
                document.getElementById('adminPassword').setAttribute('type', 'password');
            });

            // Add event listeners for comment form submission
            document.querySelectorAll(".commentForm").forEach(form => {
                form.addEventListener("submit", function(event) {
                    event.preventDefault();

                    const postId = this.getAttribute("data-post-id");
                    const commentContent = document.getElementById(`commentText-${postId}`).value.trim();

                    if (!commentContent) {
                        alert("Comment cannot be empty.");
                        return;
                    }

                    submitComment(postId, commentContent);
                });
            });

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
                e.preventDefault();
                const modal = new bootstrap.Modal(logoutModal);
                modal.show();
            });

            const logoutButton = document.querySelector('#logoutConfirmationModal .btn-danger');
            logoutButton.addEventListener('click', function() {
                window.location.href = '../../controller/dashboard_logout.php';
            });
        });

        document.addEventListener("mousemove", startTimer);
        document.addEventListener("keydown", startTimer);
        document.addEventListener("mousedown", startTimer);
        document.addEventListener("wheel", startTimer);
        document.addEventListener("touchstart", startTimer);
        startTimer();

        function fetchPostData(postId) {
            fetch(`../../controller/edit_post.php?post_id=${postId}`)
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
                            window.location.reload();
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

        // Function to confirm password and perform actions
        function confirmPasswordBeforeAction(action, data) {
            document.getElementById('actionType').value = action;
            document.getElementById('actionData').value = JSON.stringify(data);
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordConfirmationModal'));
            passwordModal.show();
        }

        // Handle password confirmation form submission
        document.addEventListener("DOMContentLoaded", function() {
            const passwordConfirmationForm = document.getElementById('passwordConfirmationForm');

            if (passwordConfirmationForm) {
                passwordConfirmationForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const adminPassword = document.getElementById('adminPassword').value;
                    const actionType = document.getElementById('actionType').value;
                    const actionData = JSON.parse(document.getElementById('actionData').value);

                    fetch('../../controller/admin_confirm_password.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `adminPassword=${encodeURIComponent(adminPassword)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const passwordModalElement = document.getElementById('passwordConfirmationModal');
                                const passwordModal = bootstrap.Modal.getInstance(passwordModalElement);
                                if (passwordModal) {
                                    passwordModal.hide();
                                }

                                if (actionType === 'edit_post') {
                                    fetchPostData(actionData.postId); // Fetch post data after password confirmation
                                }
                            } else {
                                document.getElementById('passwordError').style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to confirm password.');
                        });
                });
            } else {
                console.error("passwordConfirmationForm not found in the DOM");
            }
        });

        function fetchPostData(postId) {
            fetch(`../../controller/edit_post.php?post_id=${postId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // First, get the raw response as text
                })
                .then(text => {
                    console.log("Raw response:", text); // Log the raw response
                    return JSON.parse(text); // Parse the text as JSON
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById('editPostId').value = data.post_id;
                        document.getElementById('editTitle').value = data.title;
                        document.getElementById('editContent').value = data.content;

                        const editPostModalElement = document.getElementById('editPostModal');
                        if (editPostModalElement) {
                            const editPostModal = new bootstrap.Modal(editPostModalElement);
                            editPostModal.show();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching post data:', error);
                    alert('Failed to fetch post data.');
                });
        }

        // Handle edit post form submission
        document.addEventListener("DOMContentLoaded", function() {
            const editPostForm = document.getElementById('editPostForm');

            if (editPostForm) {
                editPostForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const postId = document.getElementById('editPostId').value;
                    const title = document.getElementById('editTitle').value;
                    const content = document.getElementById('editContent').value;

                    fetch('../../controller/edit_post.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `post_id=${postId}&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
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
                            alert('Failed to update post.');
                        });
                });
            } else {
                console.error("editPostForm not found in the DOM");
            }
        });

        function submitReply(commentId) {
            const replyContent = document.getElementById(`replyContent-${commentId}`).value.trim();

            if (!replyContent) {
                alert("Reply cannot be empty.");
                return;
            }

            // Create a FormData object to send the data
            const formData = new FormData();
            formData.append('comment_id', commentId);
            formData.append('reply_content', replyContent);

            fetch('../../controller/add_reply_admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Close the reply modal
                        const replyModalElement = document.getElementById(`replyModal-${commentId}`);
                        const replyModal = bootstrap.Modal.getInstance(replyModalElement);
                        if (replyModal) {
                            replyModal.hide();
                        }
                        window.location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("Error: " + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit reply.');
                });
        }

        function submitComment(postId, commentContent) {
            // Create a FormData object to send the data
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('comment', commentContent);

            fetch('../../controller/add_comment_admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Close the comment modal
                        const commentModalElement = document.getElementById(`commentModal-${postId}`);
                        const commentModal = bootstrap.Modal.getInstance(commentModalElement);
                        if (commentModal) {
                            commentModal.hide();
                        }
                        window.location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("Error: " + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to submit comment.');
                });
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
                            <!-- Post Actions (Edit, Delete) at the top right -->
                            <div class="post-actions-top">
                                <button class="btn btn-light btn-sm" onclick="confirmPasswordBeforeAction('edit_post', { postId: <?php echo $row['post_id']; ?> })">
                                    <i class="fas fa-pencil-alt"></i> Edit
                                </button>
                                <button class="btn btn-light btn-sm text-danger" onclick="confirmPasswordBeforeAction('delete_post', { postId: <?php echo $row['post_id']; ?> })">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>

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

                            <!-- Comment Button -->
                            <div class="post-actions">
                                <button type="button" class="btn btn-primary btn-sm bg-success" onclick="confirmPasswordBeforeAction('comment', { postId: <?php echo $row['post_id']; ?> })">
                                    <i class="fas fa-comment"></i> Comment
                                </button>
                            </div>

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
                                            <form id="commentForm-<?php echo $row['post_id']; ?>" onsubmit="event.preventDefault(); submitComment(<?php echo $row['post_id']; ?>, document.getElementById('commentText-<?php echo $row['post_id']; ?>').value);">
                                                <div class="mb-3">
                                                    <label for="commentText-<?php echo $row['post_id']; ?>" class="form-label">Comment</label>
                                                    <textarea class="form-control" id="commentText-<?php echo $row['post_id']; ?>" name="comment" required></textarea>
                                                </div>
                                                <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>" />
                                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" />
                                                <button type="submit" class="btn btn-success">Submit</button>
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
                                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="confirmPasswordBeforeAction('reply', { commentId: <?php echo $comment['id']; ?> })">
                                                <i class="fas fa-reply"></i> Reply
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
                                                            <form id="replyForm-<?php echo $comment['id']; ?>" onsubmit="event.preventDefault(); submitReply(<?php echo $comment['id']; ?>);">
                                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="replyContent-<?php echo $comment['id']; ?>" class="form-label">Reply</label>
                                                                    <textarea class="form-control" id="replyContent-<?php echo $comment['id']; ?>" name="reply_content" required></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-success">Submit</button>
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
                            <input type="hidden" id="actionType" name="actionType" value="">
                            <input type="hidden" id="actionData" name="actionData" value="">
                            <div class="mb-3">
                                <label for="adminPassword" class="form-label">Enter your password to continue:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="passwordError" class="text-danger mb-3" style="display: none;">Incorrect password. Please try again.</div>
                            <button type="submit" class="btn btn-success">Confirm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include Bootstrap JS -->
        <script src="../../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>