<?php
session_start();
// After successful login
echo "User ID: " . $_SESSION['user_id'];  // Check if user_id is being set correctly

$CONN = require("../model/connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signIn'])) {
    $identifier = $_POST['email']; // Accepts username or email
    $password = $_POST['password'];

    // Query to check if identifier matches username or email
    $query = $CONN->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    if (!$query) {
        die("Database query failed: " . $CONN->error);
    }

    $query->bind_param("ss", $identifier, $identifier);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password using bcrypt
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent session fixation

            // Store user details in session
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store user role in session
            $_SESSION['user_id'] = $user['user_id']; // Store user_id in session
            $_SESSION['alert_message'] = "Welcome, " . htmlspecialchars($user['username']) . "!";

            // Redirect based on role
            if ($user['role'] === 'admin') {
                error_log('User role is admin. Username: ' . $user['username']); // For debugging
                header("Location: ../view/dashboard_pages/admin_dashboard.php");
                exit;
            } elseif ($user['role'] === 'user') {
                header("Location: ../view/homepages/homepage1.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid user role!";
                header("Location: ../index.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid username/email or password!";
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Invalid username/email or password!";
        header("Location: ../index.php");
        exit;
    }
}

// session_start();
// // After successful login
// echo "User ID: " . $_SESSION['user_id'];  // Check if user_id is being set correctly

// $CONN = require("../model/connect.php");

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signIn'])) {
//     $identifier = $_POST['email']; // Accepts username or email
//     $password = $_POST['password'];

//     // Query to check if identifier matches username or email
//     $query = $CONN->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
//     if (!$query) {
//         die("Database query failed: " . $CONN->error);
//     }

//     $query->bind_param("ss", $identifier, $identifier);
//     $query->execute();
//     $result = $query->get_result();

//     if ($result->num_rows > 0) {
//         $user = $result->fetch_assoc();

//         // Verify password
//         // if (password_verify($password, $user['password'])) {
//         if ($password === $user['password']) {
//             session_regenerate_id(true); // Prevent session fixation

//             // Store user details in session
//             $_SESSION['email'] = $user['email'];
//             $_SESSION['username'] = $user['username'];
//             $_SESSION['role'] = $user['role']; // Store user role in session
//             $_SESSION['user_id'] = $user['user_id']; // Store user_id in session
//             $_SESSION['alert_message'] = "Welcome, " . htmlspecialchars($user['username']) . "!";

//             // Redirect based on role
//             if ($user['role'] === 'admin') {
//                 error_log('User role is admin. Username: ' . $user['username']); // For debugging
//                 // header("Location: ../view/dashboard.php");
//                 header("Location: ../view/dashboard_pages/admin_dashboard.php");
//                 exit;
//             } elseif ($user['role'] === 'user') {
//                 header("Location: ../view/homepages/homepage1.php");
//                 exit;
//             } else {
//                 $_SESSION['error_message'] = "Invalid user role!";
//                 header("Location: ../index.php");
//                 exit;
//             }
//         } else {
//             $_SESSION['error_message'] = "Invalid username/email or password!";
//             header("Location: ../index.php");
//             exit;
//         }
//     } else {
//         $_SESSION['error_message'] = "Invalid username/email or password!";
//         header("Location: ../index.php");
//         exit;
//     }
// }
