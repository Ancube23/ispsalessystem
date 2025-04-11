<?php
session_start();
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

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Include FPDF and FPDI
require('fpdf186/fpdf.php');
require('src/autoload.php');
require('src/FPDI.php');

use setasign\Fpdi\Fpdi;

// Handle Deletions
if (isset($_GET['delete_fno'])) {
    $id = $_GET['delete_fno'];
    mysqli_query($conn, "DELETE FROM FNO WHERE FNO_id='$id'");
    header("Location: view_pricelist.php?success=deleted_fno");
}

if (isset($_GET['delete_pricelist'])) {
    $id = $_GET['delete_pricelist'];
    mysqli_query($conn, "DELETE FROM Pricelist WHERE PR_id='$id'");
    header("Location: view_pricelist.php?success=deleted_pricelist");
}

if (isset($_GET['delete_package'])) {
    $id = $_GET['delete_package'];
    mysqli_query($conn, "DELETE FROM Package WHERE PK_ID='$id'");
    header("Location: view_pricelist.php?success=deleted_package");
}

// Fetch all data
$fno_result = mysqli_query($conn, "SELECT * FROM FNO ORDER BY FNO_Name ASC");
$pricelist_result = mysqli_query($conn, "SELECT * FROM Pricelist ORDER BY PR_Short_Description ASC");
$package_result = mysqli_query($conn, "SELECT p.PK_ID, f.FNO_Name, pl.PR_Short_Description, p.PK_Term, p.PK_Speed, p.PK_Price 
    FROM Package p
    JOIN FNO f ON p.FNO_id = f.FNO_id
    JOIN Pricelist pl ON p.PR_id = pl.PR_id
    ORDER BY f.FNO_Name ASC");

// Fetch all data
$fno_result = mysqli_query($conn, "SELECT * FROM FNO ORDER BY FNO_Name ASC");
$pricelist_result = mysqli_query($conn, "SELECT * FROM Pricelist ORDER BY PR_Short_Description ASC");

// Get filtering parameters for Packages only
$fno_filter = isset($_GET['fno_filter']) ? $_GET['fno_filter'] : '';
$pricelist_filter = isset($_GET['pricelist_filter']) ? $_GET['pricelist_filter'] : '';

$query = "SELECT p.PK_ID, f.FNO_Name, pl.PR_Short_Description, p.PK_Term, p.PK_Speed, p.PK_Price 
    FROM Package p
    JOIN FNO f ON p.FNO_id = f.FNO_id
    JOIN Pricelist pl ON p.PR_id = pl.PR_id";

$conditions = [];
if (!empty($fno_filter)) {
    $conditions[] = "f.FNO_id = '$fno_filter'";
}
if (!empty($pricelist_filter)) {
    $conditions[] = "pl.PR_id = '$pricelist_filter'";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY f.FNO_Name ASC";
$package_result = mysqli_query($conn, $query);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pricelist</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
/* Enable scrolling when there are many rows */
#FNO_Table_Container,
#Pricelist_Table_Container,
#Packages_Table_Container, 
#Terms_Table_Container {
    max-height: 300px; /* Limit the height */
    overflow-y: auto; /* Allow scrolling */
    display: block;
    border: 1px solid #ddd; /* Optional: visual separation */
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

/* Optional: Make table header sticky for better UX */
thead {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}


     </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">View Pricelist</h2>

        <!-- FNO Table -->
        <div class="card p-4 shadow mb-3">
            <h5>Fibre Network Operators</h5>
            <div id="FNO_Table_Container">
            <table id="FNO_Table" class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Logo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($fno_result)) { ?>
                <tr>
                    <td><?php echo $row['FNO_Name']; ?></td>
                    <td><img src="<?php echo $row['FNO_Logo']; ?>" width="50"></td>
                    <td>
                        <button class="btn btn-warning btn-sm editFNO" data-id="<?php echo $row['FNO_id']; ?>">Edit</button>
                        <a href="view_pricelist.php?delete_fno=<?php echo $row['FNO_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php } ?>
                </tbody>
            </table>
        </div>
        </div>

        <!-- Pricelist Table -->
        <div class="card p-4 shadow mb-3">
            <h5>Pricelists</h5>
            <div id="Pricelist_Table_Container">
            <table id="Pricelist_Table" class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Short Description</th>
                        <th>Long Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($pricelist_result)) { ?>
                <tr>
                    <td><?php echo $row['PR_Short_Description']; ?></td>
                    <td><?php echo $row['PR_Long_Description']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm editPricelist" data-id="<?php echo $row['PR_id']; ?>">Edit</button>
                        <a href="view_pricelist.php?delete_pricelist=<?php echo $row['PR_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php } ?>
                </tbody>
            </table>
        </div>
        </div>

        <!-- Package Table -->
        <div class="card p-4 shadow">
            <h5>Packages</h5>

            <!-- Dropdown Filters -->
    <form method="GET" id="filterForm">
        <div class="row">
            <!-- Fibre Operator Filter -->
            <div class="col-md-6">
                <label for="fno_filter">Fibre Operator:</label>
                <select name="fno_filter" id="fno_filter" class="form-control">
                    <option value="">All</option>
                    <?php
                    mysqli_data_seek($fno_result, 0); // Reset pointer to fetch data again
                    while ($row = mysqli_fetch_assoc($fno_result)) { ?>
                        <option value="<?php echo $row['FNO_id']; ?>" <?php if ($fno_filter == $row['FNO_id']) echo 'selected'; ?>>
                            <?php echo $row['FNO_Name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Pricelist Filter -->
            <div class="col-md-6">
                <label for="pricelist_filter">Pricelist:</label>
                <select name="pricelist_filter" id="pricelist_filter" class="form-control">
                    <option value="">All</option>
                     <?php
                    mysqli_data_seek($pricelist_result, 0); // Reset pointer to fetch data again
                    while ($row = mysqli_fetch_assoc($pricelist_result)) { ?>
                        <option value="<?php echo $row['PR_id']; ?>" <?php if ($pricelist_filter == $row['PR_id']) echo 'selected'; ?>>
                            <?php echo $row['PR_Short_Description']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </form><br>


            <div id="Packages_Table_Container">
            <table id="Package_Table" class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>FNO</th>
                        <th>Pricelist</th>
                        <th>Term</th>
                        <th>Speed (Mbps)</th>
                        <th>Price (R)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($package_result)) { ?>
                <tr>
                    <td><?php echo $row['FNO_Name']; ?></td>
                    <td><?php echo $row['PR_Short_Description']; ?></td>
                    <td><?php echo $row['PK_Term']; ?></td>
                    <td><?php echo $row['PK_Speed']; ?></td>
                    <td><?php echo $row['PK_Price']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm editPackage" data-id="<?php echo $row['PK_ID']; ?>">Edit</button>
                        <a href="view_pricelist.php?delete_package=<?php echo $row['PK_ID']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php } ?>
                </tbody>
            </table>
        </div>
        </div>

       <!-- DISPLAY EXISTING TERMS & CONDITIONS -->
<div class="card p-4 shadow mt-4">
    <h5>Existing Terms & Conditions</h5>

    <div id="Terms_Table_Container">
            <table id="Terms_Table" class="table table-bordered">
         <thead class="table-dark">
            <tr>
                <th>Pricelist</th>
                <th>Terms</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $terms_result = mysqli_query($conn, "SELECT tandc.TC_id, tandc.Terms, Pricelist.PR_Short_Description 
                                                 FROM tandc 
                                                 JOIN Pricelist ON tandc.PR_id = Pricelist.PR_id 
                                                 ORDER BY Pricelist.PR_Short_Description ASC");
            while ($row = mysqli_fetch_assoc($terms_result)) { ?>
                <tr>
                    <td><?php echo $row['PR_Short_Description']; ?></td>
                    <td><?php echo nl2br($row['Terms']); ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-term-btn" 
                                data-id="<?php echo $row['TC_id']; ?>" 
                                data-terms="<?php echo htmlspecialchars($row['Terms']); ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#editTermsModal">Edit</button>

                        <a href="delete_terms.php?id=<?php echo $row['TC_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>


    </div>

    <!-- FNO Edit Modal -->
<div class="modal fade" id="editFnoModal" tabindex="-1" aria-labelledby="editFnoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFnoModalLabel">Edit FNO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editFnoForm" enctype="multipart/form-data">
                    <input type="hidden" id="editFnoId">
                    <div class="mb-3">
                        <label class="form-label">FNO Name</label>
                        <input type="text" class="form-control" id="editFnoName">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" id="editFnoLogo">
                        <img id="logoPreview" src="" width="100" style="display:none; margin-top: 10px;">
                    </div>
                    <button type="button" class="btn btn-primary" id="updateFnoBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Pricelist Edit Modal -->
<div class="modal fade" id="editPricelistModal" tabindex="-1" aria-labelledby="editPricelistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPricelistModalLabel">Edit Pricelist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPricelistForm">
                    <input type="hidden" id="editPricelistId">
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" class="form-control" id="editPricelistShortDesc">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Long Description</label>
                        <textarea class="form-control" id="editPricelistLongDesc"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" id="updatePricelistBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Package Edit Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPackageModalLabel">Edit Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPackageForm">
                    <input type="hidden" id="editPackageId">
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <input type="text" class="form-control" id="editPackageTerm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Speed (Mbps)</label>
                        <input type="text" class="form-control" id="editPackageSpeed">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (R)</label>
                        <input type="text" class="form-control" id="editPackagePrice">
                    </div>
                    <button type="button" class="btn btn-primary" id="updatePackageBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT TERMS MODAL -->
<div class="modal fade" id="editTermsModal" tabindex="-1" aria-labelledby="editTermsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTermsModalLabel">Edit Terms & Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTermsForm">
                    <input type="hidden" id="editTcId">
                    <div class="mb-3">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea class="form-control" id="editTermsText" rows="5"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" id="updateTermsBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- AJAX -->
    <script>
       $(document).ready(function() {
         // Check rows when the page is ready
    checkTableRows("FNO");
    checkTableRows("Pricelist");
    checkTableRows("Package");

    $(document).on("click", ".editFNO", function() {
    var id = $(this).data("id");
    $.ajax({
        url: "fetch_data.php",
        type: "POST",
        data: { id: id, table: "FNO" },
        success: function(data) {
            var result = JSON.parse(data);
            $("#editFnoId").val(result.FNO_id);
            $("#editFnoName").val(result.FNO_Name);
            $("#logoPreview").attr("src", result.FNO_Logo).show();

            // Explicitly show the modal
            $("#editFnoModal").modal("show");
        }
    });
});


    // Edit Pricelist
    $(".editPricelist").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: "fetch_data.php",
            type: "POST",
            data: { id: id, table: "Pricelist" },
            success: function(data) {
                var result = JSON.parse(data);
                $("#editPricelistId").val(result.PR_id);
                $("#editPricelistShortDesc").val(result.PR_Short_Description);
                $("#editPricelistLongDesc").val(result.PR_Long_Description);
                $("#editPricelistModal").modal("show");
            }
        });
    });

    // Edit Package
    $(".editPackage").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: "fetch_data.php",
            type: "POST",
            data: { id: id, table: "Package" },
            success: function(data) {
                var result = JSON.parse(data);
                $("#editPackageId").val(result.PK_ID);
                $("#editPackageTerm").val(result.PK_Term);
                $("#editPackageSpeed").val(result.PK_Speed);
                $("#editPackagePrice").val(result.PK_Price);
                $("#editPackageModal").modal("show");
            }
        });
    });

    // Update FNO
    $("#updateFnoBtn").click(function() {
        var id = $("#editFnoId").val();
        var name = $("#editFnoName").val();
        var logo = $("#editFnoLogo")[0].files[0];
        
        var formData = new FormData();
        formData.append("id", id);
        formData.append("name", name);
        if (logo) formData.append("logo", logo);

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Update Pricelist
    $("#updatePricelistBtn").click(function() {
        var id = $("#editPricelistId").val();
        var shortDesc = $("#editPricelistShortDesc").val();
        var longDesc = $("#editPricelistLongDesc").val();

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: { id: id, shortDesc: shortDesc, longDesc: longDesc, table: "Pricelist" },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Update Package
    $("#updatePackageBtn").click(function() {
        var id = $("#editPackageId").val();
        var term = $("#editPackageTerm").val();
        var speed = $("#editPackageSpeed").val();
        var price = $("#editPackagePrice").val();

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: { id: id, term: term, speed: speed, price: price, table: "Package" },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

     function checkTableRows(table) {
    $.ajax({
        url: "fetch_data.php",
        type: "POST",
        data: { table: table },
        success: function(data) {
            var result = JSON.parse(data);
            var rowCount = result.rowCount;

            if (rowCount >= 10) {
                // Enable scrolling if there are 10 or more rows
                $("#" + table + "_Table").closest(".card").css({
                    "max-height": "300px", // Adjust based on your needs
                    "overflow-y": "auto"
                });
            } else {
                // Remove scrolling if there are fewer than 10 rows
                $("#" + table + "_Table").closest(".card").css({
                    "max-height": "none",
                    "overflow-y": "none"
                });
            }
        },
        error: function() {
            alert("Error fetching row count.");
        }
    });
}


});

document.addEventListener("DOMContentLoaded", function() {
    function updateScroll(tableId, containerId) {
        const tableContainer = document.getElementById(containerId);
        const tableBody = document.querySelector(`#${tableId} tbody`);

        const rowCount = tableBody.getElementsByTagName("tr").length;
        if (rowCount > 10) { 
            tableContainer.style.overflowY = "auto"; // Enable scrolling
        } else {
            tableContainer.style.overflowY = "hidden"; // Hide scroll if not needed
        }
    }

    // Check scrolling for all tables
    updateScroll("FNO_Table", "FNO_Table_Container");
    updateScroll("Pricelist_Table", "Pricelist_Table_Container");
    updateScroll("Packages_Table", "Packages_Table_Container");
    updateScroll("Terms_Table", "Terms_Table_Table_Container");

    // Simulating row addition to demonstrate scrolling effect
    function addDummyRows(tableId, numRows) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        for (let i = 1; i <= numRows; i++) { 
            let row = document.createElement("tr");
            if (tableId === "FNO_Table") {
                row.innerHTML = `<td>Person ${i}</td><td>${20 + i}</td><td>Country ${i}</td>`;
            } else if (tableId === "Pricelist_Table") {
                row.innerHTML = `<td>Plan ${i}</td><td>${50 * i} Mbps</td><td>$${10 * i}</td>`;
            } else {
                row.innerHTML = `<td>Category ${i}</td><td>Details ${i}</td>`;
            }
            tableBody.appendChild(row);
        }
        updateScroll(tableId, `${tableId}_Container`);
    }

    // Simulate adding rows dynamically (for testing)
    addDummyRows("FNO_Table", 12); 
    addDummyRows("Pricelist_Table", 15);
    addDummyRows("Packages_Table", 8);
    addDummyRows("Terms_Table", 8);
});

$(document).ready(function() {
    $("#fno_filter, #pricelist_filter").change(function() {
        $("#filterForm").submit();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Open modal and populate with data
    document.querySelectorAll(".edit-term-btn").forEach(button => {
        button.addEventListener("click", function () {
            let tcId = this.getAttribute("data-id");
            let terms = this.getAttribute("data-terms");

            document.getElementById("editTcId").value = tcId;
            document.getElementById("editTermsText").value = terms;
        });
    });

    // Update terms via AJAX
    document.getElementById("updateTermsBtn").addEventListener("click", function () {
        let tcId = document.getElementById("editTcId").value;
        let terms = document.getElementById("editTermsText").value;

        fetch("update_terms.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `tc_id=${tcId}&terms=${encodeURIComponent(terms)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    });
});

    </script>
</body>
</html>