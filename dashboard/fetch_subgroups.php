<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$fno_id = isset($_GET['fno_id']) ? (int) $_GET['fno_id'] : 0;
$response = [];

if ($fno_id) {
    try {
        $stmt = $pdo->prepare("SELECT Subgroup_id AS id, Subgroup_Name AS name FROM fno_subgroup WHERE FNO_id = :fno_id ORDER BY Subgroup_Name ASC");
        $stmt->execute(['fno_id' => $fno_id]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $response[] = $row;
        }
    } catch (PDOException $e) {
        error_log("Error fetching subgroups: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
