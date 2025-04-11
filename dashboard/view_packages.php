<?php
// Include database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get Pricelist ID (ensure it's an integer)
$pricelist_id = isset($_GET['pricelist_id']) ? (int) $_GET['pricelist_id'] : 2;

// Debug: Check if Pricelist ID is being received
var_dump("Received Pricelist ID:", $pricelist_id);

$stmt = $conn->prepare("SELECT * FROM package WHERE PR_id = :pricelist_id");
$stmt->execute(['pricelist_id' => $pricelist_id]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if data is being retrieved
var_dump("Fetched Packages:", $packages);
if (empty($packages)) {
    echo "<p>No packages found for this Pricelist ID.</p>";
    exit; // Stop execution to debug
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Packages for Pricelist ID: <?php echo htmlspecialchars($pricelist_id); ?></h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Term</th>
                <th>Speed (Mbps)</th>
                <th>Price (R pm)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php echo htmlspecialchars($package['PK_Term'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($package['PK_Speed'] ?? 'N/A'); ?></td>
                    <td>R <?php echo isset($package['PK_Price']) ? number_format($package['PK_Price'], 2) : 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form action="generate_pdf.php" method="POST">
        <input type="hidden" name="pricelist_id" value="<?php echo htmlspecialchars($pricelist_id); ?>">
        <button type="submit" class="btn btn-primary">Generate PDF</button>
    </form>
</div>

</body>
</html>
