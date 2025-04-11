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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Delete package from the database
    $stmt = $pdo->prepare("DELETE FROM package WHERE PK_ID = :id");
    $stmt->execute(['id' => $id]);

    echo 'success';
} else {
    echo 'error';
}
?>
