<?php
session_start();
require '../db.php';

// Check if user is logged in and is a sales person
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    echo '<div class="slide-content">User not authorized</div>';
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

try {
    // Fetch user's name
    $userQuery = "SELECT name FROM users WHERE id = :user_id";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(':user_id', $userId);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $user['name'];

    // Fetch sales and target data for yesterday
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $salesQuery = "
        SELECT COALESCE(ds.sales, 0) AS sales, COALESCE(ds.target, 0) AS target
        FROM daily_sales ds
        WHERE ds.user_id = :user_id
        AND ds.date = :yesterday";
    
    $stmt = $conn->prepare($salesQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':yesterday', $yesterday);
    $stmt->execute();
    $salesData = $stmt->fetch(PDO::FETCH_ASSOC);

    $sales = $salesData['sales'] ?? 0;
    $target = $salesData['target'] ?? 0;

   // Generate HTML content for the slide
$htmlContent = '<div class="slide-content" style="text-align: center;">';
$htmlContent .= '<div class="logo-container" style="display: flex; justify-content: center; margin-bottom: 20px;">';
$htmlContent .= '<center><img src="logo2.png" alt="Logo" style="max-width: 100px; height: auto;"></center>';
$htmlContent .= '</div>';
$htmlContent .= "<p><center>Hi {$userName},<br><br> You made {$sales} sales yesterday with an expected target of {$target}.</center></p>";
$htmlContent .= '</div>';
    
    echo $htmlContent;
} catch (PDOException $e) {
    echo '<div class="slide-content">Error: ' . $e->getMessage() . '</div>';
}
?>
