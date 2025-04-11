<?php
session_start();
require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $name = trim($_POST['updateName']);
    $email = trim($_POST['updateEmail']);
    $phone = trim($_POST['updatePhone']);
    $password = trim($_POST['updatePassword']);
    $role = trim($_POST['updateRole']);

    // Debug output
    error_log("User ID: $userId");
    error_log("Name: $name");
    error_log("Email: $email");
    error_log("Phone: $phone");
    error_log("Password: " . (!empty($password) ? '****' : ''));
    error_log("Role: $role");

    // Check if user is active
    $sql_check = "SELECT active FROM users WHERE id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':id' => $userId]);
    $user = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['active'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot modify inactive users']);
        exit;
    }

    // Check if the new email is already taken by another active user
    if (!empty($email)) {
        $sql_email_check = "SELECT id FROM users WHERE email = :email AND id != :id AND active = 1";
        $stmt_email_check = $conn->prepare($sql_email_check);
        $stmt_email_check->execute([':email' => $email, ':id' => $userId]);
        if ($stmt_email_check->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use by another active user']);
            exit;
        }
    }

    // Initialize the SQL update query
    $sql = "UPDATE users SET ";
    $params = [];
    $updateFields = [];

    if (!empty($name)) {
        $updateFields[] = "name = :name";
        $params[':name'] = $name;
    }
    if (!empty($email)) {
        $updateFields[] = "email = :email";
        $params[':email'] = $email;
    }
    if (!empty($phone)) {
        $updateFields[] = "phone = :phone";
        $params[':phone'] = $phone;
    }
    if (!empty($password)) {
        $updateFields[] = "password = :password";
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }
    if (!empty($role)) {
        $updateFields[] = "role = :role";
        $params[':role'] = $role;
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }

    $sql .= implode(", ", $updateFields) . " WHERE id = :id";
    $params[':id'] = $userId;

    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
