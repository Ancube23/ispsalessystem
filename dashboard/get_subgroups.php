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

if (isset($_POST['fno_id'])) {
    $fno_id = $_POST['fno_id'];
    $stmt = $pdo->prepare("SELECT * FROM fno_subgroup WHERE fno_id = :fno_id");
    $stmt->bindParam(':fno_id', $fno_id, PDO::PARAM_INT);
    $stmt->execute();
    $subgroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($subgroups);
}
?>
