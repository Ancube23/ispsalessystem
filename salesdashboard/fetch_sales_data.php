<?php
session_start();
require '../db.php';

// Check if user is logged in and is a sales person
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    http_response_code(401);
    echo json_encode(array("error" => "User not authorized"));
    exit;
}

// Get start and end dates from POST request
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    try {
        // Fetch sales data from the database for the specified date range
        $salesQuery = "
            SELECT date, sales, target
            FROM daily_sales
            WHERE user_id = :user_id
            AND date BETWEEN :start_date AND :end_date
            ORDER BY date";
        
        $stmt = $conn->prepare($salesQuery);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data to send back as JSON
        $dates = [];
        $sales = [];
        $targets = [];
        foreach ($salesData as $sale) {
            $dates[] = $sale['date'];
            $sales[] = $sale['sales'];
            $targets[] = $sale['target'];
        }

        // Send response
        echo json_encode(array(
            "dates" => $dates,
            "sales" => $sales,
            "targets" => $targets
        ));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("error" => "Internal Server Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Invalid request parameters"));
}
?>
