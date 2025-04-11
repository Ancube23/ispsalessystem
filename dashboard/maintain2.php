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

// Function to log errors
function logError($message) {
    file_put_contents("error_log.txt", date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

try {
    // Handle FNO submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_fno'])) {
        $fno_name = trim(htmlspecialchars($_POST['fno_name']));
        $fno_logo = $_FILES['fno_logo'];
        $subgroup_name = trim(htmlspecialchars($_POST['subgroup_name'] ?? ''));
        
        if (!empty($fno_name) && isset($fno_logo) && $fno_logo['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_extension = pathinfo($fno_logo['name'], PATHINFO_EXTENSION);
            $target_file = $target_dir . uniqid("fno_", true) . '.' . $file_extension;
            
            if (move_uploaded_file($fno_logo['tmp_name'], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO fno (FNO_Name, FNO_Logo) VALUES (:name, :logo)");
                $stmt->execute(['name' => $fno_name, 'logo' => $target_file]);
                $fno_id = $pdo->lastInsertId();
                
                // Insert Subgroup if provided
                if (!empty($subgroup_name)) {
                    $stmt = $pdo->prepare("INSERT INTO fno_subgroup (FNO_id, Subgroup_Name) VALUES (:fno_id, :subgroup_name)");
                    $stmt->execute(['fno_id' => $fno_id, 'subgroup_name' => $subgroup_name]);
                }
                
                $_SESSION['success_msg'] = "FNO added successfully!";
            } else {
                $_SESSION['error_msg'] = "File upload failed.";
            }
        } else {
            $_SESSION['error_msg'] = "Invalid input or file upload error.";
        }
    }
    
    // Handle Subgroup submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_subgroup'])) {
    $fno_id = $_POST['fno_id'] ?? null;
    $subgroup_name = trim(htmlspecialchars($_POST['subgroup_name'] ?? ''));

    if (!empty($fno_id) && !empty($subgroup_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO fno_subgroup (FNO_id, Subgroup_Name) VALUES (:fno_id, :subgroup_name)");
            $stmt->execute(['fno_id' => $fno_id, 'subgroup_name' => $subgroup_name]);

            $_SESSION['success_msg'] = "Subgroup added successfully!";
        } catch (PDOException $e) {
            logError("Error adding subgroup: " . $e->getMessage());
            $_SESSION['error_msg'] = "Error adding subgroup. Please try again.";
        }
    } else {
        $_SESSION['error_msg'] = "Please select an FNO and enter a subgroup name.";
    }
}

    
    // Handle Pricelist submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pricelist'])) {
        $pr_short = trim(htmlspecialchars($_POST['pr_short'] ?? ''));
        $pr_long = trim(htmlspecialchars($_POST['pr_long'] ?? ''));
        
        if (!empty($pr_short) && !empty($pr_long)) {
            $stmt = $pdo->prepare("INSERT INTO pricelist (PR_Short_Description, PR_Long_Description) VALUES (:short, :long)");
            $stmt->execute(['short' => $pr_short, 'long' => $pr_long]);
            $_SESSION['success_msg'] = "Pricelist added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: Both Short and Long descriptions are required.";
        }
    }

    // Handle Package submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_package'])) {
    $stmt = $pdo->prepare("INSERT INTO package (PR_id, FNO_id, PK_Term, PK_Speed, PK_Price, Subgroup_id) VALUES (:pr_id, :fno_id, :term, :speed, :price, :subgroup_id)");
    $stmt->execute([
        'pr_id' => $_POST['pr_id'] ?? null,
        'fno_id' => $_POST['fno_id'] ?? null,
        'term' => $_POST['pk_term'] ?? null,
        'speed' => $_POST['pk_speed'] ?? null,
        'price' => $_POST['pk_price'] ?? null,
        'subgroup_id' => $_POST['subgroup_id'] ?? null // Add this line to insert subgroup_id
    ]);
    $_SESSION['success_msg'] = "Package added successfully!";
}

    
    // Handle Terms & Conditions submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_terms'])) {
        $stmt = $pdo->prepare("INSERT INTO tandc (PR_id, Terms) VALUES (:pr_id, :terms)");
        $stmt->execute(['pr_id' => $_POST['pr_id'] ?? null, 'terms' => $_POST['terms'] ?? '']);
        $_SESSION['success_msg'] = "Terms & Conditions added successfully!";
    }

    // Handle Cover Page Upload
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['cover_page'])) {
        $pricelist_id = $_POST['pricelist'] ?? null;
        $cover_page = $_FILES['cover_page'];
        $target_dir = "uploads/cover_pages/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = basename($cover_page["name"]);
        $target_file = $target_dir . uniqid("cover_", true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array($file_extension, $allowedExtensions) || $cover_page["size"] > 5000000) {
            $_SESSION['error_msg'] = "Invalid file type or file too large.";
        } else {
            if (move_uploaded_file($cover_page["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO cover_page (PR_id, CP_FilePath) VALUES (:pr_id, :file_path)");
                $stmt->execute(['pr_id' => $pricelist_id, 'file_path' => $target_file]);
                $_SESSION['success_msg'] = "File uploaded successfully!";
            } else {
                $_SESSION['error_msg'] = "File upload error.";
            }
        }
    }
} catch (PDOException $e) {
    logError("Database Error: " . $e->getMessage());
    $_SESSION['error_msg'] = "A database error occurred. Please try again.";
} catch (Exception $e) {
    logError("General Error: " . $e->getMessage());
    $_SESSION['error_msg'] = "An unexpected error occurred.";
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
            <li class="nav-item active">
              <a class="nav-link" href="./maintain.php">
                <img class="mr-2 img-small" src="./img/categorization.png" />
                Maintain Pricelist
              </a>
            </li>
            <li class="nav-item">
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
<!-- JavaScript for the alert popup -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <script>
        alert("Success: <?php echo $_SESSION['success_msg']; ?>");
    </script>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <script>
        alert("Error: <?php echo $_SESSION['error_msg']; ?>");
    </script>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <div class="float-left">
                            <a>
                                <span class="material-icons custom-material-icon">description</span>
                                <span>Maintain</span>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- ADD PACKAGE FORM -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <form method="POST" class="card p-4 shadow mb-4">
            <h5 class="text-center">Add Package</h5>

            <!-- FNO Dropdown -->
            <div class="mb-3">
                <label class="form-label">Fibre Network Operator</label>
                <select name="fno_id" id="fnoSelect" class="form-control" required>
                    <option value="">Select FNO</option>
                    <?php 
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM fno ORDER BY FNO_Name ASC");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $row['FNO_id']; ?>"><?php echo htmlspecialchars($row['FNO_Name']); ?></option>
                        <?php }
                    } catch (PDOException $e) {
                        echo "Error fetching FNO: " . $e->getMessage();
                    }
                    ?>
                </select>
            </div>

            <!-- FNO Subgroup Dropdown (Optional) -->
            <div class="mb-3">
                <label class="form-label">FNO Subgroup <small class="text-muted">(Optional)</small></label>
                <select name="subgroup_id" id="subgroupSelect" class="form-control">
                    <option value="">No Subgroup</option>
                </select>
            </div>

            <!-- Pricelist Dropdown -->
            <div class="mb-3">
                <label class="form-label">Pricelist</label>
                <select name="pr_id" class="form-control" required>
                    <option value="">Select Pricelist</option>
                    <?php 
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM pricelist ORDER BY PR_Short_Description ASC");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?php echo $row['PR_id']; ?>"><?php echo htmlspecialchars($row['PR_Short_Description']); ?></option>
                        <?php }
                    } catch (PDOException $e) {
                        echo "Error fetching Pricelist: " . $e->getMessage();
                    }
                    ?>
                </select>
            </div>

            <!-- Term Selection -->
            <div class="mb-3">
                <label class="form-label d-block">Term</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input class="black-radio" type="radio" name="pk_term" value="Month-to-Month" required>
                        <label>Month-to-Month</label>
                    </div>
                    <div class="radio-item">
                        <input class="black-radio" type="radio" name="pk_term" value="12 Months" required>
                        <label>12 Months</label>
                    </div>
                    <div class="radio-item">
                        <input class="black-radio" type="radio" name="pk_term" value="24 Months" required>
                        <label>24 Months</label>
                    </div>
                    <div class="radio-item">
                        <input class="black-radio" type="radio" name="pk_term" value="36 Months" required>
                        <label>36 Months</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Speed (Mbps)</label>
                <input type="text" name="pk_speed" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (R)</label>
                <input type="text" name="pk_price" class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" name="submit_package" class="btn btn-primary">Save Package</button>
            </div>
        </form>
    </div>
</div>



                       
!-- Packages Table -->
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






                    </div> <!-- End of Card Body -->
                </div> <!-- End of Card -->
            </div> <!-- End of Col-md-12 -->
        </div> <!-- End of Row -->
    </div> <!-- End of Container-fluid -->
</div> <!-- End of Content -->


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
    document.addEventListener("DOMContentLoaded", function() {
        var alertList = document.querySelectorAll('.alert');
        alertList.forEach(function(alert) {
            new bootstrap.Alert(alert);
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



    
    
    document.getElementById("fnoSelect").addEventListener("change", function() {
    let fnoId = this.value;
    let subgroupSelect = document.getElementById("subgroupSelect");

    if (fnoId) {
        fetch(`fetch_subgroups.php?fno_id=${fnoId}`)
            .then(response => response.json())
            .then(data => {
                subgroupSelect.innerHTML = '<option value="">No Subgroup</option>';
                data.forEach(subgroup => {
                    let option = document.createElement("option");
                    option.value = subgroup.id;
                    option.textContent = subgroup.name;
                    subgroupSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching subgroups:", error));
    } else {
        subgroupSelect.innerHTML = '<option value="">No Subgroup</option>';
    }
});
</script>

</body>
</html>