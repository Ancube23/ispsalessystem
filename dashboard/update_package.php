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
    $term = $_POST['term'];
    $speed = $_POST['speed'];
    $price = $_POST['price'];

    $stmt = $pdo->prepare("UPDATE package SET PK_Term = :term, PK_Speed = :speed, PK_Price = :price WHERE PK_ID = :id");
    $stmt->execute([
        'id' => $id,
        'term' => $term,
        'speed' => $speed,
        'price' => $price
    ]);
    echo 'success';
} else {
    echo 'error';
}
?>
