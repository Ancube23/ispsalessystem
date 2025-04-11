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

// Fetch active user data with roles 'sales' or 'salesadmin'
$sql_active = "SELECT id, name, email, phone, role, created_at, updated_at 
               FROM users 
               WHERE active = 1 
               AND role IN ('sales', 'salesadmin') 
               ORDER BY name ASC";
$stmt_active = $conn->query($sql_active);
$active_users = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

// Fetch inactive user data
$sql_inactive = "SELECT id, name, email, phone, role, created_at, updated_at FROM users WHERE active = 0 ORDER BY name ASC";
$stmt_inactive = $conn->query($sql_inactive);
$inactive_users = $stmt_inactive->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['message'])) {
    echo '<p>' . htmlspecialchars($_GET['message']) . '</p>';
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
            <li class="nav-item active">
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

<br><br><br><br>
<div class="row">
              <div class="col-md-12">
                <div class="card custom-user-info-card">
                  <div class="card-header card-header-primary">
                    <div class="float-left">
                      <a
                        ><span class="material-icons custom-material-icon">
                          person </span
                        ><span>Active Users</span></a
                      >
                    </div>
                     <div class="float-right">
                <div class="user-profile-nav">

                  <center><form class="navbar-form">
  <div class="input-group custom-input border ">
    <input
      id="search_input"
      type="text"
      value=""
      class="form-control"
      placeholder="Search..."
    />
    <button
      id="search_button"
      type="submit"
      class="btn btn-danger btn-round btn-just-icon"
    >
      <i class="material-icons">search</i>
      <div class="ripple-container"></div>
    </button>
  </div>
</form></center>


<!--<form class="navbar-form">
                <div class="input-group custom-input no-border">
                  <div class="searchbar">
  <input
    id="search_input"
    class="search_input"
    type="text"
    placeholder="Search..."
  />
  <a href="#" id="search_icon" class="search_icon">
    <i class="material-icons">search</i>
  </a>
</div>
</div>
</form>-->

<div class="btn-add-group">
  <button type="submit" class="btn btn-white" onclick="location.href='add_user.php'">
    <span class="material-icons" style="color: #28a745; font-size: 24px; margin-right: 8px;">
      add_box
    </span>
    Add New
  </button>
  <form action="export_csv.php" method="post" style="display:inline;">
    <button type="submit" class="btn btn-white">
      <span class="material-icons" style="color: #dc3545; font-size: 24px; margin-right: 8px;">
        description
      </span>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Export
    </button>
  </form>
</div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body custom-user-table-data">
                    <div class="table-responsive">
                      <table class="table">
    <thead class="text-primary">
        <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Name</th>
        <th style="width: 12%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Email</th>
        <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Phone</th>
        <th style="width: 7%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Role</th>
        <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Created</th>
        <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Last Update</th>
        <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;" class="text-center">Action</th>
    </thead>
    <tbody>
        <?php if ($active_users): ?>
            <?php foreach ($active_users as $user): ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['name']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['role']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['updated_at']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 2px;" class="text-primary">
                        <div class="action-btn-group float-right d-flex">
                           <div style="display: flex; gap: 10px;">
    <button
        type="button"
        class="custom-action-btn btn btn-primary edit-btn"
        data-id="<?php echo htmlspecialchars($user['id']); ?>"
        data-name="<?php echo htmlspecialchars($user['name']); ?>"
        data-email="<?php echo htmlspecialchars($user['email']); ?>"
        data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
        data-role="<?php echo htmlspecialchars($user['role']); ?>"
        style="width: 90px; height: 38px;" 
    >
        Edit
    </button>
    <button
        type="button"
        class="custom-action-btn btn btn-danger deactivate-btn"
        data-id="<?php echo htmlspecialchars($user['id']); ?>"
        style="width: 90px; height: 38px;" 
    >
        Deactivate
    </button>
</div>

                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No users found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

                      <!-- Edit User Modal -->
<div id="editUserModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="userId">
                    <div class="form-group">
        <label for="updateName">Name</label> <!-- New field for name -->
        <input type="text" class="form-control" id="updateName" name="updateName" required> <!-- Added required -->
    </div>
                    <div class="form-group">
                        <label for="updateEmail">Email</label>
                        <input type="email" class="form-control" id="updateEmail" name="updateEmail" required>
                    </div>
                    <div class="form-group">
                            <label for="editUserPhone">Phone</label>
                            <input type="text" class="form-control" id="updatePhone" name="updatePhone">
                        </div>
                        <div class="form-group">
    <label for="updateRole">Role</label>
    <select class="form-control" id="updateRole" name="updateRole" required>
        <option value="admin">Admin</option>
        <option value="salesadmin">Sales Admin</option>
        <option value="sales">Sales</option>
    </select>
</div>
                    <div class="form-group">
                        <label for="updatePassword">Password</label>
                        <input type="text" class="form-control" id="updatePassword" name="updatePassword">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
  <div class="col-md-12">
    <div class="card custom-user-info-card">
      <div class="card-header card-header-primary">
        <div class="float-left">
          <a><span class="material-icons custom-material-icon">person</span><span>Inactive Users</span></a>
        </div>
      </div>
      <div class="card-body custom-user-table-data">
        <div class="table-responsive">
          <table class="table">
                  <thead class="text-primary">
                    <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Name</th>
                    <th style="width: 15%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Email</th>
                    <th style="width: 15%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Phone</th>
                    <th style="width: 7%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Role</th>
                    <th style="width: 13%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Created</th>
                    <th style="width: 13%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;">Last Update</th>
                    <th style="width: 10%; border: 1px solid #ddd; padding: 2px; background-color: #f2f2f2;" class="text-center">Action</th>
                  </thead>
                  <tbody>
                    <?php if ($inactive_users): ?>
                      <?php foreach ($inactive_users as $user): ?>
                        <tr>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['name']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['email']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['phone']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['role']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['created_at']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;"><?php echo htmlspecialchars($user['updated_at']); ?></td>
                          <td style="border: 1px solid #ddd; padding: 2px;" class="text-primary">
                            <div class="action-btn-group float-right d-flex">
                              <div style="display: flex; gap: 10px;">
    <button
        type="button"
        class="custom-action-btn btn btn-primary edit-btn"
        data-id="<?php echo htmlspecialchars($user['id']); ?>"
        data-name="<?php echo htmlspecialchars($user['name']); ?>"
        data-email="<?php echo htmlspecialchars($user['email']); ?>"
        data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
        data-role="<?php echo htmlspecialchars($user['role']); ?>"
        style="width: 90px; height: 38px;" 
    >
        Edit
    </button>
    <button
    type="button"
    class="custom-action-btn btn btn-success activate-btn"
    data-id="<?php echo htmlspecialchars($user['id']); ?>"
    style="width: 90px; height: 38px;" 
>
    Activate
</button>
</div>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7">No inactive users found</td>
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
 <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
 <!--<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function () {
    // Activate button handler
    $('.activate-btn').on('click', function () {
        var userId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to activate this user.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, activate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'activate_user.php',
                    data: { user_id: userId },
                    success: function (response) {
                        if (response === 'success') {
                            Swal.fire('Activated!', 'The user has been activated.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to activate user.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Error communicating with server.', 'error');
                    }
                });
            }
        });
    });

    // Deactivate button handler
    $('.deactivate-btn').on('click', function () {
        var userId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to deactivate this user.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, deactivate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'deactivate_user.php',
                    data: { user_id: userId },
                    success: function (response) {
                        if (response === 'success') {
                            Swal.fire('Deactivated!', 'The user has been deactivated.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to deactivate user.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error!', 'Error communicating with server.', 'error');
                    }
                });
            }
        });
    });
});


