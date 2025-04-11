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

header('Content-Type: application/json');

// Check if ID is set via POST
if (isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM fno_subgroup WHERE subgroup_id = :id");
        $stmt->execute(['id' => $_POST['id']]); // Use $_POST instead of $_GET
        $subgroup = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subgroup) {
            echo json_encode(['success' => true, 'data' => $subgroup]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subgroup not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
