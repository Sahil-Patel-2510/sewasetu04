# SMS OTP Integration Setup Guide

## Overview
The worker registration form now sends real OTP to the user's mobile number for verification.

## Available SMS Providers

### 1. **Fast2SMS** (Recommended for India)
- **Website**: https://www.fast2sms.com/
- **Cost**: Affordable, pay-as-you-go
- **Speed**: Fast delivery

**Setup Steps:**
1. Register at https://www.fast2sms.com/
2. Go to Dashboard → API Section
3. Copy your authentication key
4. Open `send_otp.php`
5. Replace `YOUR_FAST2SMS_API_KEY` with your actual key
6. The function is already configured to use Fast2SMS by default

### 2. **MSG91**
- **Website**: https://www.msg91.com/
- **Cost**: Competitive pricing
- **Speed**: Reliable

**Setup Steps:**
1. Register at https://www.msg91.com/
2. Get your Auth Key from the dashboard
3. Get your Sender ID (alphanumeric)
4. Open `send_otp.php`
5. Update `sendOTPMSG91()` function with your credentials
6. In `register2.php`, change line that says `$otp_result = sendOTP($mobile, $otp);` 
   to `$otp_result = sendOTPMSG91($mobile, $otp);`

### 3. **Twilio**
- **Website**: https://www.twilio.com/
- **Cost**: Reliable, international support
- **Speed**: Fast

**Setup Steps:**
1. Sign up at https://www.twilio.com/
2. Create a project and get a Twilio phone number
3. Get your Account SID and Auth Token
4. Open `send_otp.php`
5. Update `sendOTPTwilio()` function with credentials
6. In `register2.php`, change the sendOTP call to `sendOTPTwilio()`

## Current Implementation in register2.php

```php
// Line that calls the OTP sending function:
$otp_result = sendOTP($mobile, $otp); // Using Fast2SMS
```

To switch providers, change this line to:
- `sendOTPMSG91($mobile, $otp)` for MSG91
- `sendOTPTwilio($mobile, $otp)` for Twilio

## Testing

### Before Real SMS Setup:
For testing purposes, you can temporarily modify `register2.php` to view the OTP:

```php
// Add this line temporarily for debugging:
error_log("OTP sent: " . $otp); // Check in PHP error logs
```

### After Configuration:
1. Fill the registration form
2. Click "Register"
3. If OTP sends successfully: "OTP sent to your mobile ending with XXXX"
4. Check your actual SMS inbox
5. Enter the OTP and click "Verify OTP"
6. Registration completes if OTP is correct

## File Structure

```
login system/
├── register2.php          (Main registration form with OTP)
├── send_otp.php          (SMS sending functions)
├── config/
│   └── db.php           (Database config)
└── uploads/             (For file uploads if needed)
```

## Security Notes

1. **Never commit API keys to version control**
2. Keep API keys in `send_otp.php` or move to a separate config file
3. OTP expires after verification (improved in future versions)
4. Add OTP expiration time (currently valid until session ends)
5. Limit OTP resend attempts to prevent abuse

## Troubleshooting

### OTP not sending?
- Check if cURL is enabled in PHP
- Verify API key is correct
- Check SMS service balance
- Check mobile number format (+91XXXXXXXXXX for India)

### OTP sending but not receiving?
- Check SMS service status dashboard
- Verify number is in correct format
- Check if your number is blacklisted
- Try with a different number

### Session issues?
- Ensure `session_start()` is at the top of register2.php
- Check if sessions folder is writable

## Next Steps

1. Choose your SMS provider
2. Get API credentials
3. Update `send_otp.php` with credentials
4. Update `register2.php` to use correct function
5. Test with a real number
