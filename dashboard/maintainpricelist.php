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
    die("Connection failed: " . $e->getMessage());
}

// Function to log errors
function logError($message) {
    file_put_contents("error_log.txt", date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Base query for retrieving data
$query = "SELECT p.PK_ID, f.FNO_Name, pl.PR_Short_Description, p.PK_Term, p.PK_Speed, p.PK_Price 
    FROM package p
    JOIN fno f ON p.FNO_id = f.FNO_id
    JOIN pricelist pl ON p.PR_id = pl.PR_id";

try {
    
    
   
    // Handle Pricelist submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pricelist'])) {
        $pr_short = trim(htmlspecialchars($_POST['pr_short'] ?? ''));
        $pr_long = trim(htmlspecialchars($_POST['pr_long'] ?? ''));
        
        if (!empty($pr_short) && !empty($pr_long)) {
            $stmt = $pdo->prepare("INSERT INTO pricelist (PR_Short_Description, PR_Long_Description) VALUES (:short, :long)");
            $stmt->execute(['short' => $pr_short, 'long' => $pr_long]);
            $_SESSION['success_msg'] = "Pricelist added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: Both Short and Long descriptions are required.";
        }
    }

    if (isset($_GET['delete_pricelist'])) {
    $id = $_GET['delete_pricelist'];
    try {
        // Delete the pricelist
        $stmt = $pdo->prepare("DELETE FROM pricelist WHERE PR_id = :id");
        $stmt->execute(['id' => $id]);

        // Optionally, you can also delete any associated records from other tables (if needed)

        $_SESSION['success_msg'] = "Pricelist deleted successfully!";
        header("Location: maintainpricelist.php?success=deleted_pricelist");
        exit();
    } catch (PDOException $e) {
        logError("Deletion Error: " . $e->getMessage());
        $_SESSION['error_msg'] = "Error occurred while deleting the pricelist.";
    }
}


} catch (PDOException $e) {
    logError("Database Error: " . $e->getMessage());
    $_SESSION['error_msg'] = "A database error occurred. Please try again.";
} catch (Exception $e) {
    logError("General Error: " . $e->getMessage());
    $_SESSION['error_msg'] = "An unexpected error occurred.";
}
?> 


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="#">
    <link rel="icon" type="image/png" href="./img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="Ask online Form">
    <meta name="description"
        content="">
    <meta name="keywords"
        content="mobilewebdevelopment,HTML, CSS, JavaScript,Material,js,Forum ,webdesign ,website ,web ,webdesigner ,webdevelopment,Template,admin,dashboard,ebsitedesig,themeym,radwanweb,frontend-with-radwan">
    <meta name="robots" content="index, nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English">
    <meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<!-- Cache-Control Meta Tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>WhatsMyScore</title>
    <link rel="icon" type="image/png" href="wms_icon.png">
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="./css/fontawesome-all.min.css">
    <link href="./css/materil.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/responsive.css" rel="stylesheet" />
    <link href="./css/style.css" rel="stylesheet" />
    <script src="script.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="style.css?v=1.0.1">
<script src="script.js?v=1.0.1"></script>
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

/* Ensure buttons have the same size and behavior */
button.btn-sm, a.btn-sm {
    display: inline-block; /* Ensure both behave similarly */
    padding: 5px 10px; /* Adjust padding to make both buttons the same size */
    font-size: 14px; /* Ensure font size is the same */
    line-height: 1.5; /* Adjust line height for uniformity */
}



     </style>
  </head>

  <body class="">

    <!--<div class='light x1'></div>
    <div class='light x2'></div>
    <div class='light x3'></div>
    <div class='light x4'></div>
    <div class='light x5'></div>
    <div class='light x6'></div>
    <div class='light x7'></div>
    <div class='light x8'></div>
    <div class='light x9'></div>-->
    <div class="wrapper">
      <div
        class="sidebar"
        data-color="blue"
        data-background-color="white"
        data-image="../assets/img/sidebar-1.jpg"
      >
        <div class="logo">
          <a href="./index.php" class="simple-text logo-normal">
            <img src="./logo.png" alt="logo" style="margin-top: 40px; width: auto; height: 50px;" />
          </a>
        </div>
        <div class="sidebar-wrapper">
          <ul class="nav">
    <li class="nav-item">
        <a class="nav-link" href="./index.php">
            <img class="mr-2 img-small" src="./img/dashboard.png" />
            Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./add_user.php">
            <img class="mr-2 img-small" src="./img/add-user.png" />
            Add Users
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./admin.php">
            <img class="mr-2 img-small" src="./img/group.png" />
            Users
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./update_sales.php">
            <img class="mr-2 img-small" src="./img/administrator.png" />
            Add Sales
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./edit_sales.php">
            <img class="mr-2 img-small" src="./img/editing.png" />
            Edit Sales
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./sales_report.php">
            <img class="mr-2 img-small" src="./img/bar-chart.png" />
            Sales Report
        </a>
    </li>

    <!-- Maintain Tab with Collapse -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#maintainCollapse" data-toggle="collapse" aria-expanded="false">
            <img class="mr-2 img-small" src="./img/categorization.png" />
            Maintain Prices
        </a>
        <div id="maintainCollapse" class="collapse">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="./maintainfno.php">Maintain FNO</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintainpricelist.php">Maintain Pricelist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintainpackage.php">Maintain Package</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintaintandc.php">Maintain Terms & Cover</a>
                </li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="./view_pricelist_pdfs.php">
            <img class="mr-2 img-small" src="./img/smart-home.png" />
            View PDF
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="../logout.php">
            <img class="mr-2 img-small" src="./img/logout.png" />
            Logout
        </a>
    </li>
</ul>
        </div>
      </div>
      <div class="main-panel">
        <!-- Navbar -->
        <nav
          class="
            navbar navbar-expand-lg navbar-transparent navbar-absolute
            fixed-top
          "
        >
          <div class="container-fluid">
            <div class="navbar-wrapper">
              <a class="navbar-brand" href="javascript:;">Admin</a>
            </div>
            <button
              class="navbar-toggler"
              type="button"
              data-toggle="collapse"
              aria-controls="navigation-index"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="sr-only">Toggle navigation</span>
              <span class="navbar-toggler-icon icon-bar"></span>
              <span class="navbar-toggler-icon icon-bar"></span>
              <span class="navbar-toggler-icon icon-bar"></span>
            </button>
            <div class="collapse navbar-collapse">
             
              <!--<ul class="navbar-nav">
                <li class="nav-item dropdown">
                  <a
                    class="nav-link"
                    href="http://example.com"
                    id="navbarDropdownMenuLink"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="material-icons">notifications</i>
                    <span class="notification">5</span>
                    <p class="d-lg-none d-md-block">Some Actions</p>
                  </a>
                  <div
                    class="dropdown-menu dropdown-menu-right"
                    aria-labelledby="navbarDropdownMenuLink"
                  >
                    <a class="dropdown-item" href="#"
                      >No New Notifications</a
                    >
                  </div>
                </li>
                <li class="nav-item dropdown">
                  <a
                    class="nav-link"
                    href="javascript:;"
                    id="navbarDropdownProfile"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="material-icons">person</i>
                    <p class="d-lg-none d-md-block">Account</p>
                    <span class="hide-arrow-admin-text">
                      Admin
                      <i class="material-icons">arrow_drop_down</i>
                    </span>
                  </a>

                  <div
                    class="dropdown-menu dropdown-menu-right"
                    aria-labelledby="navbarDropdownProfile"
                  >
                    <a class="dropdown-item" href="user-profile.html">Profile</a>
                    <a class="dropdown-item" href="#">Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Log out</a>
                  </div>
                </li>
              </ul>-->
            </div>
          </div>
        </nav>
        <!-- End Navbar -->
<!-- JavaScript for the alert popup -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <script>
        alert("Success: <?php echo $_SESSION['success_msg']; ?>");
    </script>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <script>
        alert("Error: <?php echo $_SESSION['error_msg']; ?>");
    </script>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <div class="float-left">
                            <a>
                                <span class="material-icons custom-material-icon">description</span>
                                <span>Maintain</span>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                       
                       


                        <!-- ADD NEW PRICELIST FORM -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <form method="POST" class="card p-4 shadow mb-4">
            <h5 class="text-center">Add Pricelist</h5>

            <div class="mb-3">
                <label class="form-label">Short Description</label>
                <input type="text" name="pr_short" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Long Description</label>
                <textarea name="pr_long" class="form-control" required></textarea>
            </div>

            <div class="text-center">
                <button type="submit" name="submit_pricelist" class="btn btn-primary">Add Pricelist</button>
            </div>
        </form>
    </div>
</div>

<!-- Pricelist Table -->
                            <div class="col-md-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">Pricelists</h5>
                                    </div>
                                    <div class="card-body">
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
        <?php 
        try {
            // Fetch pricelist data using PDO
            $stmt = $pdo->prepare("SELECT PR_id, PR_Short_Description, PR_Long_Description FROM pricelist ORDER BY PR_Short_Description ASC");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PR_Short_Description']); ?></td>
                    <td><?php echo htmlspecialchars($row['PR_Long_Description']); ?></td>
                    <td>
                        <button type="button" class="custom-action-btn btn btn-warning editPricelist" data-id="<?php echo $row['PR_id']; ?>" style="width: 90px; height: 38px;">Edit</button>
                        <a href="#" class="btn btn-danger btn-sm" style="display: inline-block;" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $row['PR_id']; ?>" data-type="pricelist">Delete</a>
                    </td>
                </tr>
            <?php } 
        } catch (PDOException $e) {
            echo "Error fetching pricelist data: " . $e->getMessage();
        }
        ?>
    </tbody>
