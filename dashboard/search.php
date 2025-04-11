<?php
session_start();
require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$users = [];

if ($search_query) {
    // Fetch user data based on search query
    $sql = "SELECT id, name, email, role, created_at, updated_at FROM users 
            WHERE name LIKE :query OR email LIKE :query OR role LIKE :query";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['query' => '%' . $search_query . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Search Results</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="./css/fontawesome-all.min.css">
    <link href="./css/materil.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/responsive.css" rel="stylesheet" />
    <link href="./css/style.css" rel="stylesheet" />
</head>
<body>
<div class="wrapper">
    <div class="main-panel">
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="javascript:;">Search Results</a>
                </div>
            </div>
        </nav>
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-primary">
                                <h4 class="card-title">Search Results</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="text-primary">
                                            <th>Name</th>
                                            <th>Email Address</th>
                                            <th>Role</th>
                                            <th>Created</th>
                                            <th>Last Update</th>
                                        </thead>
                                        <tbody>
                                            <?php if ($users): ?>
                                                <?php foreach ($users as $user): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['updated_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5">No results found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <a href="admin.php" class="btn btn-primary">Back to Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="./js/vendor/jquery-3.2.1.min.js"></script>
<script src="./js/popper.min.js"></script>
<script src="./js/bootstrap-material-design.min.js"></script>
<script src="./js/perfect-scrollbar.jquery.min.js"></script>
<script src="./js/moment.min.js"></script>
<script src="./js/sweetalert2.js"></script>
<script src="./js/jquery.validate.min.js"></script>
<script src="./js/jquery.bootstrap-wizard.js"></script>
<script src="./js/bootstrap-selectpicker.js"></script>
<script src="./js/bootstrap-datetimepicker.min.js"></script>
<script src="./js/jquery.dataTables.min.js"></script>
<script src="./js/bootstrap-tagsinput.js"></script>
<script src="./js/jasny-bootstrap.min.js"></script>
<script src="./js/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
<script src="./js/arrive.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="./js/chartist.min.js"></script>
<script src="./js/bootstrap-notify.js"></script>
<script src="./js/material-dashboard.min.js?v=2.1.2" type="text/javascript"></script>
<script src="./js/main.js"></script>
</body>
</html>
