<?php
session_start();
include "config/db.php";

$message = "";
$message_type = "";
$can_reset = false;
$user_id = null;
$user_table = 'users';

if(isset($_GET['user_id']) && isset($_GET['table'])){
    $user_id = (int)$_GET['user_id'];
    $allowed_tables = ['users', 'workers'];
    $user_table = in_array($_GET['table'], $allowed_tables, true) ? $_GET['table'] : 'users';
    
    // Verify user exists in the selected table
    $query = "SELECT id, username FROM $user_table WHERE id=$user_id LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if($result && mysqli_num_rows($result) == 1){
        $can_reset = true;
    } else {
        $message = "Invalid user. Please request a new password reset.";
        $message_type = "error";
    }
} else {
    $message = "Invalid request. Please use the password reset link sent to your mobile.";
    $message_type = "error";
}

if(isset($_POST['reset_password']) && $can_reset){
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($new_password) || empty($confirm_password)){
        $message = "All fields are required.";
        $message_type = "error";
    } elseif($new_password !== $confirm_password){
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif(strlen($new_password) < 6){
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update database with new password in the correct table
        $update_query = "UPDATE $user_table SET password='$password_hash' WHERE id=$user_id";
        
        if(mysqli_query($conn, $update_query)){
            // Verify the update was successful
            $verify_query = "SELECT password FROM $user_table WHERE id=$user_id LIMIT 1";
            $verify_result = mysqli_query($conn, $verify_query);
            
            if($verify_result && mysqli_num_rows($verify_result) == 1){
                // Clear any cached data for this user
                if(isset($_SESSION['forget_otp'])){
                    unset($_SESSION['forget_otp']);
                }
                if(isset($_SESSION['temp_user_id'])){
                    unset($_SESSION['temp_user_id']);
                }
                if(isset($_SESSION['temp_user_table'])){
                    unset($_SESSION['temp_user_table']);
                }
                if(isset($_SESSION['temp_mobile_forget'])){
                    unset($_SESSION['temp_mobile_forget']);
                }
                
                $message = "Password has been reset successfully. You can now login with your new password.";
                $message_type = "success";
                $can_reset = false;
            } else {
                $message = "Error verifying password update. Please try again.";
                $message_type = "error";
            }
        } else {
            $message = "Database error: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password - Seva Setu</title>
<link rel="stylesheet" href="style.css">
<style>
body{
    margin: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f4f9ff 0%, #e3f2ff 100%);
}

.login-container {
    width: min(460px, 95%);
    background: #ffffff;
    padding: 34px 30px;
    border-radius: 24px;
    box-shadow: 0 28px 70px rgba(37, 78, 124, 0.14);
}

h2 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #1f3044;
    font-size: 2rem;
}

.subtitle {
    color: #666;
    margin-bottom: 24px;
    font-size: 0.95rem;
}

p {
    margin: 0;
}

.message {
    margin-bottom: 20px;
    padding: 14px 16px;
    border-radius: 12px;
    font-weight: 600;
}

.message.success {
    background: #d1fae5;
    color: #064e3b;
    border: 1px solid #6ee7b7;
}

.message.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.login-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2e3d52;
}

.login-form input {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 18px;
    border-radius: 12px;
    border: 1px solid #d2dae5;
    background: #f8fbff;
    font-size: 1rem;
    color: #1f2b3a;
    box-sizing: border-box;
}

.login-form input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.18);
    border-color: #2f78ff;
}

.login-form button {
    width: 100%;
    padding: 14px 16px;
    border: none;
    border-radius: 12px;
    background: #2f78ff;
    color: #ffffff;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s ease;
}

.login-form button:hover {
    background: #255fd1;
}

.signup-link {
    margin-top: 18px;
    font-size: 0.95rem;
    text-align: center;
}

.signup-link a {
    color: #2f78ff;
    font-weight: 700;
    text-decoration: none;
}

.signup-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="login-container">
    <h2>Reset Password</h2>
    <p class="subtitle">Enter your new password below</p>
    
    <?php if(!empty($message)): ?>
        <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if($can_reset): ?>
    <form method="POST" class="login-form">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required placeholder="Enter new password" minlength="6">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password" minlength="6">

        <button type="submit" name="reset_password">Reset Password</button>
    </form>
    <?php else: ?>
        <?php if($message_type === "success"): ?>
            <p class="signup-link"><a href="login.php">Go to Login →</a></p>
        <?php endif; ?>
    <?php endif; ?>

    <p class="signup-link"><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>
