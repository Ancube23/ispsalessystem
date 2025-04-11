<?php
// activate_user.php

// Database connection
include('../db.php');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from POST data
    $user_id = $_POST['user_id'];

    // Update user's active status to 1
    $stmt = $conn->prepare("UPDATE users SET active = 1 WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo 'success'; // Send success response
    } else {
        echo 'failure'; // Send failure response
    }

    $conn = null; // Close the connection
}
?>
