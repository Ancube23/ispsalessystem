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
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $e->getMessage()]));
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;
    $shortDesc = trim(htmlspecialchars($_POST['short_desc'] ?? ''));
    $longDesc = trim(htmlspecialchars($_POST['long_desc'] ?? ''));

    if ($id && !empty($shortDesc) && !empty($longDesc)) {
        try {
            $stmt = $pdo->prepare("UPDATE pricelist SET PR_Short_Description = :short, PR_Long_Description = :long WHERE PR_id = :id");
            $stmt->execute(['short' => $shortDesc, 'long' => $longDesc, 'id' => $id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or invalid ID.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
