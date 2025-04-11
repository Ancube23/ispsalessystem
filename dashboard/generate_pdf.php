<?php
require('fpdf186/fpdf.php');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

// Function to log errors (useful for debugging)
function logError($message) {
    error_log($message . "\n", 3, 'errors.log');
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    logError("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Check logs.");
}

// Ensure the request is POST and `pricelist_id` is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pricelist_id'])) {
    $pricelist_id = filter_var($_POST['pricelist_id'], FILTER_SANITIZE_NUMBER_INT);

    if (!$pricelist_id) {
        die("Invalid Pricelist ID.");
    }

    // Debugging: Log pricelist_id received
    logError("Fetching packages for Pricelist ID: $pricelist_id");

    // Fetch package data
    $stmt = $conn->prepare("SELECT PK_Term, PK_Speed, PK_Price FROM package WHERE PR_id = :pricelist_id");
    $stmt->execute(['pricelist_id' => $pricelist_id]);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$packages) {
        die("No packages found for Pricelist ID: $pricelist_id");
    }

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "Package List for Pricelist ID: $pricelist_id", 0, 1, 'C');
    $pdf->Ln(10);

    // Set table header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Term', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Speed (Mbps)', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Price (R pm)', 1, 1, 'C');

    // Set table data
    $pdf->SetFont('Arial', '', 12);
    foreach ($packages as $package) {
        $term = htmlspecialchars($package['PK_Term'] ?? 'N/A');
        $speed = htmlspecialchars($package['PK_Speed'] ?? 'N/A');
        $price = isset($package['PK_Price']) ? 'R ' . number_format($package['PK_Price'], 2) : 'N/A';

        $pdf->Cell(50, 10, $term, 1, 0, 'C');
        $pdf->Cell(50, 10, $speed, 1, 0, 'C');
        $pdf->Cell(50, 10, $price, 1, 1, 'C');
    }

    // Output PDF
    $pdf->Output();
} else {
    die("Invalid request: Pricelist ID is missing.");
}
?>
