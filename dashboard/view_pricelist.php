<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Set error reporting to log errors to a specific file
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');  // Path where errors will be logged
error_reporting(E_ALL);  // Report all errors

// Error handling function to log errors
function log_error($message) {
    error_log($message, 3, 'error_log.txt'); // Log errors to the file
}

// Handle Deletions
try {
    if (isset($_GET['delete_fno'])) {
        $id = $_GET['delete_fno'];
        $stmt = $pdo->prepare("DELETE FROM fno WHERE FNO_id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: view_pricelist.php?success=deleted_fno");
        exit();
    }
    
        if (isset($_GET['delete_subgroup'])) {
        $id = $_GET['delete_subgroup'];
        $stmt = $pdo->prepare("DELETE FROM fno_subgroup WHERE Subgroup_id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: view_pricelist.php?success=deleted_subgroup");
        exit();
    }


    if (isset($_GET['delete_pricelist'])) {
        $id = $_GET['delete_pricelist'];
        $stmt = $pdo->prepare("DELETE FROM pricelist WHERE PR_id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: view_pricelist.php?success=deleted_pricelist");
        exit();
    }

    if (isset($_GET['delete_package'])) {
        $id = $_GET['delete_package'];
        $stmt = $pdo->prepare("DELETE FROM package WHERE PK_ID = :id");
        $stmt->execute(['id' => $id]);
        header("Location: view_pricelist.php?success=deleted_package");
        exit();
    }

} catch (PDOException $e) {
    log_error("PDO Error: " . $e->getMessage());  // Log PDO errors to file
    echo "An error occurred. Please try again later.";
    exit();
}

// Get filtering parameters for Packages only
$fno_filter = isset($_GET['fno_filter']) ? $_GET['fno_filter'] : '';
$pricelist_filter = isset($_GET['pricelist_filter']) ? $_GET['pricelist_filter'] : '';

// Base query for retrieving data
$query = "SELECT p.PK_ID, f.FNO_Name, pl.PR_Short_Description, p.PK_Term, p.PK_Speed, p.PK_Price 
    FROM package p
    JOIN fno f ON p.FNO_id = f.FNO_id
    JOIN pricelist pl ON p.PR_id = pl.PR_id";

// Prepare the filtering conditions
$conditions = [];
if (!empty($fno_filter)) {
    $conditions[] = "f.FNO_id = :fno_filter";
}
if (!empty($pricelist_filter)) {
    $conditions[] = "pl.PR_id = :pricelist_filter";
}

// Append the filtering conditions to the query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order the results by FNO name
$query .= " ORDER BY f.FNO_Name ASC";

