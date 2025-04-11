<?php
session_start();
require '../db.php';

// Check if user is logged in and is a sales person
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    echo json_encode(['error' => 'User not authorized']);
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Initialize default date ranges
$endDate = date("Y-m-d"); // End date is today's date
$startDate = date("Y-m-d", strtotime("-7 days")); // Default start date is 8 days before today

// Check if POST data is available and update date ranges
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['startDate']) && isset($input['endDate'])) {
        $startDate = $input['startDate'];
        $endDate = $input['endDate'];
    }
}

try {
    $salesQuery = "
        SELECT ds.date, COALESCE(ds.sales, 0) AS sales, COALESCE(ds.target, 0) AS target, COALESCE(ds.rejects, 0) AS rejects
        FROM daily_sales ds
        WHERE ds.user_id = :user_id
        AND ds.date BETWEEN :start_date AND :end_date
        ORDER BY ds.date DESC"; // Order by date descending
    
    $stmt = $conn->prepare($salesQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($salesData);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
