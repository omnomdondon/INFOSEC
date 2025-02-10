<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy session data
session_unset();
session_destroy();

// Delete the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Prevent back-button access and enforce no caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect to login page
header("Location: ../index.php");
exit();
?>
