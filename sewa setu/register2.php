<?php
session_start();
include  "config/db.php";
include  "send_otp.php";

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
$otp_sent = false;

// Handle OTP verification
if(isset($_POST['verify_otp'])) {
    if(isset($_SESSION['otp']) && $_SESSION['otp'] == $_POST['otp'] && isset($_SESSION['temp_mobile'])) {
        $mobile = $_SESSION['temp_mobile'];
        
        // Get worker details from session
        $username = $_SESSION['temp_username'];
        $email = $_SESSION['temp_email'];
        $location = $_SESSION['temp_location'];
        $address = $_SESSION['temp_address'];
        $service_type = $_SESSION['temp_service_type'];
        $experience = $_SESSION['temp_experience'];
        $status = 'pending';
        $password = $_SESSION['temp_password'];
        
        // Insert into database using prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO workers(username,email,mobile,location,address,service_type,experience,password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if(!$stmt) {
            $message = "Database error: " . mysqli_error($conn);
            $message_type = "error";
        } else {
            // Bind parameters correctly - all strings except experience which is integer
            mysqli_stmt_bind_param($stmt, "ssssssis", $username, $email, $mobile, $location, $address, $service_type, $experience, $password);
            
            if(mysqli_stmt_execute($stmt)){
                $message = "Registration successful! OTP verified. You can login now.";
                $message_type = "success";
                // Clear session variables
                unset($_SESSION['otp']);
                unset($_SESSION['temp_mobile']);
                unset($_SESSION['temp_username']);
                unset($_SESSION['temp_email']);
                unset($_SESSION['temp_location']);
                unset($_SESSION['temp_address']);
                unset($_SESSION['temp_service_type']);
                unset($_SESSION['temp_experience']);
                unset($_SESSION['temp_password']);
            } else {
                $message = "Error: ".mysqli_stmt_error($stmt);
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);
        }
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
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);
    $service_type = trim($_POST['service_type']);
    $experience = (int)$_POST['experience'];
    $password = trim($_POST['password']);

    if($username === '' || $email === '' || $mobile === '' || $location === '' || $address === '' || $service_type === '' || $password === '' || $experience < 0) {
        $message = "Please complete all fields correctly.";
        $message_type = "error";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } elseif(!($normalized_mobile = normalizeMobile($mobile))) {
        $message = "Mobile number must be 10 digits and may include +91 prefix.";
        $message_type = "error";
    } else {
        $mobile = mysqli_real_escape_string($conn, $normalized_mobile);
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $location = mysqli_real_escape_string($conn, $location);
        $address = mysqli_real_escape_string($conn, $address);
        $service_type = mysqli_real_escape_string($conn, $service_type);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "SELECT id FROM workers WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Username already exists. Please choose a different username.";
            $message_type = "error";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($conn, "SELECT id FROM workers WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) > 0) {
                $message = "Email already exists. Please use a different email.";
                $message_type = "error";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                // Generate OTP
                $otp = rand(100000, 999999);

                // Store in session
                $_SESSION['otp'] = $otp;
                $_SESSION['temp_mobile'] = $mobile;
                $_SESSION['temp_username'] = $username;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_location'] = $location;
                $_SESSION['temp_address'] = $address;
                $_SESSION['temp_service_type'] = $service_type;
                $_SESSION['temp_experience'] = $experience;
                $_SESSION['temp_password'] = $passwordHash;

                // Send OTP to mobile
                $otp_result = sendOTP($mobile, $otp);

                if($otp_result['success']) {
                    $message = "OTP sent to your mobile number ending with " . substr($mobile, -4) . ". Check your SMS.";
                    $message_type = "success";
                    $show_otp_form = true;
                    $mobile_for_otp = $mobile;
                    $otp_sent = true;
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
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: black;
        }
        .message.success {
            background-color: #ccffcc;
        }
        .message.error {
            background-color: #ffcccc;
        }
        .login-form select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 12px;
            border: 1px solid #d2dae5;
            background: #f8fbff;
            font-size: 1rem;
            color: #1f2b3a;
            appearance: none; /* Remove default arrow */
            background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }
        .login-form select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.18);
            border-color: #2f78ff;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
        h3 {
            color: #1f2b3a;
            margin-top: 20px;
        }
        p {
            color: #666;
            font-size: 0.95rem;
        }
    </style>
</head>

<body>

<div class="login-container">
    <h2>Worker Registration</h2>
    <?php if(!empty($message)): ?>
        <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if(!$show_otp_form): ?>
    <form method="POST" class="login-form">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="example@mail.com">

        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" pattern="(\+91)?[0-9]{10}" maxlength="13" minlength="10" required placeholder="Enter +91XXXXXXXXXX or 10 digits">

        <label for="location">Location</label>
        <select id="location" name="location" required>
            <option value="">Select Location</option>
            <option value="Surat">Surat</option>
            <option value="Ahmedabad">Ahmedabad</option>
            <option value="Vadodara">Vadodara</option>
            <option value="Rajkot">Rajkot</option>
            <option value="Bhavnagar">Bhavnagar</option>
        </select>

        <label for="address">Address</label>
        <input type="text" id="address" name="address" required placeholder="Enter your full address">

        <label for="service_type">Service Type</label>
        <select id="service_type" name="service_type" required>
            <option value="">Select Service Type</option>
            <option value="Mechanical">Mechanical</option>
            <option value="Electrical">Electrical</option>
            <option value="Plumber">Plumber</option>
            <option value="Labor">Labor</option>
            <option value="Carpenter">Carpenter</option>
        </select>

        <label for="experience">Experience (Years)</label>
        <input type="number" id="experience" name="experience" min="0" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="register">Register</button>
    </form>
    <?php endif; ?>

    <?php if($show_otp_form): ?>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
    <h3>Verify OTP</h3>
    <p>We've sent a verification code to your mobile number ending with <?php echo substr($mobile_for_otp, -4); ?>.</p>
    <p style="color: #2f78ff; font-weight: bold;">Please enter the 6-digit OTP you received via SMS.</p>
    
    <form method="POST" class="login-form">
        <label for="otp">Enter OTP</label>
        <input type="text" id="otp" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required>
        <button type="submit" name="verify_otp">Verify OTP</button>
    </form>
    <?php endif; ?>

    <?php if($message_type === "success" && !$show_otp_form): ?>
    <p class="signup-link"><a href="login2.php">Login here</a></p>
    <?php endif; ?>

    <p class="signup-link">Already have an account? <a href="login2.php">Login</a></p>
</div>

</body>
</html>