// Prepare and execute the query
try {
    $stmt = $pdo->prepare($query);

    // Bind parameters if the filters are set
    if (!empty($fno_filter)) {
        $stmt->bindParam(':fno_filter', $fno_filter, PDO::PARAM_INT);
    }
    if (!empty($pricelist_filter)) {
        $stmt->bindParam(':pricelist_filter', $pricelist_filter, PDO::PARAM_INT);
    }

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $package_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    log_error("PDO Error during query execution: " . $e->getMessage()); // Log query errors to file
    echo "An error occurred. Please try again later.";
    exit();
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
/* Enable scrolling when there are many rows */
#FNO_Table_Container,
#Pricelist_Table_Container,
#Packages_Table_Container, 
#Terms_Table_Container {
    max-height: 300px; /* Limit the height */
    overflow-y: auto; /* Allow scrolling */
    display: block;
    border: 1px solid #ddd; /* Optional: visual separation */
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

/* Optional: Make table header sticky for better UX */
thead {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}

/* Ensure buttons have the same size and behavior */
button.btn-sm, a.btn-sm {
    display: inline-block; /* Ensure both behave similarly */
    padding: 5px 10px; /* Adjust padding to make both buttons the same size */
    font-size: 14px; /* Ensure font size is the same */
    line-height: 1.5; /* Adjust line height for uniformity */
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
              <a class="nav-link" href="./maintain.php">
                <img class="mr-2 img-small" src="./img/categorization.png" />
                Maintain Pricelist
              </a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="./view_pricelist.php">
                <img class="mr-2 img-small" src="./img/teamwork.png" />
                View Pricelist
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./view_pricelist_pdfs.php">
                <img class="mr-2 img-small" src="./img/smart-home.png" />
                View PDF
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
                            <a>
                                <span class="material-icons custom-material-icon">description</span>
                                <span>View Pricelist</span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- FNO Table -->
                            <div class="col-md-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header bg-dark text-white">
                                        <h5>Fibre Network Operator</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="FNO_Table_Container">
                                        <table id="FNO_Table" class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Name</th>
            <th>Logo</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        try {
            // Fetch FNO data using PDO
            $stmt = $pdo->prepare("SELECT FNO_id, FNO_Name, FNO_Logo FROM fno ORDER BY FNO_Name ASC");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['FNO_Name']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($row['FNO_Logo']); ?>" width="50"></td>
                    <td>
                        <button type="button" class="custom-action-btn btn btn-warning editFNO" data-id="<?php echo $row['FNO_id']; ?>" style="width: 90px; height: 38px;">Edit</button>
                        <a href="#" class="btn btn-danger btn-sm" style="display: inline-block;" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $row['FNO_id']; ?>" data-type="fno">Delete</a>

                    </td>
                </tr>
            <?php } 
        } catch (PDOException $e) {
            echo "Error fetching FNO data: " . $e->getMessage();
        }
        ?>
    </tbody>
</table>

                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5>Fibre Network Subgroups</h5>
        </div>
        <div class="card-body">
            <div id="Subgroup_Table_Container">
                <table id="Subgroup_Table" class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>FNO</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        try {
                            // Fetch Subgroup data along with FNO Name
                            $stmt = $pdo->prepare("
                                SELECT sg.Subgroup_id, sg.Subgroup_Name, f.FNO_Name 
                                FROM fno_subgroup sg
                                JOIN fno f ON sg.FNO_id = f.FNO_id
                                ORDER BY f.FNO_Name ASC, sg.Subgroup_Name ASC
                            ");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Subgroup_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['FNO_Name']); ?></td>
                                    <td>
                                        <button type="button" class="custom-action-btn btn btn-warning editSubgroup" data-id="<?php echo $row['Subgroup_id']; ?>" style="width: 90px; height: 38px;">Edit</button>
    <a href="view_pricelist.php?delete_subgroup=<?php echo $row['Subgroup_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subgroup?');">Delete</a>
                                    </td>
                                </tr>
                            <?php } 
                        } catch (PDOException $e) {
                            echo "Error fetching subgroup data: " . $e->getMessage();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


                            <!-- Pricelist Table -->
                            <div class="col-md-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">Pricelists</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="Pricelist_Table_Container">
                                        <table id="Pricelist_Table" class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Short Description</th>
            <th>Long Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        try {
            // Fetch pricelist data using PDO
            $stmt = $pdo->prepare("SELECT PR_id, PR_Short_Description, PR_Long_Description FROM pricelist ORDER BY PR_Short_Description ASC");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PR_Short_Description']); ?></td>
                    <td><?php echo htmlspecialchars($row['PR_Long_Description']); ?></td>
                    <td>
                        <button type="button" class="custom-action-btn btn btn-warning editPricelist" data-id="<?php echo $row['PR_id']; ?>" style="width: 90px; height: 38px;">Edit</button>
                        <a href="#" class="btn btn-danger btn-sm" style="display: inline-block;" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $row['PR_id']; ?>" data-type="pricelist">Delete</a>
                    </td>
                </tr>
            <?php } 
        } catch (PDOException $e) {
            echo "Error fetching pricelist data: " . $e->getMessage();
        }
        ?>
    </tbody>
</table>

                                    </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Packages Table -->
<!-- Packages Table -->
<div class="col-md-12 mb-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Packages</h5>
        </div>

        <!-- Dropdown Filters -->
        <form method="GET" id="filterForm">
            <div class="row">
                <!-- Fibre Operator Filter -->
                <div class="col-md-6">
                    <label for="fno_filter">Fibre Operator:</label>
                    <select name="fno_filter" id="fno_filter" class="form-control">
                        <option value="">All</option>
                        <?php
                        try {
                            // Fetch FNO data using PDO
                            $stmt = $pdo->prepare("SELECT FNO_id, FNO_Name FROM fno ORDER BY FNO_Name ASC");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row['FNO_id']; ?>" <?php if ($fno_filter == $row['FNO_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['FNO_Name']); ?>
                                </option>
                            <?php }
                        } catch (PDOException $e) {
                            echo "Error fetching FNO data: " . $e->getMessage();
                        }
                        ?>
                    </select>
                </div>

                <!-- Pricelist Filter -->
                <div class="col-md-6">
                    <label for="pricelist_filter">Pricelist:</label>
                    <select name="pricelist_filter" id="pricelist_filter" class="form-control">
                        <option value="">All</option>
                        <?php
                        try {
                            // Fetch Pricelist data using PDO
                            $stmt = $pdo->prepare("SELECT PR_id, PR_Short_Description FROM pricelist ORDER BY PR_Short_Description ASC");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row['PR_id']; ?>" <?php if ($pricelist_filter == $row['PR_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['PR_Short_Description']); ?>
                                </option>
                            <?php }
                        } catch (PDOException $e) {
                            echo "Error fetching Pricelist data: " . $e->getMessage();
                        }
                        ?>
                    </select>
                </div>
            </div>
        </form>
        <br>

        <div class="card-body">
            <div id="Packages_Table_Container">
                <table id="Package_Table" class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>FNO</th>
                            <th>Pricelist</th>
                            <th>Term</th>
                            <th>Speed (Mbps)</th>
                            <th>Price (R)</th>
                            <th>Subgroup</th> <!-- New Column for Subgroup -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Modified query to fetch the subgroup name instead of ID
                            $stmt = $pdo->prepare("
                                SELECT p.PK_ID, f.FNO_Name, pr.PR_Short_Description, p.PK_Term, p.PK_Speed, p.PK_Price, s.subgroup_name
                                FROM package p
                                JOIN fno f ON p.FNO_id = f.FNO_id
                                JOIN pricelist pr ON p.PR_id = pr.PR_id
                                LEFT JOIN fno_subgroup s ON p.subgroup_id = s.subgroup_id
                            ");
                            $stmt->execute();

                            // Loop through the results
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // Check if a subgroup is available
                                $subgroup = $row['subgroup_name'] ? htmlspecialchars($row['subgroup_name']) : "N/A";
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['FNO_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['PR_Short_Description']); ?></td>
                                <td><?php echo htmlspecialchars($row['PK_Term']); ?></td>
                                <td><?php echo htmlspecialchars($row['PK_Speed']); ?></td>
                                <td><?php echo htmlspecialchars($row['PK_Price']); ?></td>
                                <td><?php echo $subgroup; ?></td> <!-- Display Subgroup or N/A -->
                                <td>
                                    <button type="button" class="custom-action-btn btn btn-warning editPackage" data-id="<?php echo $row['PK_ID']; ?>" style="width: 90px; height: 38px;">Edit</button>
                                    <a href="#" class="btn btn-danger btn-sm" style="display: inline-block;" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $row['PK_ID']; ?>" data-type="package">Delete</a>
                                </td>
                            </tr>
                        <?php }
                        } catch (PDOException $e) {
                            echo "Error fetching package data: " . $e->getMessage();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



                            <!-- Terms & Conditions Table -->
                            <div class="col-md-12">
                                <div class="card shadow">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">Existing Terms & Conditions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="Terms_Table_Container">
                                         <table id="Terms_Table" class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Pricelist</th>
            <th>Terms</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        try {
            // Fetch terms data using PDO with join for Pricelist
            $stmt = $pdo->prepare("
                SELECT t.TC_id, t.Terms, p.PR_Short_Description 
                FROM tandc t
                JOIN pricelist p ON t.PR_id = p.PR_id
                ORDER BY p.PR_Short_Description ASC
            ");
            $stmt->execute();

            // Loop through the results
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PR_Short_Description']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['Terms'])); ?></td>
                    <td>
                        <button type="button" class="custom-action-btn btn btn-warning edit-term-btn" 
                                data-id="<?php echo $row['TC_id']; ?>" 
                                data-terms="<?php echo htmlspecialchars($row['Terms']); ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#editTermsModal" style="width: 90px; height: 38px;">Edit</button>
                        <a href="#" class="btn btn-danger btn-sm" style="display: inline-block;" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $row['TC_id']; ?>" data-type="terms">Delete</a>
                    </td>
                </tr>
            <?php }
        } catch (PDOException $e) {
            echo "Error fetching terms data: " . $e->getMessage();
        }
        ?>
    </tbody>
</table>

                                    </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- row -->
                    </div> <!-- card-body -->



    </div>

    <!-- FNO Edit Modal -->
<div class="modal fade" id="editFnoModal" tabindex="-1" aria-labelledby="editFnoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFnoModalLabel">Edit FNO</h5>
            </div>
            <div class="modal-body">
                <form id="editFnoForm" enctype="multipart/form-data">
                    <input type="hidden" id="editFnoId">
                    <div class="mb-3">
                        <label class="form-label">FNO Name</label>
                        <input type="text" class="form-control" id="editFnoName">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" id="editFnoLogo">
                        <img id="logoPreview" src="" width="100" style="display:none; margin-top: 10px;">
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="updateFnoBtn">Update</button>
                        <button type="button" class="btn btn-secondary mt-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Subgroup Edit Modal -->
<div class="modal fade" id="editSubgroupModal" tabindex="-1" aria-labelledby="editSubgroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubgroupModalLabel">Edit Subgroup</h5>
            </div>
            <div class="modal-body">
                <form id="editSubgroupForm">
                    <input type="hidden" id="editSubgroupId">
                    <div class="mb-3">
                        <label class="form-label">Subgroup Name</label>
                        <input type="text" class="form-control" id="editSubgroupName">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fibre Network Operator (FNO)</label>
                        <select class="form-control" id="editSubgroupFno">
                            <option value="">Select FNO</option>
                            <?php 
                            try {
                                $stmt = $pdo->prepare("SELECT FNO_id, FNO_Name FROM fno ORDER BY FNO_Name ASC");
                                $stmt->execute();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['FNO_id']}'>" . htmlspecialchars($row['FNO_Name']) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading FNOs</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="updateSubgroupBtn">Update</button>
                        <button type="button" class="btn btn-secondary mt-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Pricelist Edit Modal -->
<div class="modal fade" id="editPricelistModal" tabindex="-1" aria-labelledby="editPricelistModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPricelistModalLabel">Edit Pricelist</h5>
            </div>
            <div class="modal-body">
                <form id="editPricelistForm">
                    <input type="hidden" id="editPricelistId">
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" class="form-control" id="editPricelistShortDesc">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Long Description</label>
                        <textarea class="form-control" id="editPricelistLongDesc"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="updatePricelistBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Package Edit Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPackageModalLabel">Edit Package</h5>
            </div>
            <div class="modal-body">
                <form id="editPackageForm">
                    <input type="hidden" id="editPackageId">
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <input type="text" class="form-control" id="editPackageTerm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Speed (Mbps)</label>
                        <input type="text" class="form-control" id="editPackageSpeed">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (R)</label>
                        <input type="text" class="form-control" id="editPackagePrice">
                    </div>
                    <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="updatePackageBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT TERMS MODAL -->
<div class="modal fade" id="editTermsModal" tabindex="-1" aria-labelledby="editTermsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTermsModalLabel">Edit Terms & Conditions</h5>
            </div>
            <div class="modal-body">
                <form id="editTermsForm">
                    <input type="hidden" id="editTcId">
                    <div class="mb-3">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea class="form-control" id="editTermsText" rows="5"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="updateTermsBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>


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
    <script src="./js/main.js"></script>


    <!-- AJAX -->
    <script>
       $(document).ready(function() {
         // Check rows when the page is ready
    checkTableRows("fno");
    checkTableRows("pricelist");
    checkTableRows("package");

    $(document).on("click", ".editFNO", function() {
    var id = $(this).data("id");
    $.ajax({
        url: "fetch_data.php",
        type: "POST",
        data: { id: id, table: "fno" },
        success: function(data) {
            var result = JSON.parse(data);
            $("#editFnoId").val(result.FNO_id);
            $("#editFnoName").val(result.FNO_Name);
            $("#logoPreview").attr("src", result.FNO_Logo).show();

            // Explicitly show the modal
            $("#editFnoModal").modal("show");
        }
    });
});


    // Edit Pricelist
    $(".editPricelist").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: "fetch_data.php",
            type: "POST",
            data: { id: id, table: "pricelist" },
            success: function(data) {
                var result = JSON.parse(data);
                $("#editPricelistId").val(result.PR_id);
                $("#editPricelistShortDesc").val(result.PR_Short_Description);
                $("#editPricelistLongDesc").val(result.PR_Long_Description);
                $("#editPricelistModal").modal("show");
            }
        });
    });

    // Edit Package
    $(".editPackage").click(function() {
        var id = $(this).data("id");
        $.ajax({
            url: "fetch_data.php",
            type: "POST",
            data: { id: id, table: "package" },
            success: function(data) {
                var result = JSON.parse(data);
                $("#editPackageId").val(result.PK_ID);
                $("#editPackageTerm").val(result.PK_Term);
                $("#editPackageSpeed").val(result.PK_Speed);
                $("#editPackagePrice").val(result.PK_Price);
                $("#editPackageModal").modal("show");
            }
        });
    });

    // Update FNO
    $("#updateFnoBtn").click(function() {
        var id = $("#editFnoId").val();
        var name = $("#editFnoName").val();
        var logo = $("#editFnoLogo")[0].files[0];
        
        var formData = new FormData();
        formData.append("id", id);
        formData.append("name", name);
        if (logo) formData.append("logo", logo);

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Update Pricelist
    $("#updatePricelistBtn").click(function() {
        var id = $("#editPricelistId").val();
        var shortDesc = $("#editPricelistShortDesc").val();
        var longDesc = $("#editPricelistLongDesc").val();

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: { id: id, shortDesc: shortDesc, longDesc: longDesc, table: "pricelist" },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Update Package
    $("#updatePackageBtn").click(function() {
        var id = $("#editPackageId").val();
        var term = $("#editPackageTerm").val();
        var speed = $("#editPackageSpeed").val();
        var price = $("#editPackagePrice").val();

        $.ajax({
            url: "update_data.php",
            type: "POST",
            data: { id: id, term: term, speed: speed, price: price, table: "package" },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

     function checkTableRows(table) {
    $.ajax({
        url: "fetch_data.php",
        type: "POST",
        data: { table: table },
        success: function(data) {
            var result = JSON.parse(data);
            var rowCount = result.rowCount;

            if (rowCount >= 10) {
                // Enable scrolling if there are 10 or more rows
                $("#" + table + "_Table").closest(".card").css({
                    "max-height": "300px", // Adjust based on your needs
                    "overflow-y": "auto"
                });
            } else {
                // Remove scrolling if there are fewer than 10 rows
                $("#" + table + "_Table").closest(".card").css({
                    "max-height": "none",
                    "overflow-y": "none"
                });
            }
        },
        error: function() {
            alert("Error fetching row count.");
        }
    });
}


});

document.addEventListener("DOMContentLoaded", function() {
    function updateScroll(tableId, containerId) {
        const tableContainer = document.getElementById(containerId);
        const tableBody = document.querySelector(`#${tableId} tbody`);

        const rowCount = tableBody.getElementsByTagName("tr").length;
        if (rowCount > 10) { 
            tableContainer.style.overflowY = "auto"; // Enable scrolling
        } else {
            tableContainer.style.overflowY = "hidden"; // Hide scroll if not needed
        }
    }

    // Check scrolling for all tables
    updateScroll("FNO_Table", "FNO_Table_Container");
    updateScroll("Pricelist_Table", "Pricelist_Table_Container");
    updateScroll("Packages_Table", "Packages_Table_Container");
    updateScroll("Terms_Table", "Terms_Table_Table_Container");

    // Simulating row addition to demonstrate scrolling effect
    function addDummyRows(tableId, numRows) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        for (let i = 1; i <= numRows; i++) { 
            let row = document.createElement("tr");
            if (tableId === "FNO_Table") {
                row.innerHTML = `<td>Person ${i}</td><td>${20 + i}</td><td>Country ${i}</td>`;
            } else if (tableId === "Pricelist_Table") {
                row.innerHTML = `<td>Plan ${i}</td><td>${50 * i} Mbps</td><td>$${10 * i}</td>`;
            } else {
                row.innerHTML = `<td>Category ${i}</td><td>Details ${i}</td>`;
            }
            tableBody.appendChild(row);
        }
        updateScroll(tableId, `${tableId}_Container`);
    }

    // Simulate adding rows dynamically (for testing)
    addDummyRows("FNO_Table", 12); 
    addDummyRows("Pricelist_Table", 15);
    addDummyRows("Packages_Table", 8);
    addDummyRows("Terms_Table", 8);
});

$(document).ready(function() {
    $("#fno_filter, #pricelist_filter").change(function() {
        $("#filterForm").submit();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Open modal and populate with data
    document.querySelectorAll(".edit-term-btn").forEach(button => {
        button.addEventListener("click", function () {
            let tcId = this.getAttribute("data-id");
            let terms = this.getAttribute("data-terms");

            document.getElementById("editTcId").value = tcId;
            document.getElementById("editTermsText").value = terms;
        });
    });

    // Update terms via AJAX
    document.getElementById("updateTermsBtn").addEventListener("click", function () {
        let tcId = document.getElementById("editTcId").value;
        let terms = document.getElementById("editTermsText").value;

        fetch("update_terms.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `tc_id=${tcId}&terms=${encodeURIComponent(terms)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    });
});

$(document).ready(function() {
    // When the delete button is clicked
    $('a[data-toggle="modal"][data-target="#confirmDeleteModal"]').on('click', function() {
        var id = $(this).data('id');  // Get the ID from the data-id attribute
        var type = $(this).data('type');  // Get the type from the data-type attribute
        
        // Build the delete URL based on the type
        var deleteUrl = "view_pricelist.php?delete_" + type + "=" + id;

        // If the type is 'terms', the URL will be handled by a separate page (delete_terms.php)
        if (type === 'terms') {
            deleteUrl = "delete_terms.php?id=" + id;
        }

        // Set the href of the confirm delete button to the delete URL
        $('#confirmDeleteBtn').attr('href', deleteUrl);
    });
});

$(document).ready(function () {
    // Open the Edit Subgroup Modal
    $(".editSubgroup").click(function () {
        let subgroupId = $(this).data("id");

        // Fetch subgroup details via AJAX
        $.ajax({
            url: "get_subgroup.php",
            type: "GET",
            data: { id: subgroupId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#editSubgroupId").val(response.data.Subgroup_id);
                    $("#editSubgroupName").val(response.data.Subgroup_Name);
                    $("#editSubgroupFno").val(response.data.FNO_id);
                    $("#editSubgroupModal").modal("show");
                } else {
                    alert("Error fetching subgroup details.");
                }
            },
            error: function () {
                alert("Request failed.");
            }
        });
    });

    // Update Subgroup
    $("#updateSubgroupBtn").click(function () {
        let subgroupId = $("#editSubgroupId").val();
        let subgroupName = $("#editSubgroupName").val();
        let fnoId = $("#editSubgroupFno").val();

        $.ajax({
            url: "update_subgroup.php",
            type: "POST",
            data: {
                id: subgroupId,
                name: subgroupName,
                fno_id: fnoId
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert("Subgroup updated successfully!");
                    location.reload();
                } else {
                    alert("Failed to update subgroup.");
                }
            },
            error: function () {
                alert("Error updating subgroup.");
            }
        });
    });
});


    </script>

</body>
</html>