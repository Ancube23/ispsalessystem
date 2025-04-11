<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get operator_id from the URL
$operator_id = $_GET['operator_id'] ?? null;

if ($operator_id) {
    $sql = "SELECT * FROM packages WHERE operator_id = '$operator_id'";
    $result = $conn->query($sql);

    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }

    echo json_encode($packages);
} else {
    echo json_encode([]);  // If no operator_id provided, return empty array
}

$conn->close();
?>
