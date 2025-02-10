<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require '../model/connect.php'; // Ensure the correct path

// Check if connection is established
if (!$CONN) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Handle POST request to update an account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input if sent as JSON
    $inputData = json_decode(file_get_contents("php://input"), true);

    // Use $_POST if JSON decoding fails
    $userId = isset($inputData['user_id']) ? intval($inputData['user_id']) : intval($_POST['user_id'] ?? 0);
    $username = trim($inputData['username'] ?? $_POST['username'] ?? '');
    $email = trim($inputData['email'] ?? $_POST['email'] ?? '');
    $role = trim($inputData['role'] ?? $_POST['role'] ?? '');

    // Validate input
    if (empty($userId) || empty($username) || empty($email) || empty($role)) {
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format.']);
        exit;
    }

    // Update account details
    $updateQuery = "UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?";
    $updateStmt = $CONN->prepare($updateQuery);
    if (!$updateStmt) {
        echo json_encode(['error' => 'Failed to prepare statement.']);
        exit;
    }

    $updateStmt->bind_param('sssi', $username, $email, $role, $userId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => ($updateStmt->affected_rows > 0) ? 'Account updated successfully.' : 'No changes made.']);
    } else {
        echo json_encode(['error' => 'Failed to update account.']);
    }
    
    $updateStmt->close();
    exit;
}

// Handle GET request to fetch user data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    $userQuery = "SELECT user_id, username, email, role FROM users WHERE user_id = ?";
    $stmt = $CONN->prepare($userQuery);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement.']);
        exit;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'User not found.']);
    }

    $stmt->close();
    exit;
}

// Invalid request
http_response_code(400);
echo json_encode(['error' => 'Invalid request.']);
?>
