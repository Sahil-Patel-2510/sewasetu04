<?php
session_start();
include "config/db.php";
include "send_otp.php";

$message = "";
$message_type = "";
$show_otp_form = false;
$mobile_for_otp = "";

if(isset($_POST['verify_otp'])){
    if(isset($_SESSION['forget_otp']) && $_SESSION['forget_otp'] == trim($_POST['otp']) && isset($_SESSION['temp_user_id']) && isset($_SESSION['temp_user_table'])){
        $user_id = $_SESSION['temp_user_id'];
        $user_table = $_SESSION['temp_user_table'];
        $show_otp_form = false;
        
        header("Location: reset_password.php?user_id=" . $user_id . "&table=" . $user_table);
        exit;
    } else {
        $message = "Invalid OTP. Please try again.";
        $message_type = "error";
        $show_otp_form = true;
        $mobile_for_otp = $_SESSION['temp_mobile_forget'] ?? "";
    }
}

if(isset($_POST['send_otp'])){
    $mobile = trim($_POST['mobile']);
    $clean_mobile = preg_replace('/\D+/', '', $mobile);

    if(empty($clean_mobile)){
        $message = "Please enter your mobile number.";
        $message_type = "error";
    } elseif(strlen($clean_mobile) < 10){
        $message = "Please enter a valid mobile number.";
        $message_type = "error";
    } else {
        $last10 = substr($clean_mobile, -10);
        $escaped_last10 = mysqli_real_escape_string($conn, $last10);

        $query = "SELECT id, mobile FROM users WHERE REPLACE(REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', ''), '(', '') LIKE '%$escaped_last10' LIMIT 1";
        $result = mysqli_query($conn, $query);
        $user_table = 'users';

        if(!$result || mysqli_num_rows($result) !== 1){
            $query = "SELECT id, mobile FROM workers WHERE REPLACE(REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '+', ''), '(', '') LIKE '%$escaped_last10' LIMIT 1";
            $result = mysqli_query($conn, $query);
            $user_table = 'workers';
        }

        if($result && mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);
            $sms_mobile = '' . $last10;

            // Generate OTP
            $otp = rand(100000, 999999);

            // Store in session
            $_SESSION['forget_otp'] = $otp;
            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_user_table'] = $user_table;
            $_SESSION['temp_mobile_forget'] = $user['mobile'];

            // Send OTP to mobile
            $otp_result = sendOTP($sms_mobile, $otp);

            if($otp_result['success']){
                $message = "OTP sent to your mobile number ending with " . substr($last10, -4) . ". Check your SMS.";
                $message_type = "success";
                $show_otp_form = true;
                $mobile_for_otp = $sms_mobile;
            } else {
                $message = "Failed to send OTP: " . $otp_result['message'];
                $message_type = "error";
            }
        } else {
            $message = "Mobile number not found in our system.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password - Seva Setu</title>
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

.reset-link {
    margin-top: 15px;
    padding: 12px;
    background: #e3f2ff;
    border: 1px solid #2f78ff;
    border-radius: 8px;
    word-break: break-all;
    font-size: 0.85rem;
    color: #1f3044;
}

.reset-link-label {
    font-weight: 600;
    color: #2f78ff;
    display: block;
    margin-bottom: 8px;
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
    <h2>Forgot Password</h2>
    <p class="subtitle">Enter your mobile number to reset your password via OTP</p>
    
    <?php if(!empty($message)): ?>
        <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if(!$show_otp_form): ?>
    <form method="POST" class="login-form">
        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" required placeholder="Enter your mobile number">

        <button type="submit" name="send_otp">Send OTP</button>
    </form>
    <?php else: ?>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
    <h3>Verify OTP</h3>
    <p>We've sent a verification code to your mobile number ending with <?php echo htmlspecialchars(substr($mobile_for_otp, -4)); ?>.</p>
    <p style="color: #2f78ff; font-weight: bold;">Please enter the 6-digit OTP you received via SMS.</p>
    
    <form method="POST" class="login-form">
        <label for="otp">Enter OTP</label>
        <input type="text" id="otp" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required>
        <button type="submit" name="verify_otp">Verify OTP</button>
    </form>
    <?php endif; ?>

    <p class="signup-link">Remember your password? <a href="login.php">Login</a></p>
    <p class="signup-link">Don't have an account? <a href="register.php">Register</a></p>
</div>

</body>
</html>
