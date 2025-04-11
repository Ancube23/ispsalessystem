<?php
session_start();
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

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Fetch Data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['table'])) {
    $id = intval($_POST['id']);
    $table = $_POST['table'];
    $data = [];

    switch ($table) {
        case "fno":
            $query = "SELECT FNO_id, FNO_Name, FNO_Logo FROM fno WHERE FNO_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($FNO_id, $FNO_Name, $FNO_Logo);
            $stmt->fetch();
            $stmt->close();
            $data = [
                "FNO_id" => $FNO_id,
                "FNO_Name" => $FNO_Name,
                "FNO_Logo" => $FNO_Logo
            ];
            break;

        case "pricelist":
            $query = "SELECT PR_id, PR_Short_Description, PR_Long_Description FROM pricelist WHERE PR_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($PR_id, $PR_Short_Description, $PR_Long_Description);
            $stmt->fetch();
            $stmt->close();
            $data = [
                "PR_id" => $PR_id,
                "PR_Short_Description" => $PR_Short_Description,
                "PR_Long_Description" => $PR_Long_Description
            ];
            break;

        case "package":
            $query = "SELECT PK_ID, PK_Term, PK_Speed, PK_Price FROM package WHERE PK_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($PK_ID, $PK_Term, $PK_Speed, $PK_Price);
            $stmt->fetch();
            $stmt->close();
            $data = [
                "PK_ID" => $PK_ID,
                "PK_Term" => $PK_Term,
                "PK_Speed" => $PK_Speed,
                "PK_Price" => $PK_Price
            ];
            break;

        default:
            echo json_encode(["error" => "Invalid table"]);
            exit;
    }

    // Return the data as a JSON object
    echo json_encode($data);


}



?>
