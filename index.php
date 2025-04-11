<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">

    <!-- Font Awesome CSS -->
    <link href="https://use.fontawesome.com/releases/v5.0.3/css/all.css" rel="stylesheet">

	<link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style.css">
    <title>Sales</title>
</head>
<body>

	<main>

        <!--<div class='light x1'></div>
    <div class='light x2'></div>
    <div class='light x3'></div>
    <div class='light x4'></div>
    <div class='light x5'></div>
    <div class='light x6'></div>
    <div class='light x7'></div>
    <div class='light x8'></div>
    <div class='light x9'></div>

    <div>-->
        <h2>Navigation</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="create_user.php">Create User</a></li>
            <li><a href="sales_report.php">Sales Report</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div>
        <!-- Your dashboard content goes here -->

        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_role']); ?>!</h2>
    <a href="logout.php">Logout</a>
    </div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark datPinkColor">	

<div class="container">

<!-- Start Container -->

  <a class="navbar-brand" href="#">Navbar</a>

  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="#">Dashboard <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Restaurants</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Clients</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Pages</a>
      </li>
    </ul>

	</div>
    <span class="navbar-text text-white">Welcome Back Victor</span>

    <span>

    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Create Content
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" data-target="#exampleModal" data-toggle="modal">Create Restaurant</a>
          <a class="dropdown-item" href="#">Create Pages</a>
        </div>
      </li>
    </ul>

    </span>

<!-- Container Ends Here -->
</div>

</nav>
<!-- End Navbar -->

<!-- Start Jumbotron -->

<div id="jumbotron" class="jumbotron jumbotron-fluid">
	<div class="container">
	  <h1 class="display-4 text-white"><i class="fas fa-cogs"></i> Dashboard</h1>
	</div>
</div>

<!-- End Jumbotron -->

<br>

<!-- Start Main Container -->
<div id="main" class="container">

<!-- Breadcrumb -->
	<nav aria-label="breadcrumb">
  		<ol class="breadcrumb">
    		<li class="breadcrumb-item active" aria-current="page">Home</li>
  		</ol>
	</nav>

<!-- End Breadcrumb -->

<!-- Left Row -->
<div class="row">

<!-- First Left Column -->
<div class=col-3>

	<ul class="list-group">
  		<li class="list-group-item active datPinkColor">Overview</li>
  		<li class="list-group-item"><i class="fas fa-pencil-alt"></i> Pages</li>
  		<li class="list-group-item"><i class="fas fa-chart-bar"></i> Posts</li>
  		<li class="list-group-item"><i class="fas fa-users"></i> Users</li>
	</ul>

<!-- End First Left Column -->
	<br>

<!-- Second Left Column -->
	<div class="card">
  		<div class="card-body">
    		<h4>Bandwidth used</h4>
    		<div class="progress">
  			<div class="progress-bar" role="progressbar" id="firstProgressBar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">80%</div>
			</div>
			<br>
			<h4>Space used</h4>
    		<div class="progress">
  			<div class="progress-bar" role="progressbar" id="secondProgressBar" aria-valuenow="57" aria-valuemin="0" aria-valuemax="100">57%</div>
			</div>
  		</div>
	</div>

</div>
<!-- End Second Left Column -->

<!-- End Left Row -->

<!-- Right Column -->

<div class=col-9>

	<!-- Featured Card -->
	<div class="card">
  		<div class="card-header text-white datPinkColor">
    	Featured
  		</div>
  		<div class="card-body">
   			 <blockquote class="blockquote mb-0">
   			 	<!-- Column -->
   			 	<div class="row">
   			 	<div class="col-3">
      				<div class="card text-center bg-light">
 						<div class="card-body">
  						 	<h3 class="card-text"><i class="fas fa-chart-bar"></i> 203</h3>
  						 	<h5>Views</h5>
 						</div>
					</div>
				</div>
				<div class="col-3">
					<div class="card text-center bg-light">
 						<div class="card-body">
  						 	<h3 class="card-text"><i class="fas fa-pencil-alt"></i> 300</h3>
  						 	<h5>Posts</h5>
 						</div>
					</div>
				</div>
				<div class="col-3">
					<div class="card text-center bg-light">
 						<div class="card-body">
  						 	<h3 class="card-text"><i class="fas fa-user"></i> 251</h3>
  						 	<h5>Users</h5>
 						</div>
					</div>
				</div>
				<div class="col-3">
					<div class="card text-center bg-light">
 						<div class="card-body">
  						 	<h3 class="card-text"><i class="fas fa-utensils"></i> 20</h3>
  						 	<h5>Restaurants</h5>
 						</div>
					</div>
				</div>
				</div>
				<!-- End Column -->
   	 		</blockquote>
  		</div>
	</div>

	<!-- End Featured Card -->
<br>
	<!-- New Users -->
	<div class="card">
  		<div class="card-header text-white datPinkColor">
    	New Users
  		</div>
  		<div class="card-body">
   			 <blockquote class="blockquote mb-0">
   			 <!-- Table -->
   			 <table class="table table-bordered table-striped">
  			 	<thead>
    				<tr>
      					<th scope="col">Name</th>
      					<th scope="col">Email</th>
      					<th scope="col">Cell</th>
    				</tr>
  				</thead>
  				<tbody>
    				<tr>
      					<td>User 1</td>
      					<td>user1@gmail.com</td>
      					<td>012345678</td>
    				</tr>
    				<tr>
      					<td>User 2</td>
      					<td>user2@gmail.com</td>
      					<td>012345678</td>
    				</tr>
    				<tr>
     					<td>User 3</td>
     					<td>user3@gmail.com</td>
     					<td>012345678</td>
    				</tr>
  				</tbody>
			</table>
			<!-- End Table -->
   			 </blockquote>
  		</div>
	</div>
	<!-- End New Users -->

</div>
</div>

<!-- End Right Column -->

</div>

<!-- End Main Container -->

<br>

<!-- Start Footer -->

<div id="footer">
    <div class="container">
	  <h6 class="display-5 text-white text-center">&copy; Copyright 2018 - Jin Wei</h6>
    </div>
</div>

<!-- End Footer -->

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- End Modal -->

<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>

</main>
</body>
</html>
