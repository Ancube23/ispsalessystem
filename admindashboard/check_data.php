<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $exists = false;

    $sales_data = $_POST['sales_data'];
    foreach ($sales_data as $sales_data_item) {
        $sales_user_id = $sales_data_item['user_id'];
        $check_sql = "SELECT COUNT(*) FROM daily_sales WHERE user_id = :sales_user_id AND date = :date";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':sales_user_id', $sales_user_id);
        $check_stmt->bindParam(':date', $date);
        $check_stmt->execute();
        $existing_count = $check_stmt->fetchColumn();
        if ($existing_count > 0) {
            $exists = true;
            break;
        }
    }

    echo json_encode(['exists' => $exists]);
    exit;
}
