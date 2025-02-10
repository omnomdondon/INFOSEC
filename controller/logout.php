<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Prevent back-button navigation to cached pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to the login page
header("Location: ../index.php");
exit();
?>
