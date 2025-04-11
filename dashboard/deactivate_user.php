<?php
session_start();
require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo 'unauthorized';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    // Set user as inactive and update the deactivated_at timestamp
    $sql = "UPDATE users SET active = 0, deactivated_at = NOW() WHERE id = :id";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([':id' => $userId]);
        echo 'success';
    } catch (PDOException $e) {
        echo 'error';
    }
}
?>
