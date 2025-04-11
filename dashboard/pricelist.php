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

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Include FPDF and FPDI
require('fpdf186/fpdf.php');
require('src/autoload.php');
require('src/FPDI.php');

use setasign\Fpdi\Fpdi;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO pricelisthome (operator_name, price_1, price_2, price_3, price_4, price_5, price_6, price_7, price_8) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE price_1=VALUES(price_1), price_2=VALUES(price_2), price_3=VALUES(price_3), price_4=VALUES(price_4), price_5=VALUES(price_5), price_6=VALUES(price_6), price_7=VALUES(price_7), price_8=VALUES(price_8)");
    $stmt->bind_param("sdddddddd", $operator_name, $price1, $price2, $price3, $price4, $price5, $price6, $price7, $price8);

    // Process Opentel Prices
    if (isset($_POST['opentel'])) {
        $operator_name = 'Opentel';
        $price1 = $_POST['price1'] ?? 0;
        $price2 = $_POST['price2'] ?? 0;
        $price3 = $_POST['price3'] ?? 0;
        $price4 = $_POST['price4'] ?? 0;
        $price5 = $_POST['price5'] ?? 0;
        $price6 = $_POST['price6'] ?? 0;
        $price7 = $_POST['price7'] ?? 0;
        $price8 = $_POST['price8'] ?? 0;
        $stmt->execute();
    }

    // Process Zoom Prices
    if (isset($_POST['zoom'])) {
        $operator_name = 'Zoom';
        $price1 = $_POST['zoomprice1'] ?? 0;
        $price2 = $_POST['zoomprice2'] ?? 0;
        $price3 = $_POST['zoomprice3'] ?? 0;
        $price4 = $_POST['zoomprice4'] ?? 0;
        $price5 = $_POST['zoomprice5'] ?? 0;
        $price6 = $_POST['zoomprice6'] ?? 0;
        $price7 = $_POST['zoomprice7'] ?? 0;
        $price8 = null; // Not used for Zoom
        $stmt->execute();
    }

    // Process WC Zoom Prices
    if (isset($_POST['wczoom'])) {
        $operator_name = 'WC Zoom';
        $price1 = $_POST['wczoomprice1'] ?? 0;
        $price2 = $_POST['wczoomprice2'] ?? 0;
        $price3 = $_POST['wczoomprice3'] ?? 0;
        $price4 = $_POST['wczoomprice4'] ?? 0;
        $price5 = $_POST['wczoomprice5'] ?? 0;
        $price6 = $_POST['wczoomprice6'] ?? 0;
        $price7 = $_POST['wczoomprice7'] ?? 0;
        $price8 = null; // Not used for WC Zoom
        $stmt->execute();
    }

    $stmt->close();
}