</table>

                                    </div>
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
                    <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="updatePricelistBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>






                    </div> <!-- End of Card Body -->
                </div> <!-- End of Card -->
            </div> <!-- End of Col-md-12 -->
        </div> <!-- End of Row -->
    </div> <!-- End of Container-fluid -->
</div> <!-- End of Content -->


    <div class="fixed-plugin"></div>
    <!--   Core JS Files   -->
    <script src="./js/vendor/jquery-3.2.1.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap-material-design.min.js"></script>
    <script src="./js/perfect-scrollbar.jquery.min.js"></script>
    <!-- Plugin for the momentJs  -->
    <script src="./js/moment.min.js"></script>
    <!--  Plugin for Sweet Alert -->
    <script src="./js/sweetalert2.js"></script>
    <!-- Forms Validations Plugin -->
    <script src="./js/jquery.validate.min.js"></script>
    <!-- Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
    <script src="./js/jquery.bootstrap-wizard.js"></script>
    <!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
    <script src="./js/bootstrap-selectpicker.js"></script>
    <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
    <script src="./js/bootstrap-datetimepicker.min.js"></script>
    <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
    <script src="./js/jquery.dataTables.min.js"></script>
    <!--  Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
    <script src="./js/bootstrap-tagsinput.js"></script>
    <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
    <script src="./js/jasny-bootstrap.min.js"></script>
    <!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
    <script src="./js/fullcalendar.min.js"></script>
    <!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
    <!-- Library for adding dinamically elements -->
    <script src="./js/arrive.min.js"></script>
    <!--  Google Maps Plugin    -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>
    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Chartist JS -->
    <script src="./js/chartist.min.js"></script>
    <!--  Notifications Plugin    -->
    <script src="./js/bootstrap-notify.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script
      src="./js/material-dashboard.min.js?v=2.1.2"
      type="text/javascript"
    ></script>
    <script src="./js/main.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<!-- jQuery -->

