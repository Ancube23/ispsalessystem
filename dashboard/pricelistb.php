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
    $stmt = $conn->prepare("INSERT INTO package_prices (package_id, price_name, price) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            price_name=VALUES(price_name), price=VALUES(price)");

    $stmt->bind_param("iss", $package_id, $price_name, $price);

    // Process Prices
    if (isset($_POST['submit'])) {
        $package_id = $_POST['package_id'];
        foreach ($_POST['speeds'] as $speedIndex => $speedValue) {
            $price_name = $speedValue; // The speed as the price name
            $price = $_POST['price_' . $speedIndex]; // Price associated with that speed
            $stmt->execute();
        }
    }

    $stmt->close();
}

// Fetch operators from the database, excluding category_id 1 (FTTH)
function getOperators($conn) {
    $sql = "SELECT * FROM fiber_operators WHERE category_id != 1"; // Exclude FTTH operators
    $result = $conn->query($sql);
    $operators = [];
    while ($row = $result->fetch_assoc()) {
        $operators[] = $row;
    }
    return $operators;
}

// Fetch packages for a given operator
function getPackages($conn, $operator_id) {
    // SQL to fetch packages related to the operator_id
    $sql = "SELECT * FROM packages WHERE operator_id='$operator_id'";
    $result = $conn->query($sql);
    
    // If no packages are found for the operator, return an empty array
    if ($result->num_rows > 0) {
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row; // Add each package to the array
        }
        return $packages;
    } else {
        // If no packages exist for the operator, return an empty array
        return [];
    }
}


// Fetch speeds for a given package
function getPackageSpeeds($conn, $package_id) {
    $sql = "SELECT * FROM package_speeds WHERE package_id='$package_id'";
    $result = $conn->query($sql);
    $speeds = [];
    while ($row = $result->fetch_assoc()) {
        $speeds[] = $row['speed'];
    }
    return $speeds;
}

$operators = getOperators($conn);
$selectedOperator = $operators[0]['id'] ?? ''; // Default to first operator if exists
$packages = ($selectedOperator) ? getPackages($conn, $selectedOperator) : [];
$selectedPackage = $packages[0]['id'] ?? ''; // Default to first package if available
$speeds = ($selectedPackage) ? getPackageSpeeds($conn, $selectedPackage) : [];

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
            <li class="nav-item">
              <a class="nav-link" href="./pricelist.php">
                <img class="mr-2 img-small" src="./img/smart-home.png" />
                Price List FTTH
              </a>
            </li>
            <li class="nav-item active">
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
   <h1>Price Updates for FTTB</h1>

