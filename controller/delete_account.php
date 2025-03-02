<?php
session_start();
include '../model/connect.php';

// Ensure the user is logged in and has 'admin' role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];
$stmt = $CONN->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: ../view/dashboard_pages/account_management.php');
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>