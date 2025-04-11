<?php
session_start();
require 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];

        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("UPDATE users SET reset_token = :token WHERE email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Send email with reset link using PHPMailer
            $resetLink = "http://sales.opentel.co.za/resetpassword.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: $resetLink";
            
            $mail = new PHPMailer(true);

            try {
                // SMTP server configuration
                $mail->isSMTP();
                $mail->Host = 'cms.synaq.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'support@redwill.co.za'; // Replace with your SMTP username
                $mail->Password = 'rw#jmn853K7'; // Replace with your SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email content
                $mail->setFrom('support@redwill.co.za', 'WhatsMYScore');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
                $success = "Reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Email not found.";
        }
    } else {
        $error = "Email must be provided.";
    }
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
    <title>Forgot Password</title>
</head>
<body>
    <main>
        <div class="top-container">
            <div class="content-wrapper">
                <img src="logo.png" class="loginlogo" alt="logo"/>
                <form method="post" action="" class="loginform">
                    <center><h2>Forgot Password</h2></center><br>
                    <label class="label2" for="email"><b>Email</b></label>
                    <div class="input-wrapper">
                        <input class="input" id="email" type="email" name="email" placeholder="" required>
                    </div>
                    <button type="submit">Send Reset Link</button>
                </form>
            </div>
        </div>

        <?php if ($error): ?>
            <script>
                alert("<?php echo addslashes($error); ?>");
            </script>
        <?php endif; ?>

        <?php if ($success): ?>
            <script>
                alert("<?php echo addslashes($success); ?>");
            </script>
        <?php endif; ?>
    </main>
</body>
</html>
