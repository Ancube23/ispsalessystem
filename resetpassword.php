<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Invalid or expired token.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password']) && isset($_GET['token'])) {
    $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token = $_GET['token'];

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL WHERE reset_token = :token");
    $stmt->bindParam(':password', $newPassword);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $success = "Your password has been reset successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style.css">
    <title>Reset Password</title>
</head>
<body>
    <main>
        <div class="top-container">
            <div class="content-wrapper">
                <img src="logo.png" class="loginlogo" alt="logo"/>
                <form method="post" action="" class="loginform">
                    <center><h2>Reset Password</h2></center><br>
                    <label class="label2" for="password"><b>New Password</b></label>
                    <div class="input-wrapper">
                        <input class="input" id="password" type="password" name="password" placeholder="" required>
                        
                    </div>
                    <button type="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Debugging output to check PHP variables
            console.log("Success: <?php echo addslashes($success); ?>");
            console.log("Error: <?php echo addslashes($error); ?>");

            <?php if ($success): ?>
                alert("<?php echo addslashes($success); ?>");
                window.location.href = 'login.php'; // Redirect to login page after alert
            <?php elseif ($error): ?>
                alert("<?php echo addslashes($error); ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>
