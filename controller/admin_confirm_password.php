<?php
session_start();
include('connect.php');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$query = "SELECT password FROM users WHERE role = 'admin' LIMIT 1";
$result = $CONN->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $adminPasswordHash = $row['password'];
} else {
    die(json_encode(['error' => 'Admin user not found.']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adminPassword'])) {
    $inputPassword = $_POST['adminPassword'];

    if (password_verify($inputPassword, $adminPasswordHash)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect password. Please try again.']);
    }
    exit();
}
?>