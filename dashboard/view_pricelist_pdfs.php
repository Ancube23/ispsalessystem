

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

    <li class="nav-item active">
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


              <div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                        <div>
                            <a>
                                <span class="material-icons custom-material-icon">description</span>
                                <span>View PDF</span>
                            </a>
                        </div>
                        <div class="user-profile-nav">
                            <div class="btn-add-group"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="container mt-3">
                            <h2 class="text-center">Pricelist PDFs</h2>
                            <div class="row mt-4">
    <?php
    $servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";
    try {
        // Create a new PDO connection
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions for errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch associative arrays
        ]);

        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT DISTINCT PR_Short_Description FROM pricelist ORDER BY PR_Short_Description ASC");
        $stmt->execute();
        $pricelists = $stmt->fetchAll(); // Fetch all results

        foreach ($pricelists as $row) {
            $pricelist = $row['PR_Short_Description'];
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm p-4 text-center">
                    <h5 class="mb-3"><?php echo htmlspecialchars($pricelist); ?> Pricelist</h5>
                    <a href="view_pdf.php?pricelist=<?php echo urlencode($pricelist); ?>" target="_blank" class="btn btn-success mb-2">View PDF</a>
                </div>
            </div>
            <?php
        }
    } catch (PDOException $e) {
        echo "<p class='text-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


        
       

        
        
      </div>
    </div>
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



</body>
</html>