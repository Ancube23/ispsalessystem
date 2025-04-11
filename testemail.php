<?php
session_start();
require 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Fetch sales and target data
    $query = "
        SELECT u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
        FROM users u
        LEFT JOIN daily_sales ds ON ds.user_id = u.id
        WHERE u.role = 'sales'
        GROUP BY u.name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the email content
    $content = "<h1>Total Sales and Targets</h1><table><tr><th>Name</th><th>Sales</th><th>Target</th></tr>";
    foreach ($salesData as $data) {
        $content .= "<tr><td>{$data['name']}</td><td>{$data['sales']}</td><td>{$data['target']}</td></tr>";
    }
    $content .= "</table>";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'cms.synaq.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                
        $mail->Username   = 'support@redwill.co.za'; 
        $mail->Password   = 'rw#jmn853K7'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;                

        $mail->setFrom('support@redwill.co.za', 'WhatsYourScore');
        $mail->addAddress($email); 

        $mail->isHTML(true);
        $mail->Subject = 'Sales and Targets Report';
        $mail->Body    = $content;

        $mail->send();
        $message = "Email sent successfully.";
    } catch (Exception $e) {
        $message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
</head>
<body>
    <h2>Send Sales and Targets Report</h2>
    <form method="post" action="">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Email</button>
    </form>
    <?php if (isset($message)) echo "<p>$message</p>"; ?>
</body>
</html>
