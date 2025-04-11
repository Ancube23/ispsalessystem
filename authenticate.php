<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Email exists, now check the password
            if (password_verify($password, $user['password'])) {
                // Password is correct
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];

                // Role-based redirection
                switch ($user['role']) {
                    case 'admin':
                        header("Location: dashboard/index.php");
                        break;
                    case 'salesadmin':
                        header("Location: admindashboard/index.php");
                        break;
                    case 'sales':
                        header("Location: salesdashboard/index.php");
                        break;
                    default:
                        // In case the role doesn't match any case, log out
                        session_unset();
                        session_destroy();
                        echo "Invalid role.";
                        exit;
                }
                exit;
            } else {
                // Invalid password
                echo "Invalid password.";
            }
        } else {
            // Invalid email
            echo "Invalid email.";
        }
    } else {
        echo "Email and password must be provided.";
    }
}
?>
