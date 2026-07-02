<?php
session_start();
include "config/db.php";

$message="";

if(isset($_POST['login']))
{
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result) {
        if(mysqli_num_rows($result) == 1)
        {
            $row = mysqli_fetch_assoc($result);
            $hashed_password = $row['password'];

            if(password_verify($password,$hashed_password))
            {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit;
            }
            else
            {
                $message="Invalid password.";
            }
        }
        else
        {
            $message="User not found. Please check your username.";
        }
    } else {
        $message = "Database error: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="login-container">
    <h2>User Login</h2>
    <?php if($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" class="login-form">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <p class="signup-link"><a href="forgot_password.php">Forgot Password?</a></p>
    <p class="signup-link">Don't have an account? <a href="register.php">Create one</a></p>
</div>

</body>
</html>