<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'salesadmin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $salesQuery = "
            SELECT u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target, COALESCE(SUM(ds.rejects), 0) AS rejects
            FROM users u
            LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
            WHERE u.role = 'sales'
            GROUP BY u.name";
        
        $stmt = $conn->prepare($salesQuery);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "sales_data_" . date("Ymd") . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Name', 'Sales', 'Target', 'Rejects', 'Start Date', 'End Date'));
        
        foreach ($salesData as $row) {
            fputcsv($output, array($row['name'], $row['sales'], $row['target'], $row['rejects'], $startDate, $endDate));
        }

        fclose($output);
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $conn = null;
}
?>
