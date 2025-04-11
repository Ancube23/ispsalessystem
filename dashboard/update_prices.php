<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debugging: Print out POST data to check if it's being received correctly
echo "<pre>";
var_dump($_POST);
echo "</pre>";

// Handle form submission and update prices
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging: Ensure the expected data is present
    if (isset($_POST['package_id'])) {
        echo "Package ID: " . $_POST['package_id'] . "<br>";
    }
    foreach ($_POST as $key => $value) {
        echo "Key: $key, Value: $value<br>";
    }

    // Prepare and bind statement for updating prices
    $stmt = $conn->prepare("UPDATE pricelist_business SET price_1 = ?, price_2 = ?, price_3 = ?, price_4 = ?, price_5 = ?, price_6 = ?, price_7 = ?, price_8 = ? WHERE package_id = ?");
    $stmt->bind_param("ddddddddi", $price_1, $price_2, $price_3, $price_4, $price_5, $price_6, $price_7, $price_8, $package_id);

    // Get the posted data and update the prices
    $package_id = $_POST['package_id'];
    $price_1 = $_POST['price_0'] ?? 0;
    $price_2 = $_POST['price_1'] ?? 0;
    $price_3 = $_POST['price_2'] ?? 0;
    $price_4 = $_POST['price_3'] ?? 0;
    $price_5 = $_POST['price_4'] ?? 0;
    $price_6 = $_POST['price_5'] ?? 0;
    $price_7 = $_POST['price_6'] ?? 0;
    $price_8 = $_POST['price_7'] ?? 0;

    // Execute the query to update the prices
    if ($stmt->execute()) {
        echo "<script>alert('Prices updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating prices: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>
