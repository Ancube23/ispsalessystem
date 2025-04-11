<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$operator = $_GET['operator'] ?? '';
$parks = [];

if ($operator) {
    $sql = "SELECT DISTINCT business_park_name FROM pricelist_business WHERE operator_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $operator);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $parks[] = $row['business_park_name'];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($parks);
?>
