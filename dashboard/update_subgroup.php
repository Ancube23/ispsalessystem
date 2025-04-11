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

if (isset($_POST['id'], $_POST['name'], $_POST['fno_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE fno_subgroup SET subgroup_name = :name, FNO_id = :fno_id WHERE subgroup_id = :id");
        $stmt->execute([
            'name' => $_POST['name'],
            'fno_id' => $_POST['fno_id'],
            'id' => $_POST['id']
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
}
?>
