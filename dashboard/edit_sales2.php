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

// Get the selected role from the dropdown or default to 'sales'
$selected_role = isset($_GET['role']) ? $_GET['role'] : 'sales';

// Handle form submission for updating sales data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sale_id = $_POST['sale_id'];
    $sales = $_POST['sales'];
    $target = $_POST['target'];
    $rejects = $_POST['rejects'];

    // Calculate the valid sales by subtracting rejects from sales
    $valid_sales = $sales - $rejects;

    $sql = "UPDATE daily_sales SET sales = :sales, target = :target, rejects = :rejects WHERE id = :sale_id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':sales', $valid_sales);
    $stmt->bindParam(':target', $target);
    $stmt->bindParam(':rejects', $rejects);
    $stmt->bindParam(':sale_id', $sale_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sales data updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update sales data']);
    }
}

// Fetch sales data for the selected date or today's date
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get the first day of the current month
$first_day_of_month = date('Y-m-01', strtotime($date));

$sales_stmt = $conn->prepare("
    SELECT ds.id, u.name, ds.sales, ds.target, ds.sale_type, ds.rejects 
    FROM daily_sales ds 
    JOIN users u ON ds.user_id = u.id 
    WHERE ds.date = :date 
    AND (
        u.active = 1 
        OR (u.active = 0 AND u.deactivated_at >= :first_day_of_month AND u.deactivated_at < DATE_ADD(:first_day_of_month, INTERVAL 1 MONTH))
    )
    AND u.role = :selected_role
    ORDER BY u.name ASC
");

$sales_stmt->bindParam(':date', $date);
$sales_stmt->bindParam(':first_day_of_month', $first_day_of_month);
$sales_stmt->bindParam(':selected_role', $selected_role);
$sales_stmt->execute();
$sales_data = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);
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

   <!-- <div class='light x1'></div>
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
            <li class="nav-item actie">
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
            <li class="nav-item active">
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
                </li>-->
              </ul>
            </div>
          </div>
        </nav>
        <!-- End Navbar -->
       <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header card-header-primary">

                        <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                         edit </span
                        ><span>Edit Sales</span></a
                      >
                    </div>

                    </div>
                    <div class="card-body">
                       <?php if (isset($success)) { echo "<p class='alert alert-success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='alert alert-danger'>$error</p>"; } ?>
                      
                        <form method="get" action="edit_sales.php">
            <div class="form-group">
                <label for="date">Select Date:</label>
                <input type="date" id="date" name="date" value="<?php echo $date; ?>" class="form-control2" onchange="this.form.submit()">
            </div>
        </form>

          <div class="form-group">
    <label for="role_select" class="label-input">Role</label>
    <select id="role-dropdown" name="role" class="">
        <option value="sales" <?php echo ($selected_role === 'sales') ? 'selected' : ''; ?>>Sales</option>
        <option value="salesadmin" <?php echo ($selected_role === 'salesadmin') ? 'selected' : ''; ?>>Sales Admin</option>
    </select>
</div>


      <table class="table">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Name</th>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Target</th>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Sales</th>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Sales Type</th>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Rejects</th>
            <th style="border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales_data as $sale) { ?>
            <tr data-sale-id="<?php echo $sale['id']; ?>">
                <td style="border: 1px solid #ddd; padding: 2px;"><?php echo $sale['name']; ?></td>
                <td style="border: 1px solid #ddd; padding: 2px;"><input type="text" name="target" value="<?php echo $sale['target']; ?>" readonly></td>
                <td style="border: 1px solid #ddd; padding: 2px;"><input type="text" name="sales" value="<?php echo $sale['sales']; ?>" readonly></td>
                <td style="border: 1px solid #ddd; padding: 2px;"><?php echo $sale['sale_type']; ?></td>
                <td style="border: 1px solid #ddd; padding: 2px;"><input type="text" name="rejects" value="<?php echo $sale['rejects']; ?>" readonly></td>
                <td style="border: 1px solid #ddd; padding: 2px;">
                    <center><button class="btn btn-primary edit-button"><center>Edit</center></button>
                    <button class="btn btn-success save-button" style="display:none;"><center>Save</center></button></center>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
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

    <?php
header('Content-Type: application/json');

// Your database update logic here
$success = false;

// Assume $result is the result of your database operation
if ($result) {
    $success = true;
}

echo json_encode(['success' => $success]);
?>


    <script>

      document.getElementById('role-dropdown').addEventListener('change', function() {
    const selectedRole = this.value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('role', selectedRole);
    window.location.href = currentUrl.toString();
});


   $(document).ready(function() {
    $('.edit-button').on('click', function() {
        var row = $(this).closest('tr');
        row.find('input').prop('readonly', false);
        row.find('.edit-button').hide();
        row.find('.save-button').show();
    });

    $('.save-button').on('click', function() {
        var row = $(this).closest('tr');
        var saleId = row.data('sale-id');
        var sales = row.find('input[name="sales"]').val();
        var target = row.find('input[name="target"]').val();
        var rejects = row.find('input[name="rejects"]').val();
        
        // Exclude sales_type from being sent in the AJAX request
        var originalSales = row.find('input[name="sales"]').prop('defaultValue');
        var originalTarget = row.find('input[name="target"]').prop('defaultValue');
        var originalRejects = row.find('input[name="rejects"]').prop('defaultValue');

        // Check if any changes have been made
        var changesMade = (sales !== originalSales || target !== originalTarget || rejects !== originalRejects);

        if (changesMade) {
            $.ajax({
                url: 'edit_sales.php',
                type: 'POST',
                data: {
                    sale_id: saleId,
                    sales: sales,
                    target: target,
                    rejects: rejects
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sales data updated successfully');
                        row.find('input').prop('readonly', true);
                        row.find('.edit-button').show();
                        row.find('.save-button').hide();
                    } else {
                        alert('Failed to update sales data');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', xhr.responseText, status, error);
                    alert('Updated Successfully');
                },
                complete: function() {
                    // Ensure the button states are correctly updated
                    row.find('input').prop('readonly', true);
                    row.find('.edit-button').show();
                    row.find('.save-button').hide();
                }
            });
        } else {
            // Revert fields to readonly and buttons to original state
            row.find('input').prop('readonly', true);
            row.find('.edit-button').show();
            row.find('.save-button').hide();
        }
    });
});



    </script>

    <script>

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
