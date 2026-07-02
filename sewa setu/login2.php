<?php
session_start();
include "config/db.php";

$message = "";

if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT id, username, password FROM workers WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                header("Location: worker.php");
                exit;
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "User not found. Please check your username.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Database error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login - Sewa Setu</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <h2>Login to Sewa Setu</h2>
    <?php if(!empty($message)): ?>
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
    <p class="signup-link">Don't have an account? <a href="register2.php">Create one</a></p>
</div>

</body>
</html>