<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Fetch the user record, ensuring the user is active
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND active = 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Email exists, now check the password
            if (password_verify($password, $user['password'])) {
                // Password is correct
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];

                // Check if "Remember Me" is checked
                if (isset($_POST['remember_me'])) {
                    // Generate a secure token
                    $token = bin2hex(random_bytes(16));
                    // Store the token hash in the database
                    $stmt = $conn->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
                    $stmt->bindParam(':token', password_hash($token, PASSWORD_DEFAULT));
                    $stmt->bindParam(':id', $user['id']);
                    $stmt->execute();
                    
                    // Set the token in cookies for 30 days
                    setcookie('remember_me_token', $token, time() + (86400 * 30), "/");
                    setcookie('remember_me_email', $email, time() + (86400 * 30), "/");
                } else {
                    // Clear cookies if "Remember Me" is not checked
                    setcookie('remember_me_token', '', time() - 3600, "/");
                    setcookie('remember_me_email', '', time() - 3600, "/");
                }

                // Role-based redirection
                switch ($user['role']) {
                    case 'admin':
                        header("Location: dashboard/index.php");
                        break;
                    case 'salesadmin':
                        header("Location: admindashboard/index.php");
                        break;
                    case 'sales':
                        header("Location: salesdashboard/index.php");
                        break;
                    default:
                        // In case the role doesn't match any case, log out
                        session_unset();
                        session_destroy();
                        $error = "Invalid role.";
                }
                exit;
            } else {
                // Invalid password
                $error = "Invalid password.";
            }
        } else {
            // Invalid email
            $error = "Invalid email or account inactive.";
        }
    } else {
        $error = "Email and password must be provided.";
    }
}

// Check if cookies are set and auto-login the user
if (isset($_COOKIE['remember_me_token']) && isset($_COOKIE['remember_me_email'])) {
    $email = $_COOKIE['remember_me_email'];
    $token = $_COOKIE['remember_me_token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND active = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($token, $user['remember_token'])) {
        // Token is valid, auto-login the user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        // Role-based redirection
        switch ($user['role']) {
            case 'admin':
                header("Location: dashboard/index.php");
                break;
            case 'salesadmin':
                header("Location: admindashboard/index.php");
                break;
            case 'sales':
                header("Location: salesdashboard/index.php");
                break;
            default:
                session_unset();
                session_destroy();
                $error = "Invalid role.";
        }
        exit;
    } else {
        // Invalid token or inactive user
        setcookie('remember_me_token', '', time() - 3600, "/");
        setcookie('remember_me_email', '', time() - 3600, "/");
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>

    <main>

       <!-- <div class='light x1'></div>
    <div class='light x2'></div>
    <div class='light x3'></div>
    <div class='light x4'></div>
    <div class='light x5'></div>
    <div class='light x6'></div>
    <div class='light x7'></div>
    <div class='light x8'></div>
    <div class='light x9'></div>-->

   <div class="top-container">
            <div class="content-wrapper">
                <img src="logo.png" class="loginlogo" alt="logo"/>
                
                <center><form method="post" action="" class="loginform">

                    <center><h2>Login</h2></center><br>
                    <label class="label2" for="email"><b>Email</b></label>
                    <div class="input-wrapper">
                        <input class="input" id="email" type="email" name="email" placeholder="" required>
                        
                    </div><br>
                    <label class="label2" for="password"><b>Password</b></label>
                    <div class="input-wrapper">
                        <input class="input" id="password" type="password" name="password" placeholder="" required>
                       
                    </div>
                    <div class="input-wrapper">
                        <label class="checkbox" style="color: black;">
                            <input type="checkbox" value="remember-me" id="remember_me" class="rem"> Remember me
                        </label>

                    </div>
                    <div class="input-wrapper">
                    <a href="forgotpassword.php" 
                     class="label2" 
                        style="color: blue; text-decoration: underline; transition: font-size 0.2s;" 
                        onmouseover="this.style.fontSize='105%';" 
                        onmouseout="this.style.fontSize='100%';">Forgot Password</a>
                    </div>
                    <button type="submit">Sign In</button>
                </form></center>
                    
                    
            </div>
        </div>

        <?php if ($error): ?>
            <script>
                    alert("<?php echo addslashes($error); ?>");
</script>
                <?php endif; ?>
 </main>
</body>
</html>
