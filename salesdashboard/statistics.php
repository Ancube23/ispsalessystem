<?php
session_start();
require '../db.php';

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Check if user is logged in and is a sales person
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    echo '<div class="slide-content">User not authorized</div>';
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

try {
    // Fetch user's name
    $userQuery = "SELECT name FROM users WHERE id = :user_id";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(':user_id', $userId);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $user['name'];

    // Fetch total sales and total targets for the past 30 days by default
    $endDate = date("Y-m-d"); // Today's date
    $startDate = date("Y-m-d", strtotime("first day of this month")); // 1st day of the current month

    // Fetch sales data for the selected date range
    $salesQuery = "
        SELECT date, sales, target
        FROM daily_sales
        WHERE user_id = :user_id
        AND date BETWEEN :start_date AND :end_date
        ORDER BY date";

    $stmt = $conn->prepare($salesQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for line graph
    $dates = [];
    $sales = [];
    $targets = [];

    foreach ($salesData as $data) {
        $dates[] = $data['date'];
        $sales[] = $data['sales'];
        $targets[] = $data['target'];
    }

    // Prepare the JSON data for JavaScript
    $jsonData = json_encode([
        'dates' => $dates,
        'sales' => $sales,
        'targets' => $targets,
    ]);

    // Generate HTML content for the slide
    $htmlContent = '<div class="slide-content2 center-content">';
    $htmlContent .= '<div class="center-container">';
    $htmlContent .= '<div id="dateRangePicker">';
    $htmlContent .= '<input type="date" id="startDate" value="' . $startDate . '">';
    $htmlContent .= '<input type="date" id="endDate" value="' . $endDate . '">';
    $htmlContent .= '</div>';
    $htmlContent .= '<canvas id="lineChart"></canvas><br><br>';
    $htmlContent .= '<canvas id="barChart"></canvas>';
    $htmlContent .= '</div>';
    $htmlContent .= '</div>';
    $htmlContent .= '<script>var salesData = ' . $jsonData . ';</script>';

    echo $htmlContent;
} catch (PDOException $e) {
    echo '<div class="slide-content2">Error: ' . $e->getMessage() . '</div>';
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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
<script src="script.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="style.css?v=1.0.1">
<script src="script.js?v=1.0.1"></script>

    <style>
       
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
        data-image=""
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
              <a class="nav-link" href="./admin.php">
               <img class="mr-2 img-small" src="./img/play-slideshow-button.png" />
               Show Slide
              </a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="./statistics.php">
                <img class="mr-2 img-small" src="./img/bar-chart.png" />
               Statistics
              </a>
            </li>
            <!--<li class="nav-item">
              <a class="nav-link" href="./sales_report.php">
                <img class="mr-2" src="./img/Group 1380.png" />
                Sales Reports
              </a>
            </li>
            <li class="nav-item">
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
              <form class="navbar-form">
                <div class="input-group custom-input no-border">
                  <input
                    type="text"
                    value=""
                    class="form-control"
                    placeholder="Search..."
                  />
                  <button
                    type="submit"
                    class="btn btn-danger btn-round btn-just-icon"
                  >
                    <i class="material-icons">search</i>
                    <div class="ripple-container"></div>
                  </button>
                </div>
              </form>
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

        <br><br>


 <div class="row">
            <div class="col-md-4 offset-md-3 custom-size">
                <div class="card">
                    <div class="card-header bg">
                        <h1></h1>
                    </div>
                    <div class="card-body">
                        <canvas id="chartjs_bar" width="800" height="400"></canvas>
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
    <!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
    <script src="./js/bootstrap-selectpicker.js"></script>
    <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
    <script src="./js/bootstrap-datetimepicker.min.js"></script>
    <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
    <script src="./js/jquery.dataTables.min.js"></script>
    <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
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


<script src="./js/vendor/jquery-3.2.1.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/perfect-scrollbar.jquery.min.js"></script>
    <script src="./js/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>

   <script>
document.addEventListener('DOMContentLoaded', function () {
    var startDateInput = document.getElementById('startDate');
    var endDateInput = document.getElementById('endDate');

    // Check if there are stored dates in session storage
    var storedStartDate = sessionStorage.getItem('startDate');
    var storedEndDate = sessionStorage.getItem('endDate');

    if (storedStartDate && storedEndDate) {
        startDateInput.value = storedStartDate;
        endDateInput.value = storedEndDate;
    }

    startDateInput.addEventListener('change', fetchAndRenderData);
    endDateInput.addEventListener('change', fetchAndRenderData);

    function fetchAndRenderData() {
        var startDate = startDateInput.value;
        var endDate = endDateInput.value;

        // Store selected dates in session storage
        sessionStorage.setItem('startDate', startDate);
        sessionStorage.setItem('endDate', endDate);

        fetchData(startDate, endDate);
    }

    function fetchData(startDate, endDate) {
        $.ajax({
            url: 'fetch_sales_data.php',
            method: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function (response) {
                var salesData = JSON.parse(response);
                renderLineChart(salesData);
                renderBarChart(salesData);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function renderLineChart(salesData) {
        var ctx = document.getElementById('lineChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.dates,
                datasets: [{
                    label: 'Sales',
                    data: salesData.sales,
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 2,
                    fill: false
                }, {
                    label: 'Targets',
                    data: salesData.targets,
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'MMM DD'
                            }
                        }
                    }]
                }
            }
        });
    }

    function renderBarChart(salesData) {
        var ctx = document.getElementById('barChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: salesData.dates,
                datasets: [{
                    label: 'Sales',
                    data: salesData.sales,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: 'Targets',
                    data: salesData.targets,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });
    }

    // Fetch data for the initial date range
    fetchAndRenderData();
});
</script>


  </body>
</html>
