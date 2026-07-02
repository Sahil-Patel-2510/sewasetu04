<?php

// Razorpay configuration
// Set your Razorpay credentials here or via environment variables.
$keyId = getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_YOUR_KEY_ID';
$keySecret = getenv('RAZORPAY_KEY_SECRET') ?: 'YOUR_KEY_SECRET';
$paymentLink = getenv('RAZORPAY_PAYMENT_LINK') ?: 'https://razorpay.me/@sahilkumarjitendrakumarpatel';

define('RAZORPAY_KEY_ID', $keyId);
define('RAZORPAY_KEY_SECRET', $keySecret);
define('RAZORPAY_CURRENCY', 'INR');
define('RAZORPAY_PAYMENT_LINK', $paymentLink);

define('UPI_ID', getenv('UPI_ID') ?: 'yourupi@bank');
define('UPI_NAME', getenv('UPI_NAME') ?: 'Seva Setu');
define('UPI_NOTE', getenv('UPI_NOTE') ?: 'Service payment via UPI');
