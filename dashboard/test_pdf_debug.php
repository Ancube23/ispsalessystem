<?php
require('fpdf186/fpdf.php');
require('src/autoload.php');
require('src/FPDI.php');

use setasign\Fpdi\Fpdi;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug Step 1: Check if Pricelist is Set
if (!isset($_GET['pricelist'])) {
    die("Debug: No pricelist parameter provided in the URL.");
}
$pricelist = $_GET['pricelist'];

// Debug Step 2: Fetch Pricelist ID
$pricelist_query = mysqli_query($conn, "SELECT PR_id FROM Pricelist WHERE PR_Short_Description = '$pricelist'");
$pricelist_data = mysqli_fetch_assoc($pricelist_query);
$pricelist_id = $pricelist_data['PR_id'] ?? null;

if (!$pricelist_id) {
    die("Debug: Pricelist '$pricelist' not found in the database.");

}

// Debug Step 3: Fetch Terms & Conditions
$terms_query = mysqli_query($conn, "SELECT Terms FROM tandc WHERE PR_id = '$pricelist_id'");
$terms_data = mysqli_fetch_assoc($terms_query);
$terms_text = $terms_data['Terms'] ?? "Debug: No terms and conditions found.";

 //Check if there are any rows
if (mysqli_num_rows($terms_query) > 0) {
    $terms_text = "";
    while ($terms_data = mysqli_fetch_assoc($terms_query)) {
        // Append each term to the $terms_text variable
        $terms_text .= $terms_data['Terms'] . "\n\n";
    }
} else {
    $terms_text = "Debug: No terms and conditions found.";
}

//echo "<h2>Debug Output</h2>";
//echo "<p><strong>Pricelist:</strong> $pricelist</p>";
//echo "<p><strong>Pricelist ID:</strong> $pricelist_id</p>";
//echo "<p><strong>Terms & Conditions:</strong><br>" . nl2br($terms_text) . "</p>";

// Debug Step 4: Try Generating the First Page
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Terms & Conditions", 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $terms_text);
$pdf->Ln(10);

$pdf->Output();
?>
