<?php
session_start();
require '../db.php';

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Default start and end dates (today)
$startDate = date("Y-m-d");
$endDate = date("Y-m-d");

// Fetch users with the role "salesadmin" only
$usersQuery = "SELECT id, name FROM users WHERE role = 'salesadmin' ORDER BY name";
$usersStmt = $conn->prepare($usersQuery);
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize $selectedUser as a single value by default
$selectedUser = 'all';

// Initialize $salesData
$salesData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedUser = $_POST['userId'] ?? 'all';
    $startDate = $_POST['startDate'] ?? $startDate;
    $endDate = $_POST['endDate'] ?? $endDate;

    // Set query and parameters based on selected user
    $params = [
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':currentMonthStart' => date("Y-m-01"),
        ':currentDate' => date("Y-m-d")
    ];

    // Fetch sales data for the selected user or all users (only "salesadmin" role users)
    if ($selectedUser !== 'all') {
        $salesQuery = "SELECT u.name, 
                              SUM(ds.sales) AS total_sales, 
                              SUM(ds.target) AS total_target, 
                              SUM(ds.active_leads) AS active_leads, 
                              SUM(ds.active_quotes) AS active_quotes, 
                              SUM(ds.awaiting_docs) AS awaiting_docs, 
                              mt.monthly_target,
                              (SELECT SUM(ds2.sales)
                               FROM daily_sales ds2
                               WHERE ds2.user_id = u.id
                               AND ds2.date BETWEEN :currentMonthStart AND :currentDate) AS current_monthly_sale
                       FROM daily_sales ds
                       INNER JOIN users u ON ds.user_id = u.id
                       LEFT JOIN (
                           SELECT user_id, monthly_target 
                           FROM monthly_targets 
                           WHERE month = :currentMonthStart
                       ) AS mt ON ds.user_id = mt.user_id
                       WHERE ds.user_id = :selectedUser
                       AND ds.date BETWEEN :startDate AND :endDate
                       AND u.role = 'salesadmin'  -- Filter only salesadmin users
                       AND (
                           u.active = 1 
                           OR (u.active = 0 AND u.deactivated_at >= :currentMonthStart AND u.deactivated_at < DATE_ADD(:currentMonthStart, INTERVAL 1 MONTH))
                       )
                       GROUP BY u.id, u.name
                       ORDER BY u.name";
        $params[':selectedUser'] = $selectedUser;
    } else {
        $salesQuery = "SELECT u.id,
                              u.name, 
                              SUM(ds.sales) AS total_sales, 
                              SUM(ds.target) AS total_target, 
                              SUM(ds.active_leads) AS active_leads, 
                              SUM(ds.active_quotes) AS active_quotes, 
                              SUM(ds.awaiting_docs) AS awaiting_docs, 
                              mt.monthly_target,
                              (SELECT SUM(ds2.sales)
                               FROM daily_sales ds2
                               WHERE ds2.user_id = u.id
                               AND ds2.date BETWEEN :currentMonthStart AND :currentDate) AS current_monthly_sale
                       FROM daily_sales ds
                       INNER JOIN users u ON ds.user_id = u.id
                       LEFT JOIN (
                           SELECT user_id, monthly_target 
                           FROM monthly_targets 
                           WHERE month = :currentMonthStart
                       ) AS mt ON ds.user_id = mt.user_id
                       WHERE ds.date BETWEEN :startDate AND :endDate
                       AND u.role = 'salesadmin'  -- Filter only salesadmin users
                       AND (
                           u.active = 1 
                           OR (u.active = 0 AND u.deactivated_at >= :currentMonthStart AND u.deactivated_at < DATE_ADD(:currentMonthStart, INTERVAL 1 MONTH))
                       )
                       GROUP BY u.id, u.name
                       ORDER BY u.name";
    }

    $salesStmt = $conn->prepare($salesQuery);
    $salesStmt->execute($params);
    $salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize variables to hold the totals
$totalSales = 0;
$totalTargets = 0;
$totalActiveLeads = 0;
$totalActiveQuotes = 0;
$totalAwaitingDocs = 0;
$totalMonthlyTargets = 0;
$totalCurrentMonthlySales = 0;

