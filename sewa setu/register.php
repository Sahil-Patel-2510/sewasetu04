<?php
session_start();
include "config/db.php";
include "send_otp.php";

function normalizeMobile($mobile) {
    $clean = preg_replace('/\D+/', '', $mobile);
    if(strlen($clean) === 10) {
        return '+91' . $clean;
    }
    if(strlen($clean) === 12 && substr($clean, 0, 2) === '91') {
        return '+' . $clean;
    }
    return false;
}

$message = "";
$message_type = "";
$show_otp_form = false;
$mobile_for_otp = "";

if(isset($_POST['verify_otp'])) {
    if(isset($_SESSION['otp'], $_SESSION['temp_mobile']) && $_SESSION['otp'] == trim($_POST['otp'])) {
        $mobile = $_SESSION['temp_mobile'];
        $username = $_SESSION['temp_username'];
        $email = $_SESSION['temp_email'];
        $address = $_SESSION['temp_address'];
        $passwordHash = $_SESSION['temp_password'];

        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, mobile, address, password) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $mobile, $address, $passwordHash);

        if(mysqli_stmt_execute($stmt)) {
            $message = "Registration successful. You can login now.";
            $message_type = "success";
            unset($_SESSION['otp'], $_SESSION['temp_mobile'], $_SESSION['temp_username'], $_SESSION['temp_email'], $_SESSION['temp_address'], $_SESSION['temp_password']);
        } else {
            $message = "Registration failed: " . mysqli_stmt_error($stmt);
            $message_type = "error";
            $show_otp_form = true;
            $mobile_for_otp = $mobile;
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Invalid OTP. Please try again.";
        $message_type = "error";
        $show_otp_form = true;
        $mobile_for_otp = $_SESSION['temp_mobile'] ?? "";
    }
}

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if($username === '' || $email === '' || $mobile === '' || $address === '' || $password === '') {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } elseif(!($normalized_mobile = normalizeMobile($mobile))) {
        $message = "Mobile number must be 10 digits and may include +91 prefix.";
        $message_type = "error";
    } else {
        $mobile = mysqli_real_escape_string($conn, $normalized_mobile);

        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Username already exists. Please choose a different one.";
            $message_type = "error";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE mobile = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $mobile);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) > 0) {
                $message = "Mobile number already exists. Please use a different mobile number.";
                $message_type = "error";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['temp_username'] = $username;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_mobile'] = $mobile;
                $_SESSION['temp_address'] = $address;
                $_SESSION['temp_password'] = password_hash($password, PASSWORD_DEFAULT);

                $otp_result = sendOTP($mobile, $otp);

            if($otp_result['success']) {
                $message = "OTP sent to your mobile number ending with " . substr($mobile, -4) . ".";
                $message_type = "success";
                $show_otp_form = true;
                $mobile_for_otp = $mobile;
            } else {
                $message = "Failed to send OTP: " . $otp_result['message'];
                $message_type = "error";
            }
        }
    }
}
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Seva Setu - Register</title>
<link rel="stylesheet" href="style.css">
<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f4f9ff 0%, #e3f2ff 100%);
    color: #333;
}

.login-container {
    width: min(460px, 95%);
    background: #ffffff;
    padding: 34px 30px;
    border-radius: 24px;
    box-shadow: 0 28px 70px rgba(37, 78, 124, 0.14);
    text-align: left;
}

h2 {
    margin-top: 0;
    margin-bottom: 24px;
    color: #1f3044;
    font-size: 2rem;
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
}

.signup-link a {
    color: #2f78ff;
    font-weight: 700;
    text-decoration: none;
}

.signup-link a:hover {
    text-decoration: underline;
}

.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 18px;
    border-radius: 12px;
    background: #2f78ff;
    color: white;
    text-decoration: none;
    font-weight: 700;
    margin-top: 14px;
    transition: background 0.2s ease, transform 0.2s ease;
}

.button:hover {
    background: #255fd1;
    transform: translateY(-1px);
}

.login-form input:focus,
.login-form select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.18);
    border-color: #2f78ff;
}
</style>
</head>

<body>

<div class="login-container">
    <h2>User Registration</h2>
    <?php if(!empty($message)): ?>
        <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if(!$show_otp_form): ?>
    <form method="POST" class="login-form">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="example@mail.com">

        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" pattern="(\+91)?[0-9]{10}" maxlength="13" minlength="10" required placeholder="Enter +91XXXXXXXXXX or 10 digits">

        <label for="address">Address</label>
        <input type="text" id="address" name="address" required placeholder="Enter your full address">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="register">Register</button>
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

    <p class="signup-link">Already have an account? <a href="login.php">Login</a></p>
    <a class="button" href="login.php">Go to Login</a>
</div>

</body>
</html>