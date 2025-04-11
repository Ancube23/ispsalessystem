<?php
require('fpdf186/fpdf.php');
require('src/autoload.php');
require('src/FPDI.php');

use setasign\Fpdi\Fpdi;
$logFile = 'error_log.txt';

function logError($message) {
    global $logFile;
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    logError("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Check logs.");
}

if (!isset($_GET['pricelist'])) {
    logError("Invalid request: Pricelist not set.");
    die("Invalid request.");
}

$pricelist = $_GET['pricelist'];

try {
    $stmt = $conn->prepare("SELECT PR_id FROM pricelist WHERE PR_short_description = :pricelist");
    $stmt->execute(['pricelist' => $pricelist]);
    $pricelist_id = $stmt->fetchColumn();

    if (!$pricelist_id) {
        logError("Pricelist not found: " . $pricelist);
        die("Pricelist not found.");
    }

    $stmt = $conn->prepare("SELECT Terms FROM tandc WHERE PR_id = :pricelist_id");
    $stmt->execute(['pricelist_id' => $pricelist_id]);
    $terms_text = "";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $terms_text .= $row['Terms'] . "\n\n";
    }
    if (empty($terms_text)) {
        $terms_text = "No terms and conditions available.";
    }

    $stmt = $conn->prepare("SELECT DISTINCT f.* FROM fno f JOIN package p ON f.FNO_id = p.FNO_id WHERE p.PR_id = :pricelist_id ORDER BY f.FNO_name ASC");
    $stmt->execute(['pricelist_id' => $pricelist_id]);
    $fno_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT cp_filepath FROM cover_page WHERE PR_id = :pricelist_id");
    $stmt->execute(['pricelist_id' => $pricelist_id]);
    $cover_path = $stmt->fetchColumn();

    $pdf = new FPDI();
    $pdf->SetAutoPageBreak(true, 20);

    /// Check if a cover page exists and if it's a valid file
if ($cover_path && file_exists($cover_path)) {
    $file_extension = pathinfo($cover_path, PATHINFO_EXTENSION);
    if ($file_extension == 'pdf') {
        // Load the cover page PDF
        $pageCount = $pdf->setSourceFile($cover_path); // Get the number of pages in the cover PDF
        
        // Loop through all pages in the cover page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $template = $pdf->importPage($pageNo);  // Import the current page of the cover
            $pdf->useTemplate($template, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
        }
    }
} else {
    logError("Cover page not found: " . $cover_path);
}



    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "Terms & Conditions", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, $terms_text);
    $pdf->Ln(10);

    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "$pricelist Packages", 0, 1, 'C');
    $pdf->Ln(5);

    foreach ($fno_list as $fno) {
        if (isset($fno['FNO_id'])) {
            $fno_id = $fno['FNO_id'];
            $stmt = $conn->prepare("SELECT * FROM package WHERE FNO_id = :FNO_id AND PR_id = :pricelist_id");
            $stmt->execute(['FNO_id' => $fno_id, 'pricelist_id' => $pricelist_id]);
            $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add FNO Name and Logo
            if (!empty($fno['FNO_Logo']) && file_exists($fno['FNO_Logo'])) {
                $pdf->Image($fno['FNO_Logo'], ($pdf->GetPageWidth() - 50) / 2, $pdf->GetY(), 50);
            }
            $pdf->Ln(20);
            $pdf->SetFont('Arial', 'B', 14);

            // Center the FNO Name
            $pdf->Cell(0, 10, $fno['FNO_Name'], 0, 1, 'C'); // Centered text
            $pdf->Ln(5);

            // FNO packages
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(200, 200, 200);
            $tableWidth = 150;
            $xStart = ($pdf->GetPageWidth() - $tableWidth) / 2;

            $pdf->SetX($xStart);
            $pdf->Cell(50, 10, 'Term', 1, 0, 'C', true);
            $pdf->Cell(50, 10, 'Speed (Mbps)', 1, 0, 'C', true);
            $pdf->Cell(50, 10, 'Price (R pm)', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 12);
            foreach ($packages as $package) {
                $pdf->SetX($xStart);
                $pdf->Cell(50, 10, $package['PK_Term'], 1, 0, 'C');
                $pdf->Cell(50, 10, $package['PK_Speed'], 1, 0, 'C');
                $pdf->Cell(50, 10, 'R ' . number_format($package['PK_Price'], 2) . ' pm', 1, 1, 'C');
            }
            $pdf->Ln(10);

            // Check for subgroups
            $stmt = $conn->prepare("SELECT * FROM fno_subgroup WHERE FNO_id = :FNO_id");
            $stmt->execute(['FNO_id' => $fno_id]);
            $subgroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subgroups as $subgroup) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);

                // Center the Subgroup Name without the "Subgroup" prefix
                $pdf->Cell(0, 10, $subgroup['subgroup_name'], 0, 1, 'C'); // Centered text
                $pdf->Ln(5);

                $stmt = $conn->prepare("SELECT * FROM package WHERE subgroup_id = :subgroup_id AND PR_id = :pricelist_id");
                $stmt->execute(['subgroup_id' => $subgroup['subgroup_id'], 'pricelist_id' => $pricelist_id]);
                $subgroup_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Subgroup packages
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetX($xStart);
                $pdf->Cell(50, 10, 'Term', 1, 0, 'C', true);
                $pdf->Cell(50, 10, 'Speed (Mbps)', 1, 0, 'C', true);
                $pdf->Cell(50, 10, 'Price (R pm)', 1, 1, 'C', true);

                $pdf->SetFont('Arial', '', 12);
                foreach ($subgroup_packages as $package) {
                    $pdf->SetX($xStart);
                    $pdf->Cell(50, 10, $package['PK_Term'], 1, 0, 'C');
                    $pdf->Cell(50, 10, $package['PK_Speed'], 1, 0, 'C');
                    $pdf->Cell(50, 10, 'R ' . number_format($package['PK_Price'], 2) . ' pm', 1, 1, 'C');
                }
                $pdf->Ln(10);
            }

            // If the page is getting full, add a new page
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }
        }
    }

    $pdf->Output();
} catch (Exception $e) {
    logError("Error generating PDF: " . $e->getMessage());
    die("Error generating PDF. Check logs.");
}
?>
