<?php
session_start();
require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'salesadmin') {
    header("Location: ../login.php");
    exit;
}

// Capture POST data
$startDate = $_POST['startDate'] ?? date('Y-m-d');
$endDate = $_POST['endDate'] ?? date('Y-m-d');
$selectedUsers = $_POST['userIds'] ?? [];

// Prepare query parameters
$params = [
    ':startDate' => $startDate,
    ':endDate' => $endDate,
    ':currentMonth' => date('Y-m-01') // Start of the current month
];

if (!empty($selectedUsers)) {
    // Create named placeholders for the selected users
    $placeholders = implode(', ', array_map(function($i) { return ':user' . $i; }, array_keys($selectedUsers)));
    
    // Construct the query
    $salesQuery = "
        SELECT u.name, 
               SUM(ds.sales) AS total_sales, 
               SUM(ds.target) AS total_target, 
               SUM(ds.active_leads) AS active_leads, 
               SUM(ds.active_quotes) AS active_quotes, 
               SUM(ds.awaiting_docs) AS awaiting_docs, 
               COALESCE(mt.monthly_target, 0) AS monthly_target, 
               SUM(CASE WHEN ds.date BETWEEN :currentMonth AND NOW() THEN ds.sales ELSE 0 END) AS current_monthly_sale
        FROM daily_sales ds
        INNER JOIN users u ON ds.user_id = u.id
        LEFT JOIN (
            SELECT user_id, monthly_target 
            FROM monthly_targets 
            WHERE month = :currentMonth
        ) AS mt ON ds.user_id = mt.user_id
        WHERE ds.user_id IN ($placeholders) 
        AND ds.date BETWEEN :startDate AND :endDate
        GROUP BY u.name";
    
    // Add the user IDs to the parameters array
    foreach ($selectedUsers as $i => $userId) {
        $params[':user' . $i] = $userId;
    }
} else {
    // Construct the query without user filters
    $salesQuery = "
        SELECT u.name, 
               SUM(ds.sales) AS total_sales, 
               SUM(ds.target) AS total_target, 
               SUM(ds.active_leads) AS active_leads, 
               SUM(ds.active_quotes) AS active_quotes, 
               SUM(ds.awaiting_docs) AS awaiting_docs, 
               COALESCE(mt.monthly_target, 0) AS monthly_target, 
               SUM(CASE WHEN ds.date BETWEEN :currentMonth AND NOW() THEN ds.sales ELSE 0 END) AS current_monthly_sale
        FROM daily_sales ds
        INNER JOIN users u ON ds.user_id = u.id
        LEFT JOIN (
            SELECT user_id, monthly_target 
            FROM monthly_targets 
            WHERE month = :currentMonth
        ) AS mt ON ds.user_id = mt.user_id
        WHERE ds.date BETWEEN :startDate AND :endDate
        GROUP BY u.name";
}

try {
    $stmt = $conn->prepare($salesQuery);
    $stmt->execute($params);
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output as CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="sales_report.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Total Sales', 'Total Target', 'Active Leads', 'Active Quotes', 'Awaiting Docs', 'Monthly Target', 'Current Monthly Sale']);

    foreach ($salesData as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
