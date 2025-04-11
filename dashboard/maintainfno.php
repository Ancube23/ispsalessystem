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

// Fetch existing subgroups
try {
    $stmt = $pdo->query("SELECT fno_subgroup.subgroup_id, fno_subgroup.subgroup_name, 
                                fno.FNO_Name, pricelist.PR_Short_Description 
                         FROM fno_subgroup
                         LEFT JOIN fno ON fno_subgroup.fno_id = fno.FNO_id
                         LEFT JOIN pricelist ON fno_subgroup.pricelist_id = pricelist.PR_id");
    $subgroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Error fetching subgroups: " . $e->getMessage());
    $subgroups = []; // Ensure $subgroups is always an array
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
                    $stmt = $pdo->prepare("INSERT INTO fno_subgroup (fno_id, subgroup_name) VALUES (:fno_id, :subgroup_name)");
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
        $pricelist_id = $_POST['pricelist_id'] ?? null; // Pricelist is optional

        if (!empty($fno_id) && !empty($subgroup_name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO fno_subgroup (fno_id, subgroup_name, pricelist_id) VALUES (:fno_id, :subgroup_name, :pricelist_id)");
                $stmt->execute(['fno_id' => $fno_id, 'subgroup_name' => $subgroup_name, 'pricelist_id' => $pricelist_id]);
                $_SESSION['success_msg'] = "Subgroup added successfully!";
            } catch (PDOException $e) {
                logError("Error adding subgroup: " . $e->getMessage());
                $_SESSION['error_msg'] = "Error adding subgroup. Please try again.";
            }
        } else {
            $_SESSION['error_msg'] = "Please select an FNO and enter a subgroup name.";
        }
    }

    // Handle Delete FNO (Deletes associated subgroups first)
if (isset($_GET['delete_fno'])) {
    $id = $_GET['delete_fno'];

    // Verify FNO exists
    $stmt = $pdo->prepare("SELECT * FROM fno WHERE FNO_id = :id");
    $stmt->execute(['id' => $id]);
    $fno = $stmt->fetch();

    if ($fno) {
        // Delete associated subgroups first
        $stmt = $pdo->prepare("DELETE FROM fno_subgroup WHERE fno_id = :id");
        $stmt->execute(['id' => $id]);

        // Double-check if subgroups were deleted before deleting the FNO
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM fno_subgroup WHERE fno_id = :id");
        $stmt->execute(['id' => $id]);
        $subgroup_count = $stmt->fetchColumn();

        if ($subgroup_count == 0) {
            // Delete the FNO
            $stmt = $pdo->prepare("DELETE FROM fno WHERE FNO_id = :id");
            $stmt->execute(['id' => $id]);

            $_SESSION['success_msg'] = "FNO and associated subgroups deleted successfully.";
        } else {
            $_SESSION['error_msg'] = "Error: Some subgroups were not deleted.";
        }
    } else {
        $_SESSION['error_msg'] = "FNO not found.";
    }

    header("Location: maintainfno.php");
    exit();
}


    // Handle Delete Subgroup
if (isset($_GET['delete_subgroup'])) {
    $id = $_GET['delete_subgroup'];

    // Verify Subgroup exists
    $stmt = $pdo->prepare("SELECT * FROM fno_subgroup WHERE subgroup_id = :id");
    $stmt->execute(['id' => $id]);
    $subgroup = $stmt->fetch();

    if ($subgroup) {
        // Delete the subgroup
        $stmt = $pdo->prepare("DELETE FROM fno_subgroup WHERE subgroup_id = :id");
        $stmt->execute(['id' => $id]);

        $_SESSION['success_msg'] = "Subgroup deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Subgroup not found.";
    }
    header("Location: maintainfno.phpp");
    exit();
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
    </li>
    <li class="nav-item">
        <a class="nav-link" href="./admin.php">
            <img class="mr-2 img-small" src="./img/group.png" />
            Users
        </a>
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

    <!-- Maintain Tab with Collapse -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#maintainCollapse" data-toggle="collapse" aria-expanded="false">
            <img class="mr-2 img-small" src="./img/categorization.png" />
            Maintain Prices
        </a>
        <div id="maintainCollapse" class="collapse">
            <ul class="nav flex-column">
                <li class="nav-item active">
                    <a class="nav-link" href="./maintainfno.php">Maintain FNO</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintainpricelist.php">Maintain Pricelist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintainpackage.php">Maintain Package</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./maintaintandc.php">Maintain Terms & Cover</a>
                </li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="./view_pricelist_pdfs.php">
            <img class="mr-2 img-small" src="./img/smart-home.png" />
            View PDF
        </a>
    </li>
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
                        <!-- ADD NEW FNO FORM -->
                        <div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Add FNO Form -->
        <form method="POST" enctype="multipart/form-data" class="card p-4 shadow mb-4">
            <h5 class="text-center">Add Fibre Network Operator</h5>
            <div class="mb-3">
                <label class="form-label">FNO Name</label>
                <input type="text" name="fno_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">FNO Logo</label>
                <input type="file" name="fno_logo" class="form-control" required>
            </div>
            <div class="text-center">
                <button type="submit" name="submit_fno" class="btn btn-primary">Add FNO</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Subgroup Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <form method="POST" class="card p-4 shadow mb-4">
            <h5 class="text-center">Add Subgroup for FNO</h5>

            <!-- Select FNO -->
            <div class="mb-3">
                <label class="form-label">Select FNO</label>
                <select name="fno_id" class="form-control" required>
                    <option value="" disabled selected>Select an FNO</option>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT FNO_id, FNO_Name FROM fno ORDER BY FNO_Name ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['FNO_id']}'>{$row['FNO_Name']}</option>";
                        }
                    } catch (PDOException $e) {
                        logError("Error fetching FNOs: " . $e->getMessage());
                    }
                    ?>
                </select>
            </div>

            <!-- Select Pricelist -->
            <div class="mb-3">
                <label class="form-label">Select Pricelist</label>
                <select name="pricelist_id" class="form-control" required>
                    <option value="" disabled selected>Select a Pricelist</option>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT PR_id, PR_Short_Description FROM pricelist ORDER BY PR_Short_Description ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['PR_id']}'>{$row['PR_Short_Description']}</option>";
                        }
                    } catch (PDOException $e) {
                        logError("Error fetching pricelists: " . $e->getMessage());
                    }
                    ?>
                </select>
            </div>

            <!-- Subgroup Name -->
            <div class="mb-3">
                <label class="form-label">Subgroup Name</label>
                <input type="text" name="subgroup_name" class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" name="submit_subgroup" class="btn btn-primary">Add Subgroup</button>
            </div>
        </form>
    </div>
