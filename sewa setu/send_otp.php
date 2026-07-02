<?php
// SMS OTP Sending Configuration using Fast2SMS API
// This file handles sending OTP to mobile numbers

function sendOTP($mobile, $otp) {
    // Fast2SMS API Configuration
    // Get your FREE API key from https://www.fast2sms.com/
    // Dashboard → Developer API → Copy your Authorization key
    
    $api_key = "USov3a19WxhGc2jHI4dEnMZCXVADprJ7zN6OFflTYKmwusRePyqBdfku6lUDLWSJIZCH9s0VRgTEcp5x"; // Fast2SMS API key
    
    // Check if API key is configured
    if($api_key === "YOUR_API_KEY_HERE" || empty($api_key)) {
        return array('success' => false, 'message' => 'SMS API not configured. Please configure your Fast2SMS API key.');
    }
    
    // Message format
    $message = "Your Seva Setu OTP is: " . $otp . ". Do not share this OTP. Valid for 10 minutes.";
    
    try {
        // Fast2SMS API endpoint - Simple GET request
        $api_url = "https://www.fast2sms.com/dev/bulkV2";
        
        // Build query parameters
        $params = array(
            'authorization' => $api_key,
            'route' => 'q',
            'message' => $message,
            'language' => 'english',
            'flash' => '0',
            'numbers' => $mobile
        );
        
        // Build the full URL
        $url = $api_url . "?" . http_build_query($params);
        
        // Use file_get_contents for simple GET request
        $response = @file_get_contents($url);
        
        if($response === false) {
            // If file_get_contents fails, try cURL
            return sendOTPViaCurl($mobile, $otp, $api_key);
        }
        
        $result = json_decode($response, true);
        
        // Check if SMS was sent successfully
        if(isset($result['return']) && $result['return'] == true) {
            return array('success' => true, 'message' => 'OTP sent successfully');
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            return array('success' => false, 'message' => 'Failed to send OTP: ' . $error_msg);
        }
    } catch(Exception $e) {
        return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
    }
}

// Backup function using cURL if file_get_contents fails
function sendOTPViaCurl($mobile, $otp, $api_key) {
    $message = "Your Seva Setu OTP is: " . $otp . ". Do not share this OTP. Valid for 10 minutes.";
    
    try {
        $api_url = "https://www.fast2sms.com/dev/bulkV2";
        
        $params = array(
            'authorization' => $api_key,
            'route' => 'q',
            'message' => $message,
            'language' => 'english',
            'flash' => '0',
            'numbers' => $mobile
        );
        
        $url = $api_url . "?" . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if($http_code == 200 && isset($result['return']) && $result['return'] == true) {
            return array('success' => true, 'message' => 'OTP sent successfully');
        } else {
            $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
            return array('success' => false, 'message' => 'Failed: ' . $error_msg);
        }
    } catch(Exception $e) {
        return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
    }
}

// Test function to verify API key is working
function testOTPAPI($api_key) {
    $mobile = "9999999999"; // Test number
    $otp = "123456";
    
    try {
        $api_url = "https://www.fast2sms.com/dev/bulkV2";
        
        $message = "Test OTP: " . $otp;
        
        $params = array(
            'authorization' => $api_key,
            'route' => 'q',
            'message' => $message,
            'language' => 'english',
            'flash' => '0',
            'numbers' => $mobile
        );
        
        $url = $api_url . "?" . http_build_query($params);
        
        // Try with file_get_contents first
        $response = @file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => 5))));
        
        if($response === false) {
            // Try with cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if($http_code == 200) {
                return true;
            }
            return false;
        }
        
        // Check response
        $result = json_decode($response, true);
        if(isset($result['return']) && $result['return'] == true) {
            return true;
        }
        
        return false;
    } catch(Exception $e) {
        return false;
    }
}
?>