// Fetch current prices from the database
function getCurrentPrices($conn, $operator) {
    $sql = "SELECT * FROM pricelisthome WHERE operator_name='$operator'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

$opentelPrices = getCurrentPrices($conn, 'Opentel');
$zoomPrices = getCurrentPrices($conn, 'Zoom');
$wczoomPrices = getCurrentPrices($conn, 'WC Zoom');

$conn->close();
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
        .table-responsive {
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-control {
            width: 100%;
        }
        .disabled-input {
            background-color: #e9ecef;
            pointer-events: none;
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
            <li class="nav-item">
              <a class="nav-link" href="./admin.php">
                <img class="mr-2 img-small" src="./img/group.png" />
                Users
              </a>
            </li>
            
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
            <li class="nav-item active">
              <a class="nav-link" href="./pricelist.php">
                <img class="mr-2 img-small" src="./img/smart-home.png" />
                Price List FTTH
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./pricelistb.php">
                <img class="mr-2 img-small" src="./img/teamwork.png" />
                Price List FTTB
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./admin_categories.php">
                <img class="mr-2 img-small" src="./img/categorization.png" />
                Categories
              </a>
            </li>
             <!--<li class="nav-item">
              <a class="nav-link" href="./settings.php">
                <img class="mr-2" src="./img/Group 1385.png" />
                Settings
              </a>
            </li>-->
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
             
             <!-- <ul class="navbar-nav">
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

        <br><br>
  <div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header card-header-primary">


                 <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                         autorenew </span
                        ><span>Price List</span></a
                      >
                    </div>


            </div>
            <div class="card-body">
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
      <input type="hidden" name="opentel" value="1">
        <label for="price1">10 Mbps:</label>
        <input type="text" name="price1" id="price1" value="<?= isset($opentelPrices['price_1']) ? $opentelPrices['price_1'] : 0 ?>" required><br><br>

        <label for="price2">25 Mbps:</label>
        <input type="text" name="price2" id="price2" value="<?= isset($opentelPrices['price_2']) ? $opentelPrices['price_2'] : 0 ?>" required><br><br>

        <label for="price3">35 Mbps:</label>
        <input type="text" name="price3" id="price3" value="<?= isset($opentelPrices['price_3']) ? $opentelPrices['price_3'] : 0 ?>" required><br><br>

        <label for="price4">50 Mbps:</label>
        <input type="text" name="price4" id="price4" value="<?= isset($opentelPrices['price_4']) ? $opentelPrices['price_4'] : 0 ?>" required><br><br>

        <label for="price5">75 Mbps:</label>
        <input type="text" name="price5" id="price5" value="<?= isset($opentelPrices['price_5']) ? $opentelPrices['price_5'] : 0 ?>" required><br><br>

        <label for="price6">100/50 Mbps:</label>
        <input type="text" name="price6" id="price6" value="<?= isset($opentelPrices['price_6']) ? $opentelPrices['price_6'] : 0 ?>" required><br><br>

        <label for="price7">150/75 Mbps:</label>
        <input type="text" name="price7" id="price7" value="<?= isset($opentelPrices['price_7']) ? $opentelPrices['price_7'] : 0 ?>" required><br><br>

        <label for="price8">200/75 Mbps:</label>
        <input type="text" name="price8" id="price8" value="<?= isset($opentelPrices['price_8']) ? $opentelPrices['price_8'] : 0 ?>" required><br><br>

        <button type="submit">Update Pricelist</button>
    </form>

</div>


    <div id="zoom-form" style="display: none;">
     <h5>Zoom Prices</h5>

    <form method="POST">
      <input type="hidden" name="zoom" value="1">
    <label for="zoomprice1">15 Mbps:</label>
    <input type="text" name="zoomprice1" id="zoomprice1" value="<?= $zoomPrices['price_1'] ?? 0 ?>" required><br><br>

    <label for="zoomprice2">30 Mbps:</label>
    <input type="text" name="zoomprice2" id="zoomprice2" value="<?= $zoomPrices['price_2'] ?? 0 ?>" required><br><br>

    <label for="zoomprice3">50 Mbps:</label>
    <input type="text" name="zoomprice3" id="zoomprice3" value="<?= $zoomPrices['price_3'] ?? 0 ?>" required><br><br>

    <label for="zoomprice4">100 Mbps:</label>
    <input type="text" name="zoomprice4" id="zoomprice4" value="<?= $zoomPrices['price_4'] ?? 0 ?>" required><br><br>

    <label for="zoomprice5">200 Mbps:</label>
    <input type="text" name="zoomprice5" id="zoomprice5" value="<?= $zoomPrices['price_5'] ?? 0 ?>" required><br><br>

    <label for="zoomprice6">500/250 Mbps:</label>
    <input type="text" name="zoomprice6" id="zoomprice6" value="<?= $zoomPrices['price_6'] ?? 0 ?>" required><br><br>

    <label for="zoomprice7">1000/500 Mbps:</label>
    <input type="text" name="zoomprice7" id="zoomprice7" value="<?= $zoomPrices['price_7'] ?? 0 ?>" required><br><br>

    <button type="submit">Update Pricelist</button>
</form>


</div>


    <div id="wczoom-form" style="display: none;">
    <h5>WC Zoom Prices</h5>

    <form method="POST">
      <input type="hidden" name="wczoom" value="1">
    <label for="wczoomprice1">15 Mbps:</label>
    <input type="text" name="wczoomprice1" id="wczoomprice1" value="<?= $wczoomPrices['price_1'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice2">30 Mbps:</label>
    <input type="text" name="wczoomprice2" id="wczoomprice2" value="<?= $wczoomPrices['price_2'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice3">50 Mbps:</label>
    <input type="text" name="wczoomprice3" id="wczoomprice3" value="<?= $wczoomPrices['price_3'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice4">100 Mbps:</label>
    <input type="text" name="wczoomprice4" id="wczoomprice4" value="<?= $wczoomPrices['price_4'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice5">200 Mbps:</label>
    <input type="text" name="wczoomprice5" id="wczoomprice5" value="<?= $wczoomPrices['price_5'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice6">500/250 Mbps:</label>
    <input type="text" name="wczoomprice6" id="wczoomprice6" value="<?= $wczoomPrices['price_6'] ?? 0 ?>" required><br><br>

    <label for="wczoomprice7">1000/500 Mbps:</label>
    <input type="text" name="wczoomprice7" id="wczoomprice7" value="<?= $wczoomPrices['price_7'] ?? 0 ?>" required><br><br>

    <button type="submit">Update Pricelist</button>
</form>


</div>

<form method="POST" action="generate_pdf.php">
        <button type="submit">View PDF</button>
    </form>
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
  </body>
</html>