</div>

</div>



<!-- FNO Table -->
<div class="col-md-12 mb-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5>Fibre Network Operators</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
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
                        $stmt = $pdo->prepare("SELECT FNO_id, FNO_Name, FNO_Logo FROM fno ORDER BY FNO_Name ASC");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['FNO_Name']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['FNO_Logo']); ?>" width="50"></td>
                                <td>
                                    <button type="button" class="btn btn-warning editFNO" style="width: 90px; height: 38px;" data-id="<?php echo $row['FNO_id']; ?>">Edit</button>
                                    <a href="#" class="btn btn-danger btn-sm delete-btn" 
   data-toggle="modal" 
   data-target="#confirmDeleteModal" 
   data-id="<?php echo $row['FNO_id']; ?>" 
   data-type="fno">Delete</a>
                                </td>
                            </tr>
                        <?php } 
                    } catch (PDOException $e) {
                        logError("Error fetching FNO data: " . $e->getMessage());
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Subgroup Table -->
<div class="col-md-12 mb-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5>Fibre Network Subgroups</h5>
        </div>
        <div class="card-body">
            <div id="Subgroup_Table_Container" class="card-body">

            <table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>FNO Name</th>
            <th>Subgroup Name</th>
            <th>Pricelist</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subgroups as $subgroup): ?>
            <tr>
                <td><?= htmlspecialchars($subgroup['subgroup_id']) ?></td>
                <td><?= htmlspecialchars($subgroup['FNO_Name']) ?></td>
                <td><?= htmlspecialchars($subgroup['subgroup_name']) ?></td>
                <td><?= htmlspecialchars($subgroup['PR_Short_Description'] ?? 'Not Assigned') ?></td>
                <td>
                   <button type="button" class="btn btn-warning editSubgroup" style="width: 90px; height: 38px;" data-id="<?= $subgroup['subgroup_id']; ?>">Edit</button>


                                        <!-- Subgroup Delete Button -->
<a href="#" class="btn btn-danger btn-sm delete-btn" 
   data-toggle="modal" 
   data-target="#confirmDeleteModal" 
   data-id="<?php echo $subgroup['subgroup_id']; ?>" 
   data-type="subgroup">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        </div>
        </div>
        </div>
    </div>
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
                            // Fetch FNO options dynamically
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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>





    <script>
    $(".editFNO").click(function() {
    var id = $(this).data("id"); // Get dynamic ID
    $.ajax({
        url: "fetch_data.php",
        type: "POST",
        data: { id: id, table: "fno" },
        success: function(data) {
            var result = JSON.parse(data);
            $("#editFnoId").val(result.FNO_id);
            $("#editFnoName").val(result.FNO_Name);
            $("#logoPreview").attr("src", result.FNO_Logo).show();
            $("#editFnoModal").modal("show");
        }
    });
});


        // Update FNO
       $("#updateFnoBtn").click(function() {
    var id = $("#editFnoId").val();
    var name = $("#editFnoName").val();
    var logo = $("#editFnoLogo")[0].files[0];

    var formData = new FormData();
    formData.append("update_fno", true); // ADD THIS LINE
    formData.append("fno_id", id);
    formData.append("fno_name", name);
    if (logo) formData.append("fno_logo", logo);

    $.ajax({
        url: "update_data.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            alert(response); // Check if there's an actual response
            location.reload();
        },
        error: function(xhr, status, error) {
            alert("Error: " + xhr.responseText); // Debugging error messages
        }
    });
});


        // Scroll handling for FNO Table
        updateScroll("FNO_Table", "FNO_Table_Container");

        function updateScroll(tableId, containerId) {
            const tableContainer = document.getElementById(containerId);
            const tableBody = document.querySelector(`#${tableId} tbody`);
            const rowCount = tableBody.getElementsByTagName("tr").length;
            if (rowCount > 10) { 
                tableContainer.style.overflowY = "auto";
            } else {
                tableContainer.style.overflowY = "hidden";
            }
        }

        // Simulate adding rows dynamically (for testing purposes)
        function addDummyRows(tableId, numRows) {
            const tableBody = document.querySelector(`#${tableId} tbody`);
            for (let i = 1; i <= numRows; i++) {
                let row = document.createElement("tr");
                row.innerHTML = `<td>Person ${i}</td><td>${20 + i}</td><td>Country ${i}</td>`;
                tableBody.appendChild(row);
            }
            updateScroll(tableId, `${tableId}_Container`);
        }

        addDummyRows("FNO_Table", 12); // Example: adding 12 rows to FNO table

