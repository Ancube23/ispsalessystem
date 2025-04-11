<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc_id = $_POST['tc_id'];
    $terms = mysqli_real_escape_string($conn, $_POST['terms']);

    $query = "UPDATE tandc SET Terms = '$terms' WHERE TC_id = $tc_id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(["success" => true, "message" => "Terms updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating terms: " . mysqli_error($conn)]);
    }
}

$conn->close();
?>