<label for="fiber-operator">Select Fiber Operator:</label>
<select id="fiber-operator" onchange="updatePackages()">
    <option value="">Select an Operator</option>
    <?php foreach ($operators as $operator): ?>
        <option value="<?= htmlspecialchars($operator['id']) ?>" <?= ($operator['id'] == $selectedOperator) ? 'selected' : '' ?>>
            <?= htmlspecialchars($operator['operator_name']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="package">Select Package:</label>
<select id="package" onchange="updateSpeeds()">
    <option value="">Select a Package</option>
    <?php foreach ($packages as $package): ?>
        <option value="<?= htmlspecialchars($package['id']) ?>" <?= ($package['id'] == $selectedPackage) ? 'selected' : '' ?>>
            <?= htmlspecialchars($package['package_name']) ?>
        </option>
    <?php endforeach; ?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img id="refreshImage" src="recycling-symbol.png" alt="Refresh" style="cursor: pointer; width: 30px; height: 30px;" onclick="refreshPriceInputs()" />

<div id="form-container">
    <h5><?= htmlspecialchars($operators[$selectedOperator]['operator_name'] ?? ''); ?> - <?= htmlspecialchars($packages[$selectedPackage]['package_name'] ?? ''); ?> Prices</h5>

   <form method="POST" action="update_prices.php" id="priceForm">
    
    <input type="hidden" name="package_id" value="<?= htmlspecialchars($selectedPackage) ?>">

    <?php foreach ($speeds as $index => $speed): ?>
        <label for='price_<?= $index ?>'>Price for <?= $speed ?> Mbps:</label>
        <input type='text' name='price_<?= $index ?>' id='price_<?= $index ?>' value='0' required><br><br>
    <?php endforeach; ?>

    <!-- Add onclick event to the submit button -->
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

    function submitForm() {
    console.log('Submit button clicked');
    var form = document.getElementById('priceForm');
    console.log('Form data:', new FormData(form));  // Check form data
    form.submit();  // Submit the form
}

// Update Packages based on selected Operator
function updatePackages() {
    let operatorId = document.getElementById("fiber-operator").value;
    let packageSelect = document.getElementById("package");

    if (!operatorId) {
        packageSelect.innerHTML = "<option value=''>Select a Package</option>";
        return;
    }

    fetch("get_packages.php?operator_id=" + encodeURIComponent(operatorId))
        .then(response => response.json())
        .then(data => {
            packageSelect.innerHTML = "<option value=''>Select a Package</option>";
            if (data.length > 0) {
                data.forEach(package => {
                    let option = document.createElement("option");
                    option.value = package.id;
                    option.textContent = package.package_name;
                    packageSelect.appendChild(option);
                });
                updateSpeeds(); // Update speeds based on the first package
            } else {
                packageSelect.innerHTML = "<option value=''>No Packages Available</option>";
            }
        });
}

// Update Speeds based on selected Package
function updateSpeeds() {
    let packageId = document.getElementById("package").value;

    if (!packageId) return; // Do nothing if no package is selected

    // Fetch speeds for the selected package
    fetch("get_speeds.php?package_id=" + encodeURIComponent(packageId))
        .then(response => response.json())
        .then(data => {
            let formContainer = document.getElementById("form-container");
            formContainer.innerHTML = "<h5>Update Prices for Selected Package</h5>";  // Reset form container

            if (data.length > 0) {
                // Loop through the speeds and create input fields for each speed
                data.forEach((speed, index) => {
                    formContainer.innerHTML += `<label for='price_${index}'>Price for ${speed} Mbps:</label>`;
                    formContainer.innerHTML += `<input type='text' name='price_${index}' id='price_${index}' value='0' required><br><br>`;
                });
                formContainer.innerHTML += "<button type='submit'>Update Pricelist</button>";  // Add submit button after input fields
            } else {
                formContainer.innerHTML += "<p>No speeds available for this package.</p>";
            }
        })
        .catch(error => {
            console.error('Error fetching speeds:', error);
        });
}

// Refresh price inputs based on selected operator and package
function refreshPriceInputs() {
    console.log('Refresh button clicked'); // Debugging log

    let operatorId = document.getElementById("fiber-operator").value;
    let packageId = document.getElementById("package").value;

    if (!operatorId || !packageId) {
        alert('Please select both operator and package first.');
        return;
    }

    console.log('Selected Operator ID:', operatorId);  // Debugging log
    console.log('Selected Package ID:', packageId);    // Debugging log

    // Fetch speeds for the selected package
    fetch("get_speeds.php?package_id=" + encodeURIComponent(packageId))
        .then(response => response.json())
        .then(data => {
            let formContainer = document.getElementById("form-container");
            formContainer.innerHTML = "<h5>Update Prices for Selected Package</h5>";  // Reset form container

            if (data.length > 0) {
                // Loop through the speeds and create input fields for each speed
                data.forEach((speed, index) => {
                    formContainer.innerHTML += `<label for='price_${index}'>Price for ${speed} Mbps:</label>`;
                    formContainer.innerHTML += `<input type='text' name='price_${index}' id='price_${index}' value='0' required><br><br>`;
                });
                formContainer.innerHTML += "<button type='submit'>Update Pricelist</button>";  // Add submit button after input fields
            } else {
                formContainer.innerHTML += "<p>No speeds available for this package.</p>";
            }
        })
        .catch(error => {
            console.error('Error fetching speeds:', error);
        });
}

// Ensure that the refresh icon is correctly triggering the function
document.getElementById("refreshImage").addEventListener("click", refreshPriceInputs);
</script>

  </body>
</html>
