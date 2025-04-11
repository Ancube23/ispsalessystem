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

// Fetch categories
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Default category is FTTH, but this can be changed based on user selection or logic
$category_id = 1;  // Default FTTH category ID, adjust as necessary

// Check if a category is selected in the request, else use default
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];
}

// Fetch operators based on selected category
$stmt = $conn->prepare("SELECT id, operator_name FROM fiber_operators WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$operators = $result->fetch_all(MYSQLI_ASSOC);

if (empty($operators)) {
    echo "<p>No operators found for category ID: $category_id</p>";
} else {
    echo "<ul>";
    foreach ($operators as $operator) {
        echo "<li>{$operator['operator_name']}</li>";
    }
    echo "</ul>";
}


// Fetch operator and packages for a specific operator
$packages = [];
$operator_name = '';  // Initialize $operator_name

if (isset($_GET['operator_id'])) {
    $operator_id = $_GET['operator_id'];
    
    // Fetch operator name based on operator_id
    $operator_result = $conn->query("SELECT operator_name FROM fiber_operators WHERE id = $operator_id");
    if ($operator_result->num_rows > 0) {
        $operator_data = $operator_result->fetch_assoc();
        $operator_name = $operator_data['operator_name'];  // Set the operator name
    }

    $packages = $conn->query("SELECT * FROM packages WHERE operator_id = $operator_id")->fetch_all(MYSQLI_ASSOC);
}

// Handle adding new operators
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['operator_name'])) {
    $category_id = $_POST['category_id'];
    $operator_name = $_POST['operator_name'];
    
    if (!empty($category_id) && !empty($operator_name)) {
        $stmt = $conn->prepare("INSERT INTO fiber_operators (category_id, operator_name) VALUES (?, ?)");
        $stmt->bind_param("is", $category_id, $operator_name);
        $stmt->execute();
        header("Location: admin_categories.php?category_id=$category_id");
        exit();
    }
}

// Handle adding/updating packages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['package_name'])) {
    $operator_id = $_POST['operator_id'];
    $package_name = $_POST['package_name'];
    $business_park_group = $_POST['business_park_group'] ?? NULL;

    // Insert into the packages table
    $stmt = $conn->prepare("INSERT INTO packages (operator_id, package_name, business_park_group) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $operator_id, $package_name, $business_park_group);
    $stmt->execute();
    $package_id = $stmt->insert_id;  // Get the ID of the inserted package

    // Insert speeds for the package into the package_speeds table
    $speeds = [];
    for ($i = 1; $i <= 8; $i++) {
        if (isset($_POST["speed_$i"]) && !empty($_POST["speed_$i"])) {
            $speeds[] = $_POST["speed_$i"];
        }
    }

    foreach ($speeds as $speed) {
        $stmt = $conn->prepare("INSERT INTO package_speeds (package_id, speed) VALUES (?, ?)");
        $stmt->bind_param("ii", $package_id, $speed);
        $stmt->execute();
    }

    header("Location: admin_categories.php?operator_id=$operator_id&category_id=$category_id");
    exit();
}



// Handle deleting packages
if (isset($_GET['delete_package'])) {
    $package_id = $_GET['delete_package'];
    $conn->query("DELETE FROM packages WHERE id = $package_id");
    header("Location: admin_categories.php?operator_id=" . $_GET['operator_id'] . "&category_id=$category_id");
    exit();
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Add new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
    $category_name = $_POST['category_name'];
    
    if (!empty($category_name)) {
        // Insert new category into categories table
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        
        // Redirect to refresh the page and show the updated categories
        header("Location: admin_categories.php");
        exit();
    }
}

// Fetch operators based on selected category
$operators = [];
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];
    $stmt = $conn->prepare("SELECT * FROM fiber_operators WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $operators = $result->fetch_all(MYSQLI_ASSOC);
}

// Add new operator for selected category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['operator_name']) && isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
    $operator_name = $_POST['operator_name'];
    
    if (!empty($operator_name)) {
        // Insert new operator into fiber_operators table
        $stmt = $conn->prepare("INSERT INTO fiber_operators (category_id, operator_name) VALUES (?, ?)");
        $stmt->bind_param("is", $category_id, $operator_name);
        $stmt->execute();
        
        // Redirect to refresh the page and show the updated operators
        header("Location: admin_categories.php?category_id=$category_id");
        exit();
    }
}

// Fetching speeds for each package before the HTML section
$package_speeds = []; // Initialize an array to store speeds for each package

