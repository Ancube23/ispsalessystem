<?php
session_start();
require 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// No need to check for admin session for this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

$startDate = date("Y-m-d", strtotime("last Monday")); // Start of the week
$endDate = date("Y-m-d", strtotime("this Sunday")); // End of the week

// Fetch sales and target data for each user
$query = "
    SELECT u.name, u.email, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
    FROM users u
    LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
    WHERE u.role = 'sales'
    GROUP BY u.name, u.email";

$stmt = $conn->prepare($query);
$stmt->bindParam(':start_date', $startDate);
$stmt->bindParam(':end_date', $endDate);
$stmt->execute();
$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($salesData as $data) {
    // Prepare the email content
    $content = "<h1>Weekly Sales and Targets</h1>";
    $content .= "<p>Hello {$data['name']},</p>";
    $content .= "<p>Your sales from {$startDate} to {$endDate}:</p>";
    $content .= "<p>Sales: {$data['sales']}</p>";
    $content .= "<p>Target: {$data['target']}</p>";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'cms.synaq.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;                
        $mail->Username   = ''; // Replace with your email
        $mail->Password   = ''; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;                

        $mail->setFrom('support@redwill.co.za', 'WhatsYourScore');
        $mail->addAddress($data['email']); 

        $mail->isHTML(true);
        $mail->Subject = 'Weekly Sales and Targets Report';
        $mail->Body    = $content;

        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Close the database connection
$conn = null;
?>