<!-- Bootstrap 5 -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



    <script>
    // Edit Pricelist
    $(document).on("click", ".editPricelist", function() {
        var id = $(this).data("id");  // Get the ID of the row to edit
        $.ajax({
            url: "fetch_data.php",
            type: "POST",
            data: { id: id, table: "pricelist" },  // Send ID and specify 'pricelist' table
            success: function(data) {
                var result = JSON.parse(data);
                $("#editPricelistId").val(result.PR_id);  // Fill in the modal fields
                $("#editPricelistShortDesc").val(result.PR_Short_Description);
                $("#editPricelistLongDesc").val(result.PR_Long_Description);
                // Show the modal using Bootstrap 5's Modal method
                var myModal = new bootstrap.Modal(document.getElementById('editPricelistModal'));
                myModal.show();
            },
            error: function(err) {
                console.log("Error fetching pricelist data:", err);
            }
        });
    });

    // Delete Pricelist
    $(document).on("click", "a[data-type='pricelist']", function() {
        var id = $(this).data("id");  // Get the ID of the pricelist to delete
        $("#confirmDeleteBtn").attr("href", "maintainpricelist.php?delete_pricelist=" + id); // Update the delete URL with the ID
    });

    // Scroll handling for FNO Table
    updateScroll("FNO_Table", "FNO_Table_Container");

    function updateScroll(tableId, containerId) {
        const tableContainer = document.getElementById(containerId);
        const tableBody = document.querySelector(`#${tableId} tbody`);
        const rowCount = tableBody.getElementsByTagName("tr").length;
        if (rowCount > 10) { 
            tableContainer.style.overflowY = "auto";
        } else {
            tableContainer.style.overflowY = "hidden";
        }
    }

    // Update Pricelist
$(document).on("click", "#updatePricelistBtn", function() {
    var id = $("#editPricelistId").val();
    var shortDesc = $("#editPricelistShortDesc").val();
    var longDesc = $("#editPricelistLongDesc").val();

    $.ajax({
        url: "update_pricelist.php",
        type: "POST",
        data: {
            id: id,
            short_desc: shortDesc,
            long_desc: longDesc
        },
        success: function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                alert("Pricelist updated successfully!");
                location.reload(); // Refresh the page to show the updated data
            } else {
                alert("Update failed: " + result.message);
            }
        },
        error: function(err) {
            console.log("Error updating pricelist:", err);
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
            data: { id: id, shortDesc: shortDesc, longDesc: longDesc, table: "pricelist" },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Simulate adding rows dynamically (for testing purposes)
    function addDummyRows(tableId, numRows) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        for (let i = 1; i <= numRows; i++) {
            let row = document.createElement("tr");
            row.innerHTML = `<td>Person ${i}</td><td>${20 + i}</td><td>Country ${i}</td>`;
            tableBody.appendChild(row);
        }
        updateScroll(tableId, `${tableId}_Container`);
    }

    addDummyRows("FNO_Table", 12); // Example: adding 12 rows to FNO table

    // Scroll handling for Subgroup Table
    updateScroll("Subgroup_Table", "Subgroup_Table_Container");

    // Simulate adding rows dynamically (for testing purposes)
    addDummyRows("Subgroup_Table", 8); // Example: adding 8 rows to Subgroup table

</script>


</body>
</html>