// Loop through the packages and fetch the speeds for each package
foreach ($packages as $package) {
    $speeds = $conn->query("SELECT speed FROM package_speeds WHERE package_id = {$package['id']}")->fetch_all(MYSQLI_ASSOC);
    
    if ($speeds) {
        // Store speeds in the array with the package id as the key
        $speed_text = array_map(fn($s) => "{$s['speed']} Mbps", $speeds);
        $package_speeds[$package['id']] = implode(", ", $speed_text); // Store as a comma-separated string
    } else {
        $package_speeds[$package['id']] = "No speeds available"; // Default message if no speeds
    }
}

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
            <li class="nav-item">
              <a class="nav-link" href="./pricelistb.php">
                <img class="mr-2 img-small" src="./img/teamwork.png" />
                Price List FTTB
              </a>
            </li>
            <li class="nav-item active">
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
                        ><span>Categories</span></a
                      >
                    </div>


            </div>
            <div class="card-body">

    <center><h2>Manage Categories</h2><br>
    <form method="GET">
        <label for="category">Select Category:</label>
        <select name="category_id" id="category" onchange="this.form.submit()">
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $category_id ? 'selected' : '' ?>>
                    <?= $category['category_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
        </center>

    <center><?php if (isset($_GET['category_id'])): ?>
        <!-- Form to Add a New Fiber Operator for the Selected Category -->
        <h4>Add Fibre Operator to <?= $categories[array_search($_GET['category_id'], array_column($categories, 'id'))]['category_name'] ?></h4>
        <form method="POST">
            <input type="hidden" name="category_id" value="<?= $_GET['category_id'] ?>">
            <label for="operator_name">Operator Name:</label>
            <input type="text" name="operator_name" id="operator_name" required>
            <button type="submit" style="width: 120px;">Add Operator</button>

        </form>
        <?php endif; ?>

    </center>

  <h4>Existing Fiber Operators (<?= $categories[$category_id - 1]['category_name'] ?>)</h4>
    <ul>
        <?php foreach ($operators as $operator): ?>
            <li>
                <?= $operator['operator_name'] ?> 
                <a href="admin_categories.php?operator_id=<?= $operator['id'] ?>&category_id=<?= $category_id ?>">Manage Packages</a>
            </li>
        <?php endforeach; ?>
    </ul>


    <?php if (isset($_GET['operator_id'])): ?>
        <h4>Manage Packages for Operator: <?= $operator_name ?></h4>

        <form method="POST">
    <input type="hidden" name="operator_id" value="<?= $_GET['operator_id'] ?>">
    <input type="hidden" name="action" value="create">

    <label for="business_park_group">FTTB Group (for FTTB only):</label>
    <input type="text" name="business_park_group">

    <label for="package_name">Package Name:</label>
    <input type="text" name="package_name" required>

    <label for="speed_1">Speed 1 (Mbps):</label>
    <input type="number" name="speed_1" required>

    <label for="speed_2">Speed 2 (Mbps):</label>
    <input type="number" name="speed_2">

    <label for="speed_3">Speed 3 (Mbps):</label>
    <input type="number" name="speed_3">

    <label for="speed_4">Speed 4 (Mbps):</label>
    <input type="number" name="speed_4">

    <label for="speed_5">Speed 5 (Mbps):</label>
    <input type="number" name="speed_5">

    <label for="speed_6">Speed 6 (Mbps):</label>
    <input type="number" name="speed_6">

    <label for="speed_7">Speed 7 (Mbps):</label>
    <input type="number" name="speed_7">

    <label for="speed_8">Speed 8 (Mbps):</label>
    <input type="number" name="speed_8">

    <button type="submit" style="width: 120px;">Add Package</button>
</form>
<?php endif; ?>


<h4>Existing Packages</h4>
<ul>
    <?php foreach ($packages as $package): ?>
        <li>
            <?= $package['package_name'] ?> (Speeds: 
                <?php
                    // Fetch and display the speeds stored in the $package_speeds array
                    echo $package_speeds[$package['id']];
                ?>
            )
            <a href="admin_categories.php?operator_id=<?= $_GET['operator_id'] ?>&delete_package=<?= $package['id'] ?>&category_id=<?= $category_id ?>">Delete</a>
        </li>
    <?php endforeach; ?>
</ul>




    
    <!-- Form to add a new category -->
    <center><form method="POST">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required>
        
        <button type="submit" style="width: 120px;">Add Category</button>
    </form></center>

    <h3>Existing Categories</h3>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li><?= $category['category_name'] ?> (Category ID: <?= $category['id'] ?>)</li>
        <?php endforeach; ?>
    </ul>
<br><br>


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
function updateBusinessParks() {
    let operator = document.getElementById("fiber-operator").value;
    let businessParkSelect = document.getElementById("business-park");

    fetch("get_business_parks.php?operator=" + encodeURIComponent(operator))
        .then(response => response.json())
        .then(data => {
            businessParkSelect.innerHTML = "";
            data.forEach(park => {
                let option = document.createElement("option");
                option.value = park;
                option.textContent = park;
                businessParkSelect.appendChild(option);
            });
            updateForm(operator, data[0]); // Auto-update form with first business park
        });
}

function updateForm(operator, businessPark) {
    document.querySelector("input[name='operator_name']").value = operator;
    document.querySelector("input[name='business_park_name']").value = businessPark;
}
</script>
  </body>
</html>
