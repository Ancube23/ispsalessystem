<?php
session_start();
require '../db.php';

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'salesadmin') {
    header("Location: ../login.php");
    exit;
}

// Initialize variables to avoid undefined variable warnings
$totalSales = 0;
$monthlySales = 0;
$newUsers = 0;
$totalUsers = 0;
$totalRejects = 0;
$progress_date = 0;  // Initialize progress_date variable
$monthlyRejects = 0; // Initialize monthlyRejects variable
$combinedMonthlyTarget = 0;

// Initialize variables to avoid undefined variable warnings
$salesUsersData = [];
$salesAdminsData = [];

// Define start and end dates for the current month
$endDate = date("Y-m-d");
$startDate = date("Y-m-01");
$last30DaysStartDate = date("Y-m-d", strtotime("-30 days"));
$currentMonthName = date("F"); // Get the full name of the current month
$current_month = date("Y-m-01"); // Ensure the format is Y-m-01

try {
    // Get total sales, monthly sales, new users, total users, and total rejects in a single query
    $statsQuery = "
    SELECT 
        COALESCE(SUM(ds.sales), 0) AS total_sales,
        SUM(CASE WHEN ds.date BETWEEN :start_date AND :end_date THEN ds.sales ELSE 0 END) AS monthly_sales,
        COUNT(CASE WHEN u.role != 'admin' AND u.created_at BETWEEN :last_30_days_start_date AND :end_date THEN u.id END) AS new_users,
        COUNT(CASE WHEN u.role != 'admin' THEN u.id END) AS total_users,
        COALESCE(SUM(ds.rejects), 0) AS total_rejects,
        SUM(CASE WHEN ds.date BETWEEN :start_date AND :end_date THEN ds.rejects ELSE 0 END) AS monthly_rejects
    FROM users u
    LEFT JOIN daily_sales ds ON ds.user_id = u.id
    WHERE u.role != 'admin'";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->bindParam(':last_30_days_start_date', $last30DaysStartDate);
    $stmt->execute();
    $statsData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extract data from the statsData array
    $totalSales = $statsData['total_sales'] ?? 0;
    $monthlySales = $statsData['monthly_sales'] ?? 0;
    $newUsers = $statsData['new_users'] ?? 0;
    $totalUsers = $statsData['total_users'] ?? 0;
    $totalRejects = $statsData['total_rejects'] ?? 0;
    $monthlyRejects = $statsData['monthly_rejects'] ?? 0;

    // Calculate progress_date sales from the start of the current month to today
    $progressDateQuery = "
    SELECT COALESCE(SUM(ds.sales), 0) AS progress_date_sales
    FROM daily_sales ds
    JOIN users u ON ds.user_id = u.id
    WHERE ds.date BETWEEN :start_date AND :end_date
      AND (u.active = 1 
           OR (u.active = 0 
               AND (u.deactivated_at IS NULL 
                    OR MONTH(u.deactivated_at) = MONTH(:current_date))))";

$progressDateStmt = $conn->prepare($progressDateQuery);
$progressDateStmt->bindParam(':start_date', $startDate);
$progressDateStmt->bindParam(':end_date', $endDate);
$progressDateStmt->bindParam(':current_date', $currentDate);
$progressDateStmt->execute();
$progressDateData = $progressDateStmt->fetch(PDO::FETCH_ASSOC);
$progress_date = $progressDateData['progress_date_sales'] ?? 0;


    // Count all users with roles other than admin
    $allUsersQuery = "SELECT COUNT(*) AS all_users FROM users WHERE role != 'admin'";
    $allUsersStmt = $conn->prepare($allUsersQuery);
    $allUsersStmt->execute();
    $allUsersRow = $allUsersStmt->fetch(PDO::FETCH_ASSOC);
    $allUsers = $allUsersRow['all_users'] ?? 0;

    // Get total users
    $totalUsersQuery = "SELECT COUNT(*) AS total_users FROM users";
    $totalUsersResult = $conn->query($totalUsersQuery);
    if ($totalUsersResult) {
        $totalUsersRow = $totalUsersResult->fetch(PDO::FETCH_ASSOC);
        $totalUsers = $totalUsersRow['total_users'] ?? 0;
    }

    // Query to get sales and targets for users with roles 'sales' or 'salesadmin'
    $salesQuery = "
    SELECT u.name, u.role, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
    FROM users u
    LEFT JOIN daily_sales ds ON ds.user_id = u.id AND ds.date BETWEEN :start_date AND :end_date
    WHERE u.role IN ('sales', 'salesadmin') 
      AND (u.active = 1 
           OR (u.active = 0 
               AND (u.deactivated_at IS NULL 
                    OR MONTH(u.deactivated_at) = MONTH(:current_date))))
    GROUP BY u.id, u.name, u.role";

$stmt = $conn->prepare($salesQuery);
$stmt->bindParam(':start_date', $startDate);
$stmt->bindParam(':end_date', $endDate);
$stmt->bindParam(':current_date', $currentDate);
$stmt->execute();
$allSalesData = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Separate the data into sales users and sales admins
    foreach ($allSalesData as $user) {
        if ($user['role'] === 'salesadmin') {
            $salesAdminsData[] = $user;
        } else {
            $salesUsersData[] = $user;
        }
    }

    // Calculate the combined monthly target
    $monthlyTargetQuery = "
        SELECT COALESCE(SUM(monthly_target), 0) AS combined_monthly_target
        FROM monthly_targets
        WHERE month = :current_month";
    
    $monthlyTargetStmt = $conn->prepare($monthlyTargetQuery);
    $monthlyTargetStmt->bindParam(':current_month', $current_month);
    $monthlyTargetStmt->execute();
    $monthlyTargetData = $monthlyTargetStmt->fetch(PDO::FETCH_ASSOC);

    // Debugging output
    if ($monthlyTargetData === false) {
        echo "Query failed: " . print_r($monthlyTargetStmt->errorInfo(), true);
    } else {
        $combinedMonthlyTarget = $monthlyTargetData['combined_monthly_target'] ?? 0;
    }

    // Calculate the percentage of sales against the monthly target
    if ($combinedMonthlyTarget > 0) {
        $salesPercentage = ($monthlySales / $combinedMonthlyTarget) * 100;
        $salesPercentage = round($salesPercentage, 2); // Round to two decimal places
    } else {
        $salesPercentage = 0;
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close the database connection
$conn = null;
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="#" />
   
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="title" content="Ask online Form" />

    <meta name="robots" content="index, nofollow" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="English" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

    <!-- Cache-Control Meta Tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>WhatsMyScore</title>
    <link rel="icon" type="image/png" href="wms_icon.png">
    <link
      rel="stylesheet"
      type="text/css"
      href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons"
    />
    <link rel="stylesheet" href="./css/fontawesome-all.min.css" />
    <link href="./css/materil.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/responsive.css" rel="stylesheet" />
    <link href="./css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
<script src="script.js?v=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="style.css?v=1.0.1">
<script src="script.js?v=1.0.1"></script>

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
    '
    <div class="main-container">

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
        <div class="sidebar-wrapper" style="top: 10px;">
         <ul class="nav">
            <li class="nav-item active">
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
              <a class="navbar-brand" href="javascript:;">Dashboard</a>
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
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="custom-icon-card card card-stats">
                  <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                      <img src="./img/wifi.png" class="icon" alt="building" />
                    </div>
                    <p class="card-category">Total Sales To Date</p>
                    <h3 class="card-title"><?php echo $totalSales; ?></h3>
                  </div>

                  <div class="card-body">
                    <div class="stats">
                      <div class="progress" style="height: 4px">
                        <div
                          class="progress-bar bg-d-flex"
                          role="progressbar"
                          style="width: 65%"
                          aria-valuenow="65"
                          aria-valuemin="0"
                          aria-valuemax="100"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="custom-icon-card card card-stats">
                  <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                      <img src="./img/calendar.png" class="icon" alt="building" />
                    </div>
                    <p class="card-category">Monthly Sales For <?php echo $currentMonthName; ?></p>
                    <h3 class="card-title"><?php echo $monthlySales; ?></h3>
                  </div>
                  <div class="card-body">
                    <div class="stats">
                      <div class="progress" style="height: 4px">
                        <div
                          class="progress-bar bg-d-flex"
                          role="progressbar"
                          style="width: 65%"
                          aria-valuenow="65"
                          aria-valuemin="0"
                          aria-valuemax="100"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="custom-icon-card card card-stats">
                  <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                      <img src="./img/group.png" class="icon" alt="building" />
                    </div>
                    <p class="card-category">Active Sales Reps</p>
                    <h3 class="card-title"><?php echo $allUsers ?></h3>
                  </div>
                  <div class="card-body">
                    <div class="stats">
                      <div class="progress" style="height: 4px">
                        <div
                          class="progress-bar bg-d-flex"
                          role="progressbar"
                          style="width: 65%"
                          aria-valuenow="65"
                          aria-valuemin="0"
                          aria-valuemax="100"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="custom-icon-card card card-stats">
                  <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                      <img src="./img/user.png" class="icon" alt="building" />
                    </div>
                    <p class="card-category">Total Users</p>
                    <h3 class="card-title"><?php echo $totalUsers; ?></h3>
                  </div>
                  <div class="card-body">
                    <div class="stats">
                      <div class="progress" style="height: 4px">
                        <div
                          class="progress-bar bg-d-flex"
                          role="progressbar"
                          style="width: 65%"
                          aria-valuenow="65"
                          aria-valuemin="0"
                          aria-valuemax="100"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8 col-md-12">
                <div class="card">
                  <div
                    class="card-header card-header-primary custom-card-height"
                  >
                    <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                          wifi </span
                        ><span></span></a
                      >
                    </div>
                    <div class="text-left"><br>
                        <div class="date-inputs">
                      Start Date: <input type="date" id="startDate" name="startDate" value="<?php echo date('Y-m-01'); ?>">
    End Date: <input type="date" id="endDate" name="endDate" value="<?php echo date('Y-m-d'); ?>">
  </div>
    <button  class="filterbtn" onclick="filterResults()">Filter</button><br><br><br><br>

                      <p class="card-category">6</p>
                    </div>
                  </div>
                  <div class="card-body table-responsive"><br><br>
                    <center><h4><b>Sales</b></h4></center>
        <table class="table table-hover">
            <thead class="text-primary">
                <tr>
                    <th>Name</th>
                    <th>Sales</th>
                    <th>Target</th>
                    
                </tr>
            </thead>
            <tbody id="salesTableBody">
                <?php if (empty($salesUsersData)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No data available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salesUsersData as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['sales']); ?></td>
                            <td><?php echo htmlspecialchars($user['target']); ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>



    <div class="container mt-4">
       <center><h4><b>Sales Admin</b></h4></center>
        <table class="table table-striped">
            <thead class="text-primary">
                <tr>
                    <th>Name</th>
                    <th>Sales</th>
                    <th>Target</th>
                    
                </tr>
            </thead>
            <tbody id="adminsTableBody">
                <?php if (empty($salesAdminsData)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No data available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salesAdminsData as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['target']); ?></td>
                            <td><?php echo htmlspecialchars($user['sales']); ?></td>
                            
                                                        
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-12">
                <div class="custom-account-card card">
                  <div
                    class="card-header card-header-primary custom-card-height"
                  >
                    <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                          person </span
                        ><?php echo $currentMonthName; ?> Monthly Progress</a
                      >
                    </div>
                  </div><br>
                  <center><span><b><?php echo $monthlySales; ?></b></span></center>
                  <div class="card-body">
                    <div
                      class="progress custom-progress mx-auto"
                      data-value="<?php echo $salesPercentage; ?>"
                    >
                      <span class="progress-left">
                        <span
                          class="
                            progress-bar
                            account-progress-bar
                            <?php echo $salesPercentage >= 100 ? 'border-success bg-success' : 'border-info bg-gray-white'; ?>
                          "
                        ></span>
                      </span>
                      <span class="progress-right">
                        <span
                          class="
                            progress-bar
                            account-progress-bar
                            border-info
                            bg-gray-white
                          "
                        ></span>
                      </span>
                      <div
                        class="
                          progress-value
                          w-100
                          h-100
                          rounded-circle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                      >
                        <div class="h5 font-weight-bold"><?php echo $salesPercentage; ?><sup class="small">
                          %</sup></div>
                      </div>
                    </div>
                    <!-- Demo info -->
                    <div class="row text-center mt-4">
                      <div class="col-6 border-right">
                        <div class="p mb-0 bullet">Monthly Target</div>
                        <span class="small text-gray"><?php echo $combinedMonthlyTarget; ?></span>
                      </div>
                      <div class="col-6">
                        <div class="p mb-0 bullet">Rejected Sales</div>
                        <span class="small text-gray"><?php echo $monthlyRejects; ?></span>
                      </div>
                    </div>
                    <!-- END -->

                  </div>

                </div>
                <form id="exportForm" method="POST" action="export.php" style="display: none;">
    <input type="hidden" name="start_date" id="exportStartDate">
    <input type="hidden" name="end_date" id="exportEndDate">
</form>
                <button id="exportButton" onclick="exportData()">Export</button>
              </div>
            </div>

            
          </div>
        </div>
      </div>
    </div>
  </div>

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
    <!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

<!-- Bootstrap Datepicker JS -->


<script>
function exportData() {
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    // Set start and end dates in the form
    document.getElementById('exportStartDate').value = startDate;
    document.getElementById('exportEndDate').value = endDate;

    // Submit the export form
    document.getElementById('exportForm').submit();
}

 function filterResults() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    fetch(`fetch_sales.php?startDate=${startDate}&endDate=${endDate}`)
        .then(response => response.json())
        .then(data => {
            const salesTableBody = document.getElementById('salesTableBody');
            const adminsTableBody = document.getElementById('adminsTableBody');

            // Clear previous data
            salesTableBody.innerHTML = '';
            adminsTableBody.innerHTML = '';

            // Handle sales users data
            if (data.sales.length === 0) {
                salesTableBody.innerHTML = '<tr><td colspan="3">No data available</td></tr>';
            } else {
                data.sales.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.name}</td>
                        <td>${row.sales}</td>
                        <td>${row.target}</td>
                        
                    `;
                    salesTableBody.appendChild(tr);
                });
            }

            // Handle sales admins data
            if (data.admins.length === 0) {
                adminsTableBody.innerHTML = '<tr><td colspan="3">No data available</td></tr>';
            } else {
                data.admins.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.name}</td>
                        <td>${row.sales}</td>
                        <td>${row.target}</td>
                    `;
                    adminsTableBody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error fetching sales data:', error));
}

// Load default data on page load
 document.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 2).toISOString().split('T')[0];
        const todayStr = today.toISOString().split('T')[0];
        document.getElementById('startDate').value = firstDayOfMonth;
        document.getElementById('endDate').value = todayStr;
        filterResults();
    });



