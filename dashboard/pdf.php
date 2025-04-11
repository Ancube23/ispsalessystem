<?php
// Include FPDF and FPDI
require('fpdf186/fpdf.php');
require('src/autoload.php'); // Adjust path based on your directory structure
require('src/FPDI.php'); // Path to FPDI.php in your src folder

use setasign\Fpdi\Fpdi;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Opentel Prices
    $price1 = $_POST['price1'] ?? '';
    $price2 = $_POST['price2'] ?? '';
    $price3 = $_POST['price3'] ?? '';
    $price4 = $_POST['price4'] ?? '';
    $price5 = $_POST['price5'] ?? '';
    $price6 = $_POST['price6'] ?? '';
    $price7 = $_POST['price7'] ?? '';
    $price8 = $_POST['price8'] ?? '';

    // Zoom Prices
    $zoomprice1 = $_POST['zoomprice1'] ?? '';
    $zoomprice2 = $_POST['zoomprice2'] ?? '';
    $zoomprice3 = $_POST['zoomprice3'] ?? '';
    $zoomprice4 = $_POST['zoomprice4'] ?? '';
    $zoomprice5 = $_POST['zoomprice5'] ?? '';
    $zoomprice6 = $_POST['zoomprice6'] ?? '';
    $zoomprice7 = $_POST['zoomprice7'] ?? '';

    //WC Zoom Prices
    $wczoomprice1 = $_POST['wczoomprice1'] ?? '';
    $wczoomprice2 = $_POST['wczoomprice2'] ?? '';
    $wczoomprice3 = $_POST['wczoomprice3'] ?? '';
    $wczoomprice4 = $_POST['wczoomprice4'] ?? '';
    $wczoomprice5 = $_POST['wczoomprice5'] ?? '';
    $wczoomprice6 = $_POST['wczoomprice6'] ?? '';
    $wczoomprice7 = $_POST['wczoomprice7'] ?? '';

    // Initialize FPDI
    $pdf = new FPDI();

    // Load the PDF template
    $templatePath = 'pricelist.pdf';
    $pageCount = $pdf->setSourceFile($templatePath);

    // Import each page from the template
    for ($i = 1; $i <= $pageCount; $i++) {
        $tplId = $pdf->importPage($i);
        $pdf->AddPage();
        $pdf->useTemplate($tplId, 0, 0);

        // Draw a grid on the page (optional: remove once you have the correct coordinates)
        $pdf->SetDrawColor(255, 0, 0); // Red color for grid lines
        $pdf->SetLineWidth(0.5);
        for ($x = 0; $x < 210; $x += 10) { // 10mm grid interval (adjust as necessary)
            $pdf->Line($x, 0, $x, 297); // Vertical grid lines (A4 height = 297mm)
        }
        for ($y = 0; $y < 297; $y += 10) { // 10mm grid interval (adjust as necessary)
            $pdf->Line(0, $y, 210, $y); // Horizontal grid lines (A4 width = 210mm)
        }

        // Write text only on the desired page (e.g., page 4)
        if ($i === 4) {
            $pdf->AddFont('calibri', '', 'calibri.php');
            $pdf->AddFont('calibri', 'B', 'calibrib.php');
            $pdf->SetFont('calibri', 'B', 11); // Use Calibri (Body) Bold with size 11
            $pdf->SetTextColor(255, 255, 255); // Set text color to white

            // Write Opentel Prices with "R" and "pm"
            $pdf->SetXY(70, 40);
            $pdf->Write(10, 'R' . $price1 . 'pm');

            $pdf->SetXY(100, 40);
            $pdf->Write(10, 'R' . $price2 . 'pm');

            $pdf->SetXY(40, 60);
            $pdf->Write(10, 'R' . $price3 . 'pm');

            $pdf->SetXY(70, 60);
            $pdf->Write(10, 'R' . $price4 . 'pm');

            $pdf->SetXY(100, 60);
            $pdf->Write(10, 'R' . $price5 . 'pm');

            $pdf->SetXY(40, 80);
            $pdf->Write(10, 'R' . $price6 . 'pm');

            $pdf->SetXY(70, 80);
            $pdf->Write(10, 'R' . $price7 . 'pm');

            $pdf->SetXY(100, 80);
            $pdf->Write(10, 'R' . $price8 . 'pm');

            // Write Zoom Prices with "R" and "pm"
            $pdf->SetXY(60, 157);
            $pdf->Write(10, 'R' . $zoomprice1 . 'pm');

            $pdf->SetXY(100, 157);
            $pdf->Write(10, 'R' . $zoomprice2 . 'pm');

            $pdf->SetXY(35, 180);
            $pdf->Write(10, 'R' . $zoomprice3 . 'pm');

            $pdf->SetXY(60, 180);
            $pdf->Write(10, 'R' . $zoomprice4 . 'pm');

            $pdf->SetXY(100, 180);
            $pdf->Write(10, 'R' . $zoomprice5 . 'pm');

            $pdf->SetXY(42, 205);
            $pdf->Write(10, 'R' . $zoomprice6 . 'pm');

            $pdf->SetXY(80, 205);
            $pdf->Write(10, 'R' . $zoomprice7 . 'pm');


            // Write Zoom Prices with "R" and "pm"
            $pdf->SetXY(170, 152);
            $pdf->Write(10, 'R' . $wczoomprice1 . 'pm');

            $pdf->SetXY(140, 172);
            $pdf->Write(10, 'R' . $wczoomprice2 . 'pm');

            $pdf->SetXY(170, 172);
            $pdf->Write(10, 'R' . $wczoomprice3 . 'pm');

            $pdf->SetXY(140, 192);
            $pdf->Write(10, 'R' . $wczoomprice4 . 'pm');

            $pdf->SetXY(170, 192);
            $pdf->Write(10, 'R' . $wczoomprice5 . 'pm');

            $pdf->SetXY(140, 213);
            $pdf->Write(10, 'R' . $wczoomprice6 . 'pm');

            $pdf->SetXY(170, 213);
            $pdf->Write(10, 'R' . $wczoomprice7 . 'pm');
        }
    }

    // Output the updated PDF
    $pdf->Output('I', 'newpricelist.pdf');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update PDF Prices</title>
    <script>
        function showForm(operator) {
            // Hide all forms
            document.getElementById('opentel-form').style.display = 'none';
            document.getElementById('zoom-form').style.display = 'none';
            document.getElementById('wczoom-form').style.display = 'none';

            // Show the selected form
            document.getElementById(operator + '-form').style.display = 'block';
        }

        // Show the default form on page load
        window.onload = function() {
            showForm('opentel');
        }
    </script>
