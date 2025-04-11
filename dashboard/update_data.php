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

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_fno'])) {
    $fno_id = $_POST['fno_id'] ?? null;
    $fno_name = trim(htmlspecialchars($_POST['fno_name'] ?? ''));

    if (!empty($fno_id) && !empty($fno_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE fno SET FNO_Name = :fno_name WHERE FNO_id = :fno_id");
            $stmt->execute(['fno_name' => $fno_name, 'fno_id' => $fno_id]);
            echo "FNO updated successfully!";
        } catch (PDOException $e) {
            logError("Error updating FNO: " . $e->getMessage());
            echo "Error updating FNO: " . $e->getMessage();
        }
    } else {
        echo "Invalid FNO ID or empty name.";
    }
    exit();
}


    
    // Handle Delete FNO
    if (isset($_GET['delete_fno'])) {
        $id = $_GET['delete_fno'];
        echo "Attempting to delete FNO ID: " . $id;
        exit();
    }
    
    // Handle Delete Subgroup
    if (isset($_GET['delete_subgroup'])) {
        $id = $_GET['delete_subgroup'];
        echo "Attempting to delete Subgroup ID: " . $id;
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
