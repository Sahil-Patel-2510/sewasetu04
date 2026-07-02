<?php
include "config/db.php";
include "send_otp.php";

$setup_message = "";
$setup_type = "";
$api_key_configured = false;

// Check current API key status
$send_otp_content = file_get_contents('send_otp.php');
if(strpos($send_otp_content, 'YOUR_API_KEY_HERE') === false) {
    $api_key_configured = true;
}

// Test API key
if(isset($_POST['test_otp'])) {
    $api_key = $_POST['api_key'];
    
    if(empty($api_key)) {
        $setup_message = "Please enter API key";
        $setup_type = "error";
    } else {
        if(testOTPAPI($api_key)) {
            $setup_message = "✓ API Key is valid! Ready to use.";
            $setup_type = "success";
            
            // Update send_otp.php with the API key
            $send_otp_content = file_get_contents('send_otp.php');
            $send_otp_content = str_replace('$api_key = "YOUR_API_KEY_HERE";', '$api_key = "' . addslashes($api_key) . '";', $send_otp_content);
            file_put_contents('send_otp.php', $send_otp_content);
            $api_key_configured = true;
        } else {
            $setup_message = "✗ API Key is invalid or Fast2SMS is unreachable. Check your key and try again.";
            $setup_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>OTP Setup - Seva Setu</title>
<link rel="stylesheet" href="style.css">
<style>
    body {
        background: linear-gradient(135deg, #f4f9ff 0%, #e3f2ff 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .setup-container {
        max-width: 600px;
        margin: 50px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    h1 {
        color: #1f2b3a;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .subtitle {
        color: #666;
        text-align: center;
        margin-bottom: 30px;
        font-size: 14px;
    }
    
    .status {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: bold;
    }
    
    .status.configured {
        background: #d1fae5;
        color: #064e3b;
        border: 2px solid #6ee7b7;
    }
    
    .status.not-configured {
        background: #fef2f2;
        color: #991b1b;
        border: 2px solid #fecaca;
    }
    
    .setup-message {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    
    .setup-message.success {
        background: #d1fae5;
        color: #064e3b;
        border: 1px solid #6ee7b7;
    }
    
    .setup-message.error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .steps {
        background: #f8fbff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #2f78ff;
    }
    
    .steps h3 {
        color: #2f78ff;
        margin-top: 0;
    }
    
    .steps ol {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .steps li {
        margin: 8px 0;
        color: #333;
        line-height: 1.6;
    }
    
    .steps a {
        color: #2f78ff;
        text-decoration: none;
        font-weight: bold;
    }
    
    .steps a:hover {
        text-decoration: underline;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2e3d52;
    }
    
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #d2dae5;
        background: #f8fbff;
        font-size: 1rem;
        color: #1f2b3a;
        box-sizing: border-box;
    }
    
    input[type="text"]:focus, input[type="password"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.18);
        border-color: #2f78ff;
    }
    
    button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #2f78ff;
        color: white;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    button:hover {
        background: #255fd1;
    }
    
    .info-box {
        background: #e3f2ff;
        border-left: 4px solid #2f78ff;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
        color: #1f2b3a;
    }
    
    .info-box strong {
        color: #2f78ff;
    }
    
    .success-link {
        text-align: center;
        margin-top: 20px;
    }
    
    .success-link a {
        color: #2f78ff;
        text-decoration: none;
        font-weight: bold;
    }
    
    .success-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="setup-container">
    <h1>🔐 OTP Setup</h1>
    <p class="subtitle">Configure SMS for Worker Registration</p>
    
    <?php if($api_key_configured): ?>
    <div class="status configured">
        ✓ OTP System is READY! You can now register workers with OTP verification.
    </div>
    <div class="success-link">
        <a href="register2.php">Go to Worker Registration →</a>
    </div>
    <?php else: ?>
    <div class="status not-configured">
        ⚠ OTP System NOT Configured. Please set up your API key below.
    </div>
    <?php endif; ?>
    
    <?php if(!empty($setup_message)): ?>
    <div class="setup-message <?php echo $setup_type; ?>">
        <?php echo $setup_message; ?>
    </div>
    <?php endif; ?>
    
    <div class="steps">
        <h3>📱 Get Your Free Fast2SMS API Key</h3>
        <ol>
            <li>Visit <a href="https://www.fast2sms.com/" target="_blank">https://www.fast2sms.com/</a></li>
            <li>Click "Sign Up" and create a free account</li>
            <li>Go to <strong>Dashboard → Developer API</strong></li>
            <li>You'll see your <strong>API Key</strong> - Copy it (looks like: 8ab1234cd5ef67gh89ij12kl34mn567o)</li>
            <li>Paste the key below and click "Test & Save"</li>
        </ol>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="api_key">Paste Your Fast2SMS API Key Here:</label>
            <input type="text" id="api_key" name="api_key" placeholder="Your API key (e.g., 8ab1234cd5ef...)" required>
        </div>
        <button type="submit" name="test_otp">Test & Save API Key</button>
    </form>
    
    <div class="info-box">
        <strong>💡 What is Fast2SMS?</strong><br>
        Fast2SMS is a free/affordable SMS service for India. Your OTP messages will be sent to real mobile numbers within seconds.
        <br><br>
        <strong>⏱ Free Credits:</strong> Fast2SMS gives you free SMS credits to test. After that, you can purchase affordable plans.
    </div>
    
    <?php if(!$api_key_configured): ?>
    <div class="info-box" style="margin-top: 15px; background: #fff3cd; border-color: #ffc107; color: #856404;">
        <strong>⚠ Impact:</strong> Without OTP setup, the registration form will show an error when users try to register. Please complete this setup first.
    </div>
    <?php endif; ?>
    
</div>

</body>
</html>
