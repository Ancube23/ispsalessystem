<?php
require '../db.php';

$startDate = $_GET['startDate'] ?? date("Y-m-d", strtotime("-1 day"));
$endDate = $_GET['endDate'] ?? date("Y-m-d", strtotime("-1 day"));

try {
    $salesQuery = "
        SELECT u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
        FROM users u
        LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
        WHERE u.role = 'sales'
        GROUP BY u.name";
    $stmt = $conn->prepare($salesQuery);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($salesData);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