</script>


    <script>

    

      $(document).ready(function () {
        $().ready(function () {
          $sidebar = $(".sidebar");

          $sidebar_img_container = $sidebar.find(".sidebar-background");

          $full_page = $(".full-page");

          $sidebar_responsive = $("body > .navbar-collapse");

          window_width = $(window).width();

          fixed_plugin_open = $(
            ".sidebar .sidebar-wrapper .nav li.active a p"
          ).html();

          if (window_width > 767 && fixed_plugin_open == "Dashboard") {
            if ($(".fixed-plugin .dropdown").hasClass("show-dropdown")) {
              $(".fixed-plugin .dropdown").addClass("open");
            }
          }

          $(".fixed-plugin a").click(function (event) {
            if ($(this).hasClass("switch-trigger")) {
              if (event.stopPropagation) {
                event.stopPropagation();
              } else if (window.event) {
                window.event.cancelBubble = true;
              }
            }
          });

          $(".fixed-plugin .active-color span").click(function () {
            $full_page_background = $(".full-page-background");

            $(this).siblings().removeClass("active");
            $(this).addClass("active");

            var new_color = $(this).data("color");

            if ($sidebar.length != 0) {
              $sidebar.attr("data-color", new_color);
            }

            if ($full_page.length != 0) {
              $full_page.attr("filter-color", new_color);
            }

            if ($sidebar_responsive.length != 0) {
              $sidebar_responsive.attr("data-color", new_color);
            }
          });

          $(".fixed-plugin .background-color .badge").click(function () {
            $(this).siblings().removeClass("active");
            $(this).addClass("active");

            var new_color = $(this).data("background-color");

            if ($sidebar.length != 0) {
              $sidebar.attr("data-background-color", new_color);
            }
          });

          $(".fixed-plugin .img-holder").click(function () {
            $full_page_background = $(".full-page-background");

            $(this).parent("li").siblings().removeClass("active");
            $(this).parent("li").addClass("active");

            var new_image = $(this).find("img").attr("src");

            if (
              $sidebar_img_container.length != 0 &&
              $(".switch-sidebar-image input:checked").length != 0
            ) {
              $sidebar_img_container.fadeOut("fast", function () {
                $sidebar_img_container.css(
                  "background-image",
                  'url("' + new_image + '")'
                );
                $sidebar_img_container.fadeIn("fast");
              });
            }

            if (
              $full_page_background.length != 0 &&
              $(".switch-sidebar-image input:checked").length != 0
            ) {
              var new_image_full_page = $(".fixed-plugin li.active .img-holder")
                .find("img")
                .data("src");

              $full_page_background.fadeOut("fast", function () {
                $full_page_background.css(
                  "background-image",
                  'url("' + new_image_full_page + '")'
                );
                $full_page_background.fadeIn("fast");
              });
            }

            if ($(".switch-sidebar-image input:checked").length == 0) {
              var new_image = $(".fixed-plugin li.active .img-holder")
                .find("img")
                .attr("src");
              var new_image_full_page = $(".fixed-plugin li.active .img-holder")
                .find("img")
                .data("src");

              $sidebar_img_container.css(
                "background-image",
                'url("' + new_image + '")'
              );
              $full_page_background.css(
                "background-image",
                'url("' + new_image_full_page + '")'
              );
            }

            if ($sidebar_responsive.length != 0) {
              $sidebar_responsive.css(
                "background-image",
                'url("' + new_image + '")'
              );
            }
          });

          $(".switch-sidebar-image input").change(function () {
            $full_page_background = $(".full-page-background");

            $input = $(this);

            if ($input.is(":checked")) {
              if ($sidebar_img_container.length != 0) {
                $sidebar_img_container.fadeIn("fast");
                $sidebar.attr("data-image", "#");
              }

              if ($full_page_background.length != 0) {
                $full_page_background.fadeIn("fast");
                $full_page.attr("data-image", "#");
              }

              background_image = true;
            } else {
              if ($sidebar_img_container.length != 0) {
                $sidebar.removeAttr("data-image");
                $sidebar_img_container.fadeOut("fast");
              }

              if ($full_page_background.length != 0) {
                $full_page.removeAttr("data-image", "#");
                $full_page_background.fadeOut("fast");
              }

              background_image = false;
            }
          });

          $(".switch-sidebar-mini input").change(function () {
            $body = $("body");

            $input = $(this);

            if (md.misc.sidebar_mini_active == true) {
              $("body").removeClass("sidebar-mini");
              md.misc.sidebar_mini_active = false;

              $(".sidebar .sidebar-wrapper, .main-panel").perfectScrollbar();
            } else {
              $(".sidebar .sidebar-wrapper, .main-panel").perfectScrollbar(
                "destroy"
              );

              setTimeout(function () {
                $("body").addClass("sidebar-mini");

                md.misc.sidebar_mini_active = true;
              }, 300);
            }

            var simulateWindowResize = setInterval(function () {
              window.dispatchEvent(new Event("resize"));
            }, 180);

            setTimeout(function () {
              clearInterval(simulateWindowResize);
            }, 1000);
          });
        });
      });
    </script>
  </body>
</html>