if (!empty($salesData)) {
    foreach ($salesData as $row) {
        // Accumulate totals for each column
        $totalSales += $row['total_sales'];
        $totalTargets += $row['total_target'];
        $totalActiveLeads += $row['active_leads'];
        $totalActiveQuotes += $row['active_quotes'];
        $totalAwaitingDocs += $row['awaiting_docs'];
        $totalMonthlyTargets += $row['monthly_target'];
        $totalCurrentMonthlySales += $row['current_monthly_sale'];
    }
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
            <li class="nav-item active">
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
                    <div class="card-header card-header-primary">
                        <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                          description </span
                        ><span>Sales Report</span></a
                      >
                    </div>

                         <div class="float-right">
                <div class="user-profile-nav">
                  <div class="btn-add-group">
   <form id="export-form" action="export_report.php" method="post" style="display:inline;">
    <input type="hidden" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
    <input type="hidden" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
    <?php if (is_array($selectedUser)): ?>
        <?php foreach ($selectedUser as $userId): ?>
            <input type="hidden" name="userIds[]" value="<?= htmlspecialchars($userId) ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <!-- If $selectedUser is a single string value -->
        <?php if ($selectedUser !== 'all'): ?>
            <input type="hidden" name="userIds[]" value="<?= htmlspecialchars($selectedUser) ?>">
        <?php endif; ?>
    <?php endif; ?>
    <button type="submit" class="btn btn-white">
        <span class="material-icons" style="color: #dc3545; font-size: 24px; margin-right: 8px;">description</span>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Export
    </button>
</form>
    <input type="hidden" name="filter-search" id="filter-search">
</div>
</div>
</div>
</div>
                    <div class="card-body">
                       <form id="userFilterForm" method="post">
    <label for="startDate">Start Date:</label>
    <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
    <label for="endDate">End Date:</label>
    <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
    <label for="userId">Select User:</label>
    <select id="userId" name="userId">
        <option value="all" <?php echo $selectedUser == 'all' ? 'selected' : ''; ?>>All Users</option>
        <?php foreach ($users as $user): ?>
            <option value="<?php echo $user['id']; ?>" <?php echo $selectedUser == $user['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['name']); ?></option>
        <?php endforeach; ?>
    </select>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img id="refreshImage" src="recycling-symbol.png" alt="Refresh" style="cursor: pointer; width: 30px; height: 30px;" />
   <a href="sales_report4.php" class="btn btn-primary" style="width: 13%; padding: 6px 12px;">Sales</a>
</form>
                                    <br>
                                    <div class="table-responsive">
                                       <table class="table">
    <thead class=" text-primary">
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Name</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Daily Sales</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Daily Target</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Active Leads</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Active Quotes</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Awaiting Docs</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Monthly Target</th>
        <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Current Monthly Sales</th>
    </thead>
    <tbody>
        <?php if (!empty($salesData)): ?>
            <?php 
            // Initialize an array to keep track of displayed user IDs
            $displayedUserIds = [];
            ?>
            <?php foreach ($salesData as $row): ?>
                <?php if (!in_array($row['id'], $displayedUserIds)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_sales']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_target']); ?></td>
                        <td><?php echo htmlspecialchars($row['active_leads']); ?></td>
                        <td><?php echo htmlspecialchars($row['active_quotes']); ?></td>
                        <td><?php echo htmlspecialchars($row['awaiting_docs']); ?></td>
                        <td><?php echo htmlspecialchars($row['monthly_target']); ?></td>
                        <td><?php echo htmlspecialchars($row['current_monthly_sale']); ?></td>
                    </tr>
                    <?php 
                    // Add the user ID to the displayed array to prevent duplicates
                    $displayedUserIds[] = $row['id']; 
                    ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No data available for the selected criteria.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

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

    <script>
      $(document).ready(function() {
    // Handle form submission on image click
    $('#refreshImage').click(function() {
        $('#userFilterForm').submit();
    });

    // Optional: Handle form submission on user selection change
    $('#userId').change(function() {
        $('#userFilterForm').submit();
    });
});


     $(document).ready(function() {
            // Handle form submission on user selection change
            $('#userId').change(function() {
                $('#userFilterForm').submit();
            });
        });



    document.getElementById('export-form').addEventListener('submit', function() {
    // Copy filter values to hidden fields in the export form
    document.getElementById('filter-search').value = document.querySelector('[name="search"]').value;
    // Repeat for other filters as needed
});

</script>


    <script>
        $(document).ready(function() {
            $().ready(function() {
                $sidebar = $('.sidebar');

                $sidebar_img_container = $sidebar.find('.sidebar-background');

                $full_page = $('.full-page');

                $sidebar_responsive = $('body > .navbar-collapse');

                window_width = $(window).width();

                fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();

                if (window_width > 767 && fixed_plugin_open == 'Dashboard') {
                    if ($('.fixed-plugin .dropdown').hasClass('show-dropdown')) {
                        $('.fixed-plugin .dropdown').addClass('open');
                    }

                }

                $('.fixed-plugin a').click(function(event) {
                    // Alex if we click on switch, stop propagation of the event, so the dropdown will not be hide,
                    if ($(this).hasClass('switch-trigger')) {
                        if (event.stopPropagation) {
                            event.stopPropagation();
                        } else if (window.event) {
                            window.event.cancelBubble = true;
                        }
                    }
                });

                $('.fixed-plugin .active-color span').click(function() {
                    $full_page_background = $('.full-page-background');

                    $(this).siblings().removeClass('active');
                    $(this).addClass('active');

                    var new_color = $(this).data('color');

                    if ($sidebar.length != 0) {
                        $sidebar.attr('data-color', new_color);
                    }

                    if ($full_page.length != 0) {
                        $full_page.attr('filter-color', new_color);
                    }

                    if ($sidebar_responsive.length != 0) {
                        $sidebar_responsive.attr('data-color', new_color);
                    }
                });

                $('.fixed-plugin .background-color .badge').click(function() {
                    $(this).siblings().removeClass('active');
                    $(this).addClass('active');

                    var new_color = $(this).data('background-color');

                    if ($sidebar.length != 0) {
                        $sidebar.attr('data-background-color', new_color);
                    }
                });

                $('.fixed-plugin .img-holder').click(function() {
                    $full_page_background = $('.full-page-background');

                    $(this).parent('li').siblings().removeClass('active');
                    $(this).parent('li').addClass('active');


                    var new_image = $(this).find("img").attr('src');

                    if ($sidebar_img_container.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
                        $sidebar_img_container.fadeOut('fast', function() {
                            $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
                            $sidebar_img_container.fadeIn('fast');
                        });
                    }

                    if ($full_page_background.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
                        var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

                        $full_page_background.fadeOut('fast', function() {
                            $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
                            $full_page_background.fadeIn('fast');
                        });
                    }

                    if ($('.switch-sidebar-image input:checked').length == 0) {
                        var new_image = $('.fixed-plugin li.active .img-holder').find("img").attr('src');
                        var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

                        $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
                        $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
                    }

                    if ($sidebar_responsive.length != 0) {
                        $sidebar_responsive.css('background-image', 'url("' + new_image + '")');
                    }
                });

                $('.switch-sidebar-image input').change(function() {
                    $full_page_background = $('.full-page-background');

                    $input = $(this);

                    if ($input.is(':checked')) {
                        if ($sidebar_img_container.length != 0) {
                            $sidebar_img_container.fadeIn('fast');
                            $sidebar.attr('data-image', '#');
                        }

                        if ($full_page_background.length != 0) {
                            $full_page_background.fadeIn('fast');
                            $full_page.attr('data-image', '#');
                        }

                        background_image = true;
                    } else {
                        if ($sidebar_img_container.length != 0) {
                            $sidebar.removeAttr('data-image');
                            $sidebar_img_container.fadeOut('fast');
                        }

                        if ($full_page_background.length != 0) {
                            $full_page.removeAttr('data-image', '#');
                            $full_page_background.fadeOut('fast');
                        }

                        background_image = false;
                    }
                });

                $('.switch-sidebar-mini input').change(function() {
                    $body = $('body');

                    $input = $(this);

                    if (md.misc.sidebar_mini_active == true) {
                        $('body').removeClass('sidebar-mini');
                        md.misc.sidebar_mini_active = false;

                        $('.sidebar .sidebar-wrapper, .main-panel').perfectScrollbar();

                    } else {

                        $('.sidebar .sidebar-wrapper, .main-panel').perfectScrollbar('destroy');

                        setTimeout(function() {
                            $('body').addClass('sidebar-mini');

                            md.misc.sidebar_mini_active = true;
                        }, 300);
                    }

                    // we simulate the window Resize so the charts will get updated in realtime.
                    var simulateWindowResize = setInterval(function() {
                        window.dispatchEvent(new Event('resize'));
                    }, 180);

                    // we stop the simulation of Window Resize after the animations are completed
                    setTimeout(function() {
                        clearInterval(simulateWindowResize);
                    }, 1000);

                });
            });
        });
    </script>
  </body>
</html>
