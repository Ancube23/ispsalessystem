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

// Fetch all FNOs
$fno_result = mysqli_query($conn, "SELECT * FROM FNO ORDER BY FNO_Name ASC");

// Initialize PDF
$pdf = new FPDF();
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Fibre Network Operators & Packages', 0, 1, 'C');
$pdf->Ln(5);

// Loop through each FNO
while ($fno = mysqli_fetch_assoc($fno_result)) {
    // Fetch packages for the current FNO
    $fno_id = $fno['FNO_id'];
    $package_result = mysqli_query($conn, "SELECT * FROM Package WHERE FNO_id = '$fno_id'");

    // Display FNO logo and name
    if (!empty($fno['FNO_Logo']) && file_exists($fno['FNO_Logo'])) {
        $pdf->Image($fno['FNO_Logo'], ($pdf->GetPageWidth() - 50) / 2, $pdf->GetY(), 50); // Centered logo
    }
    $pdf->Ln(20); // Space after logo
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $fno['FNO_Name'], 0, 1, 'C');
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(200, 200, 200);

    $tableWidth = 150; // Adjusted table width for centering
    $xStart = ($pdf->GetPageWidth() - $tableWidth) / 2; // Calculate center position

    $pdf->SetX($xStart);
    $pdf->Cell(50, 10, 'Term', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Speed (Mbps)', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Price (R pm)', 1, 1, 'C', true);

    // Table Content
    $pdf->SetFont('Arial', '', 12);
    while ($package = mysqli_fetch_assoc($package_result)) {
        $pdf->SetX($xStart);
        $pdf->Cell(50, 10, $package['PK_Term'], 1, 0, 'C');
        $pdf->Cell(50, 10, $package['PK_Speed'], 1, 0, 'C');
        $pdf->Cell(50, 10, 'R ' . number_format($package['PK_Price'], 2) . ' pm', 1, 1, 'C');
    }

    $pdf->Ln(10); // Space before next FNO
}

// Output PDF
$pdf->Output('D', 'FNO_Pricelist.pdf');
?>
