<?php

session_start();

require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get start and end dates from POST data
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

// Set up CSV file for output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_data_export.csv');

$output = fopen('php://output', 'w');

// Write header for Sales Users
fputcsv($output, array('Role', 'Name', 'Sales', 'Target'));

// Fetch data for sales users
$salesQuery = "
    SELECT u.role, u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
    FROM users u
    LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
    WHERE u.role = 'sales'
    GROUP BY u.id, u.name, u.role";

$stmt = $conn->prepare($salesQuery);
$stmt->bindParam(':start_date', $startDate);
$stmt->bindParam(':end_date', $endDate);
$stmt->execute();
$salesUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($salesUsers) {
    foreach ($salesUsers as $user) {
        fputcsv($output, array($user['role'], $user['name'], $user['sales'], $user['target']));
    }
}

// Write header for Sales Admins
fputcsv($output, array()); // Add a blank row for separation
fputcsv($output, array('Role', 'Name', 'Sales', 'Target'));

// Fetch data for sales admins
$adminsQuery = "
    SELECT u.role, u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
    FROM users u
    LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
    WHERE u.role = 'salesadmin'
    GROUP BY u.id, u.name, u.role";

$stmt = $conn->prepare($adminsQuery);
$stmt->bindParam(':start_date', $startDate);
$stmt->bindParam(':end_date', $endDate);
$stmt->execute();
$salesAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($salesAdmins) {
    foreach ($salesAdmins as $user) {
        fputcsv($output, array($user['role'], $user['name'], $user['sales'], $user['target']));
    }
}

fclose($output);
exit;
?>
