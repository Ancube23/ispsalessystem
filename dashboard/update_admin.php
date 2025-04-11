<?php
session_start();
require '../db.php';

// Add cache-control headers to ensure the latest data is loaded
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Check if user is logged in and is a sales admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $all_fields_filled = true;
    $sales_data = $_POST['sales_data'];
    $current_month = date('Y-m-01');


    foreach ($sales_data as $user_id => $sales_data_item) {
        if (
            !isset($sales_data_item['active_leads']) || 
            !isset($sales_data_item['active_quotes']) || 
            !isset($sales_data_item['awaiting_docs']) || 
            !isset($sales_data_item['daily_target']) || 
            !isset($sales_data_item['daily_sale']) ||
            empty($user_id) // Validate user_id
        ) {
            $all_fields_filled = false;
            break;
        }
    }

    if ($all_fields_filled) {
        foreach ($sales_data as $user_id => $sales_data_item) {
            $daily_sale = isset($sales_data_item['daily_sale']) ? $sales_data_item['daily_sale'] : 0;
            $daily_target = isset($sales_data_item['daily_target']) ? $sales_data_item['daily_target'] : 0;
            $active_leads = isset($sales_data_item['active_leads']) ? $sales_data_item['active_leads'] : 0;
            $active_quotes = isset($sales_data_item['active_quotes']) ? $sales_data_item['active_quotes'] : 0;
            $awaiting_docs = isset($sales_data_item['awaiting_docs']) ? $sales_data_item['awaiting_docs'] : 0;
            $monthly_target = $sales_data_item['monthly_target'] ?? null;

            // Update or insert daily sales data
            $existing_data_stmt = $conn->prepare("SELECT * FROM daily_sales WHERE user_id = :sales_user_id AND date = :date");
            $existing_data_stmt->execute(['sales_user_id' => $user_id, 'date' => $date]);
            $existing_data = $existing_data_stmt->fetch();

            if ($existing_data) {
                $sql = "UPDATE daily_sales SET sales = :daily_sale, target = :daily_target, active_leads = :active_leads, active_quotes = :active_quotes, awaiting_docs = :awaiting_docs WHERE user_id = :sales_user_id AND date = :date";
            } else {
                $sql = "INSERT INTO daily_sales (user_id, sales, target, active_leads, active_quotes, awaiting_docs, date) VALUES (:sales_user_id, :daily_sale, :daily_target, :active_leads, :active_quotes, :awaiting_docs, :date)";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':sales_user_id', $user_id);
            $stmt->bindParam(':daily_sale', $daily_sale, PDO::PARAM_INT);
            $stmt->bindParam(':daily_target', $daily_target, PDO::PARAM_INT);
            $stmt->bindParam(':active_leads', $active_leads, PDO::PARAM_INT);
            $stmt->bindParam(':active_quotes', $active_quotes, PDO::PARAM_INT);
            $stmt->bindParam(':awaiting_docs', $awaiting_docs, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);

            if ($stmt->execute()) {
                // Update or insert monthly target data
                if ($monthly_target !== null) {
                    $monthly_target_stmt = $conn->prepare("SELECT * FROM monthly_targets WHERE user_id = :sales_user_id AND month = :current_month");
                    $monthly_target_stmt->execute(['sales_user_id' => $user_id, 'current_month' => $current_month]);
                    $monthly_target_data = $monthly_target_stmt->fetch();

                    if ($monthly_target_data) {
                        $monthly_target_sql = "UPDATE monthly_targets SET monthly_target = :monthly_target WHERE user_id = :sales_user_id AND month = :current_month";
                    } else {
                        $monthly_target_sql = "INSERT INTO monthly_targets (user_id, month, monthly_target) VALUES (:sales_user_id, :current_month, :monthly_target)";
                    }

                    $monthly_target_stmt = $conn->prepare($monthly_target_sql);
                    $monthly_target_stmt->bindParam(':sales_user_id', $user_id);
                    $monthly_target_stmt->bindParam(':current_month', $current_month);
                    $monthly_target_stmt->bindParam(':monthly_target', $monthly_target);

                    if ($monthly_target_stmt->execute()) {
                        $success = "Sales data and monthly target updated successfully";
                    } else {
                        $error = "Failed to update monthly target";
                    }
                }
            } else {
                $error = "Failed to update sales data";
            }
        }
    } else {
        $error = "All fields must be filled for each user";
    }
}

// Default role is "salesadmin" if not already set
$selected_role = isset($_GET['role']) ? $_GET['role'] : 'salesadmin';

// Fetch sales admin users and their monthly targets for the current month, ordered alphabetically by name
$current_month = date('Y-m-01');
$sales_admin_stmt = $conn->prepare("
    SELECT u.id, u.name, COALESCE(mt.monthly_target, 0) AS monthly_target
    FROM users u
    LEFT JOIN monthly_targets mt ON u.id = mt.user_id AND mt.month = :current_month
    WHERE u.role = :selected_role
      AND (
          u.active = 1 
          OR (u.active = 0 AND MONTH(u.deactivated_at) = MONTH(:current_month) AND YEAR(u.deactivated_at) = YEAR(:current_month))
      )
    ORDER BY u.name ASC
");
$sales_admin_stmt->execute(['current_month' => $current_month, 'selected_role' => $selected_role]);
$sales_admins = $sales_admin_stmt->fetchAll(PDO::FETCH_ASSOC);
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
             <li class="nav-item active">
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
                        ><span>Update Sales</span></a
                      >
                    </div>


            </div>
            <div class="card-body">
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="date" class="label-input">Date</label>
            <input type="date" name="date" id="date" class="form-control2 date-input" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
        <label for="role_select" class="label-input">Role</label>
        <select id="role-dropdown" name="role" class="">
            <option value="sales" <?php echo ($selected_role === 'sales') ? 'selected' : ''; ?>>Sales</option>
            <option value="salesadmin" <?php echo ($selected_role === 'salesadmin') ? 'selected' : ''; ?>>Sales Admin</option>
        </select>
    </div>

        <table class="table table-bordered" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Name</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Active Leads</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Active Quotes</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Awaiting Docs</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Monthly Target</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Current Monthly Sale</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Daily Target</th>
                    <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Daily Sale</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_admins as $admin): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 2px;"><?php echo $admin['name']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][active_leads]" class="form-control" value="0" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][active_quotes]" class="form-control" value="0" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][awaiting_docs]" class="form-control" value="0" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][monthly_target]" class="form-control" value="<?php echo $admin['monthly_target']; ?>" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][current_monthly_sale]" class="form-control" value="0" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][daily_target]" class="form-control" value="0" required></td>
                        <td style="border: 1px solid #ddd; padding: 2px;"><input type="number" name="sales_data[<?php echo $admin['id']; ?>][daily_sale]" class="form-control" value="0" required></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Update Sales</button>
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

       document.getElementById('role-dropdown').addEventListener('change', function() {
    const selectedRole = this.value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('role', selectedRole);
    if (selectedRole === 'sales') {
        window.location.href = 'update_sales.php?' + currentUrl.searchParams.toString();
    } else {
        window.location.href = currentUrl.toString();
    }
});

       document.write(new Date().getFullYear())

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