</head>
<body>
    <h1>Price Updates</h1>

      <label for="fiber-operator">Select Fiber Operator:</label>
    <select id="fiber-operator" onchange="showForm(this.value)">
        <option value="opentel" selected>Opentel Prices</option>
        <option value="zoom">Zoom Prices</option>
        <option value="wczoom">WC Zoom Prices</option>
    </select>


    <div id="opentel-form">
     <h5>Opentel Prices</h5>

    <form method="POST">
        <label for="price1">10 Mbps:</label>
        <input type="text" name="price1" id="price1" required><br><br>

        <label for="price2">25 Mbps:</label>
        <input type="text" name="price2" id="price2" required><br><br>

        <label for="price3">35 Mbps:</label>
        <input type="text" name="price3" id="price3" required><br><br>

        <label for="price4">50 Mbps:</label>
        <input type="text" name="price4" id="price4" required><br><br>

        <label for="price5">75 Mbps:</label>
        <input type="text" name="price5" id="price5" required><br><br>

        <label for="price6">100/50 Mbps:</label>
        <input type="text" name="price6" id="price6" required><br><br>

        <label for="price7">150/75 Mbps:</label>
        <input type="text" name="price7" id="price7" required><br><br>

        <label for="price8">200/75 Mbps:</label>
        <input type="text" name="price8" id="price8" required><br><br>

        <button type="submit">Update Pricelist</button>
    </form>

</div>


    <div id="zoom-form" style="display: none;">
     <h5>Zoom Prices</h5>

    <form method="POST">
        <label for="zoomprice1">15 Mbps:</label>
        <input type="text" name="zoomprice1" id="zoomprice1" required><br><br>

        <label for="zoomprice2">30 Mbps:</label>
        <input type="text" name="zoomprice2" id="zoomprice2" required><br><br>

        <label for="zoomprice3">50 Mbps:</label>
        <input type="text" name="zoomprice3" id="zoomprice3" required><br><br>

        <label for="zoomprice4">100 Mbps:</label>
        <input type="text" name="zoomprice4" id="zoomprice4" required><br><br>

        <label for="zoomprice5">200 Mbps:</label>
        <input type="text" name="zoomprice5" id="zoomprice5" required><br><br>

        <label for="zoomprice6">500/250 Mbps:</label>
        <input type="text" name="zoomprice6" id="zoomprice6" required><br><br>

        <label for="zoomprice7">1000/500 Mbps:</label>
        <input type="text" name="zoomprice7" id="zoomprice7" required><br><br>

        <button type="submit">Update Pricelist</button>
    </form>

</div>


    <div id="wczoom-form" style="display: none;">
    <h5>WC Zoom Prices</h5>

    <form method="POST">
        <label for="wczoomprice1">15 Mbps:</label>
        <input type="text" name="wczoomprice1" id="wczoomprice1" required><br><br>

        <label for="wczoomprice2">30 Mbps:</label>
        <input type="text" name="wczoomprice2" id="wczoomprice2" required><br><br>

        <label for="wczoomprice3">50 Mbps:</label>
        <input type="text" name="wczoomprice3" id="wczoomprice3" required><br><br>

        <label for="wczoomprice4">100 Mbps:</label>
        <input type="text" name="wczoomprice4" id="wczoomprice4" required><br><br>

        <label for="wczoomprice5">200 Mbps:</label>
        <input type="text" name="wczoomprice5" id="wczoomprice5" required><br><br>

        <label for="wczoomprice6">500/250 Mbps:</label>
        <input type="text" name="wczoomprice6" id="wczoomprice6" required><br><br>

        <label for="wczoomprice7">1000/500 Mbps:</label>
        <input type="text" name="wczoomprice7" id="wczoomprice7" required><br><br>

        <button type="submit">Update Pricelist</button>
    </form>

</div>

</body>
</html>
