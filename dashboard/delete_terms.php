<?php
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

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    try {
        // Prepare and execute the delete query using PDO
        $stmt = $pdo->prepare("DELETE FROM tandc WHERE TC_id = :id");
        $stmt->execute(['id' => $id]);

        // Redirect to view_pricelist.php after successful deletion
        header("Location: maintaintandc.php?success=deleted_terms");
        exit();
    } catch (PDOException $e) {
        echo "Error deleting record: " . $e->getMessage();
    }
}
?>
