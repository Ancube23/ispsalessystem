<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get package_id from the URL
$package_id = $_GET['package_id'] ?? null;

if ($package_id) {
    $sql = "SELECT * FROM package_speeds WHERE package_id = '$package_id'";
    $result = $conn->query($sql);

    $speeds = [];
    while ($row = $result->fetch_assoc()) {
        $speeds[] = $row['speed'];
    }

    echo json_encode($speeds);
} else {
    echo json_encode([]);  // If no package_id provided, return empty array
}

$conn->close();
?>
