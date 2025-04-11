<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricelist PDFs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Pricelist PDFs</h2>
        <div class="row mt-4">
            <?php
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

            // Fetch unique Pricelist categories
            $pricelist_result = mysqli_query($conn, "SELECT DISTINCT PR_Short_Description FROM Pricelist ORDER BY PR_Short_Description ASC");

            while ($row = mysqli_fetch_assoc($pricelist_result)) { 
                $pricelist = $row['PR_Short_Description'];

                
            ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow p-3 text-center">
                        <h5><?php echo $pricelist; ?> Pricelist</h5>

                        <a href="view_pdf.php?pricelist=<?php echo urlencode($pricelist); ?>" target="_blank" class="btn btn-success mb-2">View PDF</a>
                        <a href="generate_pdf.php?pricelist=<?php echo urlencode($pricelist); ?>" class="btn btn-primary">Download PDF</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