$(document).on("click", ".editSubgroup", function() {
    console.log("Edit button clicked");
    var id = $(this).data("id"); // Get dynamic ID
    console.log(id); // Log the ID to make sure it's being passed
    $.ajax({
        url: "get_subgroup.php", // Use get_subgroup.php
        type: "POST",
        data: { id: id }, // Send the ID of the subgroup
        success: function(data) {
            var result = JSON.parse(data);
            if (result.success) {
                $("#editSubgroupId").val(result.data.subgroup_id);
                $("#editSubgroupName").val(result.data.subgroup_name);
                $("#editSubgroupFno").val(result.data.fno_id); // Preselect the FNO
                $("#editSubgroupModal").modal("show"); // Open the modal
            } else {
                alert("Error: " + result.message);
            }
        },
        error: function() {
            alert("Error fetching subgroup data.");
        }
    });
});



  

$("#updateSubgroupBtn").click(function() {
    var subgroupId = $("#editSubgroupId").val();
    var subgroupName = $("#editSubgroupName").val();
    var fnoId = $("#editSubgroupFno").val();

    $.ajax({
        url: "update_subgroup.php",
        type: "POST",
        data: { subgroup_id: subgroupId, subgroup_name: subgroupName, fno_id: fnoId },
        success: function(response) {
            if (response.success) {
                location.reload(); // Refresh page to show updated data
            } else {
                alert(response.message);
            }
        }
    });
});

        // Scroll handling for Subgroup Table
        updateScroll("Subgroup_Table", "Subgroup_Table_Container");

        // Simulate adding rows dynamically (for testing purposes)
        addDummyRows("Subgroup_Table", 8); // Example: adding 8 rows to Subgroup table

        $(document).ready(function () {
        let deleteUrl = ""; // To store delete URL

        $(".delete-btn").on("click", function () {
            let recordId = $(this).data("id"); // Get the ID from the button
            deleteUrl = "delete_terms.php?id=" + recordId; // Modify the delete URL dynamically

            $("#confirmDeleteBtn").attr("href", deleteUrl); // Set the href to the delete button
        });

        $("#confirmDeleteBtn").on("click", function (e) {
            e.preventDefault(); // Prevent default link behavior

            $.ajax({
                url: deleteUrl,
                type: "GET", // Change to POST if necessary
                success: function (response) {
                    alert("Record deleted successfully!");
                    location.reload(); // Reload the page to reflect changes
                },
                error: function () {
                    alert("Error deleting record.");
                }
            });
        });
    });


$(document).ready(function() {
    // When the delete button is clicked
    $('a[data-toggle="modal"][data-target="#confirmDeleteModal"]').on('click', function() {
        var id = $(this).data('id');  // Get the ID from the data-id attribute
        var type = $(this).data('type');  // Get the type from the data-type attribute
        
        // Build the delete URL based on the type
        var deleteUrl = "maintainfno.php?delete_" + type + "=" + id;

        // If the type is 'terms', the URL will be handled by a separate page (delete_terms.php)
        if (type === 'terms') {
            deleteUrl = "delete_terms.php?id=" + id;
        }

        // Set the href of the confirm delete button to the delete URL
        $('#confirmDeleteBtn').attr('href', deleteUrl);
    });
});


      $(".delete-btn").click(function() {
    var id = $(this).data("id");  // Get dynamic ID from the button clicked
    var type = $(this).data("type");  // Get the type (fno or subgroup)

    // Set the correct deletion URL based on the type (FNO or Subgroup)
    var deleteUrl = type === 'fno' ? 'maintainfno.php?delete_fno=' + id : 'maintainfno.php?delete_subgroup=' + id;

    // Set up the delete confirmation
    $("#confirmDeleteBtn").attr("href", deleteUrl);  // Update the delete link with the URL
    $("#confirmDeleteModal").modal("show");  // Show the modal
});




</script>

</body>
</html>