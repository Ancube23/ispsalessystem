<?php
session_start();
require '../db.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Fetch sales and target data
    $query = "
        SELECT u.name, COALESCE(SUM(ds.sales), 0) AS sales, COALESCE(SUM(ds.target), 0) AS target
        FROM users u
        LEFT JOIN daily_sales ds ON ds.user_id = u.id
        WHERE u.role = 'sales'
        GROUP BY u.name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the email content
    $content = "<h1>Total Sales and Targets</h1><table><tr><th>Name</th><th>Sales</th><th>Target</th></tr>";
    foreach ($salesData as $data) {
        $content .= "<tr><td>{$data['name']}</td><td>{$data['sales']}</td><td>{$data['target']}</td></tr>";
    }
    $content .= "</table>";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'cms.synaq.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                
        $mail->Username   = 'support@redwill.co.za'; 
        $mail->Password   = 'rw#jmn853K7'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;                

        $mail->setFrom('support@redwill.co.za', 'WhatsYourScore');
        $mail->addAddress($email); 

        $mail->isHTML(true);
        $mail->Subject = 'Sales and Targets Report';
        $mail->Body    = $content;

        $mail->send();
        $message = "Email sent successfully.";
    } catch (Exception $e) {
        $message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

try {
    // Fetch all users with sales role
    $usersQuery = "SELECT id, name FROM users WHERE role = 'sales'";
    $usersStmt = $conn->prepare($usersQuery);
    $usersStmt->execute();
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total sales and total targets for the past 30 days by default
    $endDate = date("Y-m-d"); // Today's date
    $startDate = date("Y-m-01"); // First day of the current month

    // Fetch sales data for all users by default
    $salesQuery = "
        SELECT date, SUM(sales) as sales, SUM(target) as target
        FROM daily_sales
        WHERE date BETWEEN :start_date AND :end_date
        GROUP BY date
        ORDER BY date";

    $stmt = $conn->prepare($salesQuery);
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="./css/fontawesome-all.min.css">
    <link href="./css/materil.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/responsive.css" rel="stylesheet" />
    <link href="./css/style.css" rel="stylesheet" />
  </head>

  <body class="">

    <div class='light x1'></div>
    <div class='light x2'></div>
    <div class='light x3'></div>
    <div class='light x4'></div>
    <div class='light x5'></div>
    <div class='light x6'></div>
    <div class='light x7'></div>
    <div class='light x8'></div>
    <div class='light x9'></div>
    <div class="wrapper">
      <div
        class="sidebar"
        data-color="blue"
        data-background-color="white"
        data-image="../assets/img/sidebar-1.jpg"
      >
        <div class="logo">
          <a href="./index.php" class="simple-text logo-normal">
            <img src="./logo.png" alt="logo" style="margin-top: 40px; width: auto; height: 160px;" />
          </a>
        </div>
        <div class="sidebar-wrapper">
          <ul class="nav">
            <li class="nav-item">
              <a class="nav-link" href="./index.php">
                <img class="mr-2" src="./img/ic_view_quilt_24px.png" />
                Dashboard
              </a>
            </li>
            <li class="nav-item actie">
              <a class="nav-link" href="./admin.php">
                <img class="mr-2" src="./img/Group 1380.png" />
                Users
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./add_user.php">
                <img class="mr-2" src="./img/Group 1381.png" />
               Add Users
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./sales_report.php">
                <img class="mr-2" src="./img/Group 1380.png" />
                Sales Reports
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./statistics.php">
                <img class="mr-2" src="./img/Group 1380.png" />
                Statistics
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
                  <img class="mr-2" src="./img/Group 1382.png" />
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

        <br><br><br><br><br>
        <center><h2>Send Sales and Targets Report</h2><center>
    <form method="post" action="">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Email</button>
    </form>
    <?php if (isset($message)) echo "<p>$message</p>"; ?>

<div class="row">
    <div class="col-md-4 offset-md-3 custom-size">
        <div class="card">
            <div class="card-header bg">
                <h1>Statistics</h1>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="userSelect">Select User:</label>
                    <select class="form-control" id="userSelect">
                        <option value="all">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                            <!-- Add the "Filter" button -->
<button id="filterButton" class="btn btn-primary">Filter</button>
                <div id="dateRangePicker"></div>
                <canvas id="lineChart" width="400" height="200"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function () {
        var startDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1); // First day of the current month
        var endDate = new Date(); // Today's date

        var picker = new Pikaday({
            field: document.getElementById('dateRangePicker'),
            format: 'YYYY-MM-DD',
            onSelect: function () {
                startDate = picker.getStartDate();
                endDate = picker.getEndDate();
                fetchData(startDate, endDate);
            },
            defaultDate: [startDate, endDate],
            setDefaultDate: true,
            maxDate: new Date()
        });

        // Fetch data for line chart
        fetchData(startDate, endDate);

        function fetchData(startDate, endDate, userId = 'all') {
            var data = {
                start_date: startDate.toISOString().split('T')[0],
                end_date: endDate.toISOString().split('T')[0],
                user_id: userId
            };

            $.ajax({
                url: 'fetch_sales_data.php',
                method: 'POST',
                data: data,
                success: function (response) {
                    var salesData = JSON.parse(response);
                    if (salesData.error) {
                        console.error(salesData.error);
                        return;
                    }
                    renderLineChart(salesData);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        // Attach a click event to the "Filter" button
    $('#filterButton').on('click', function () {
        // Fetch the selected user ID from the dropdown
        var userId = $('#userSelect').val();
        // Fetch data for the selected user
        fetchData(startDate, endDate, userId);
    });

        $('#userSelect').on('change', function () {
            var userId = $(this).val();
            fetchData(startDate, endDate, userId);
        });

        function renderLineChart(salesData) {
            var ctx = document.getElementById('lineChart').getContext('2d');
            var myChart = new Chart(ctx, {
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
                            },
                            distribution: 'linear',
                            ticks: {
                                source: 'auto'
                            }
                        }]
                    }
                }
            });
        }
    });

</script>
  </body>
</html>