</script>



     <script>
  $(document).ready(function () {
  $('#search_button').on('click', function (e) {
    e.preventDefault(); // Prevent the default form submission

    // Get the search term
    var searchTerm = $('#search_input').val().toLowerCase();

    // Show or hide table rows based on the search term
    $('table tbody tr').each(function () {
      var row = $(this);
      var rowText = row.text().toLowerCase();

      // Check if the row text includes the search term
      if (rowText.indexOf(searchTerm) !== -1) {
        row.show(); // Show row if it matches
      } else {
        row.hide(); // Hide row if it doesn't match
      }
    });

    // Check if any rows are visible
    if ($('table tbody tr:visible').length === 0) {
      $('table tbody').html('<tr><td colspan="6">No results found</td></tr>');
    }
  });
});


  $(document).ready(function () {
    // Show modal with user data when 'Edit' button is clicked
    $('.edit-btn').on('click', function () {
        var userId = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');
        var phone = $(this).data('phone');
        var role = $(this).data('role');

        // Populate modal fields with user data
        $('#userId').val(userId);
        $('#updateName').val(name);
        $('#updateEmail').val(email);
        $('#updatePhone').val(phone);
        $('#updateRole').val(role);

        // Show the modal
        $('#editUserModal').modal('show');
    });

    // Handle form submission
    $('#editUserForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        $.ajax({
            type: 'POST',
            url: 'update_user.php',
            data: $(this).serialize(),
            dataType: 'json', // Expect JSON response
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    }).then(() => {
                        location.reload(); // Reload the page on success
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(textStatus, errorThrown); // Log errors
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error communicating with server.',
                });
            }
        });
    });
});


;


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
