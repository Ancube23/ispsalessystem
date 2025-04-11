<?php

session_start();

require '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'salesadmin') {
    header("Location: ../login.php");
    exit;
}

// Fetch user data
$sql = "SELECT id, name, email, role, created_at, updated_at FROM users";
$stmt = $conn->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('ID', 'Name', 'Email', 'Role', 'Created At', 'Updated At'));

if ($users) {
    foreach ($users as $user) {
        fputcsv($output, $user);
    }
}

fclose($output);
exit;
?>
