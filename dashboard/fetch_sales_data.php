<?php
require '../db.php';

try {
    // Get start and end dates from POST data
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? date('Y-m-d');
    $userId = $_POST['user_id'] ?? 'all';  // Default to 'all' if user_id is not provided

    // Adjust query based on user selection
    if ($userId === 'all') {
        $salesQuery = "
            SELECT date, SUM(sales) as sales, SUM(target) as target
            FROM daily_sales
            WHERE date BETWEEN :start_date AND :end_date
            GROUP BY date
            ORDER BY date";
    } else {
        $salesQuery = "
            SELECT date, SUM(sales) as sales, SUM(target) as target
            FROM daily_sales
            WHERE date BETWEEN :start_date AND :end_date
              AND user_id = :user_id
            GROUP BY date
            ORDER BY date";
    }

    $stmt = $conn->prepare($salesQuery);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    if ($userId !== 'all') {
        $stmt->bindParam(':user_id', $userId);
    }
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for JSON response
    $dates = [];
    $sales = [];
    $targets = [];

    foreach ($salesData as $data) {
        $dates[] = $data['date'];
        $sales[] = $data['sales'];
        $targets[] = $data['target'];
    }

    echo json_encode([
        'dates' => $dates,
        'sales' => $sales,
        'targets' => $targets
    ]);
    exit;
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
