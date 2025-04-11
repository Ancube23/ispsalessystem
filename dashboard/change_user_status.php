<?php
session_start();
require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$user_id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

if ($user_id && in_array($status, [0, 1])) {
    // Update user status
    $sql = "UPDATE users SET active = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user status']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>
