<?php
session_start();
include "config/db.php";
include "config/razorpay.php";

// Check if customer is logged in
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

$customer_name = $_SESSION['username'];

// Pricing function based on work type
function getPriceForWorkType($workType) {
    $workType = strtolower(trim($workType));
    
    $pricing = array(
        // Electrical Work
        'electrical repair' => 500,
        'electrical work' => 500,
        'wiring installation' => 450,
        'short circuit fix' => 600,
        'panel repair' => 550,
        'socket & switch installation' => 300,
        'light installation' => 250,
        'fan repair' => 400,
        'appliance repair' => 500,
        'maintenance & inspection' => 350,
        
        // Plumbing Work
        'pipe installation' => 200,
        'pipe repair' => 250,
        'water leak fix' => 250,
        'tap installation' => 150,
        'drainage repair' => 300,
        'water filter installation' => 180,
        'valve repair' => 200,
        'bathroom fitting' => 400,
        'maintenance' => 300,
        'emergency repair' => 350,
        
        // Painting Work
        'wall painting' => 300,
        'interior painting' => 350,
        'exterior painting' => 500,
        'texture coating' => 400,
        'door & window painting' => 200,
        'ceiling painting' => 250,
        'waterproofing paint' => 450,
        'maintenance & touch-up' => 200,
        'color consultation' => 150,
        'wall cleaning & prep' => 180,
        
        // Carpentry Work
        'wood work' => 400,
        'door installation' => 350,
        'window frame work' => 450,
        'cupboard installation' => 600,
        'shelf installation' => 300,
        'furniture repair' => 350,
        'wooden floor work' => 500,
        'design & estimation' => 250,
        'finishing work' => 300,
        
        // AC Work
        'ac installation' => 800,
        'ac repair' => 600,
        'gas refill' => 400,
        'cooling not working' => 550,
        'water leakage' => 450,
        'noise issues' => 500,
        'filter cleaning' => 200,
        'compressor repair' => 700,
        'emergency service' => 650,
        
        // Appliance Repair
        'washing machine repair' => 450,
        'refrigerator repair' => 500,
        'microwave repair' => 350,
        'dishwasher repair' => 400,
        'oven repair' => 450,
        'water heater repair' => 400,
        'gas stove repair' => 300,
        
        // General Services
        'general repair' => 300,
        'installation' => 250,
        'inspection' => 200,
        'consultation' => 150,
        'custom service' => 400,
        'follow-up work' => 200,
        'quality check' => 150,
        'other' => 300,
        
        // Mason Work
        'wall construction' => 600,
        'brick laying' => 550,
        'concrete work' => 500,
        'tile work' => 400,
        'floor leveling' => 350,
        'waterproofing' => 450,
        'foundation work' => 800,
        'restoration' => 700,
        'custom design work' => 600,
        
        // Other Services
        'pest control' => 400,
        'gardening' => 250,
        'cleaning' => 200,
        'house cleaning' => 250,
        'lock repair' => 150,
        'geyser repair' => 300,
        'chimney cleaning' => 200,
        'chimney repair' => 400,
        'mobile repair' => 300,
        'laptop repair' => 500,
        'tv repair' => 400,
        'furniture repair' => 350,
        'window repair' => 250,
    );
    
    // Exact match first
    if(isset($pricing[$workType])){
        return $pricing[$workType];
    }
    
    // Partial match
    foreach($pricing as $type => $price){
        if(strpos($workType, $type) !== false || strpos($type, $workType) !== false){
            return $price;
        }
    }
    
    // Default price if no match found
    return 300;
}

// Get form data
$work_id = isset($_POST['work_id']) ? (int)$_POST['work_id'] : 0;
$amount_from_form = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
$payment_method = isset($_POST['payment_method']) ? htmlspecialchars($_POST['payment_method']) : '';
$payment_timing = isset($_POST['payment_timing']) ? htmlspecialchars($_POST['payment_timing']) : 'before_work';

if(!$work_id || !$payment_method){
    die("Invalid payment request");
}

// Verify work exists and belongs to customer
$query = "SELECT * FROM requests WHERE id='$work_id' AND customer_name='$customer_name'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    die("Work not found!");
}

$work = mysqli_fetch_assoc($result);

// Recalculate amount based on work type (security: don't trust form data)
$amount = getPriceForWorkType($work['work']);

// Generate transaction ID
$transaction_id = "TXN" . time() . rand(1000, 9999);
$payment_date = date('Y-m-d H:i:s');

// Handle different payment methods
$payment_status = 'pending';
$payment_details = '';

switch($payment_method) {
    case 'razorpay':
        // Razorpay payment processing
        // In real implementation, this would call Razorpay API
        // For now, we'll mark as pending until webhook confirmation
        $payment_status = 'pending';
        $payment_details = json_encode([
            'method' => 'Razorpay',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ]);
        
        // Redirect to Razorpay payment gateway
        $_SESSION['pending_payment'] = [
            'work_id' => $work_id,
            'amount' => $amount,
            'transaction_id' => $transaction_id,
            'method' => 'razorpay'
        ];
        header("Location: razorpay_gateway.php?txn_id=" . $transaction_id);
        break;

    case 'wallet':
        // Wallet payment processing
        $payment_status = 'completed';
        $payment_details = json_encode([
            'method' => 'Wallet',
            'wallet_pin_verified' => true
        ]);
        break;

    case 'netbanking':
        // Net Banking payment processing
        $payment_status = 'pending';
        $payment_details = json_encode([
            'method' => 'Net Banking',
            'bank' => $_POST['bank_name'] ?? ''
        ]);
        break;

    case 'qr':
        // QR payment confirmation
        if(!isset($_POST['qr_confirm'])){
            die("Please confirm that you have scanned the UPI QR code to pay.");
        }
        $payment_status = 'completed';
        $payment_details = json_encode([
            'method' => 'UPI QR Payment',
            'upi_id' => UPI_ID,
            'amount' => $amount,
            'confirmation' => true
        ]);
        break;

    case 'cod':
        // Cash on Delivery
        if(!isset($_POST['cod_confirm'])){
            die("Please confirm Cash on Site payment.");
        }
        $payment_status = 'pending';
        $payment_details = json_encode([
            'method' => 'Cash on Site',
            'status' => 'waiting_for_collection'
        ]);
        break;

    default:
        die("Invalid payment method");
}

$payment_data = json_decode($payment_details, true);
if(!is_array($payment_data)) {
    $payment_data = [];
}
$payment_data['timing'] = $payment_timing;
$payment_details = json_encode($payment_data);

// Create payment record in database (if table doesn't exist, create it first)
$create_table_query = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    worker_name VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL,
    payment_details LONGTEXT,
    payment_date DATETIME,
    confirmed_date DATETIME NULL,
    receipt_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_id) REFERENCES requests(id)
)";

mysqli_query($conn, $create_table_query);

// Insert payment record
$insert_query = "INSERT INTO payments 
(work_id, customer_name, worker_name, amount, transaction_id, payment_method, payment_status, payment_details, payment_date)
VALUES 
('$work_id', '$customer_name', '{$work['worker_name']}', '$amount', '$transaction_id', '$payment_method', '$payment_status', '$payment_details', '$payment_date')";

if(!mysqli_query($conn, $insert_query)){
    die("Error processing payment: " . mysqli_error($conn));
}

// Create notifications table if it doesn't exist
$create_notifications_table = "CREATE TABLE IF NOT EXISTS worker_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(50),
    work_id INT,
    payment_id INT,
    read_status INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_id) REFERENCES requests(id)
)";

mysqli_query($conn, $create_notifications_table);

// Send notification to worker if payment timing is after work
if($payment_timing === 'after_work'){
    $notification_message = "Payment will be received after work completion for request #$work_id. Amount: ₹$amount";
    $notification_type = 'after_work_payment';
    
    $notify_query = "INSERT INTO worker_notifications (worker_name, message, notification_type, work_id, read_status)
    VALUES ('{$work['worker_name']}', '$notification_message', '$notification_type', '$work_id', 0)";
    
    mysqli_query($conn, $notify_query);
}

// If payment is completed, redirect to success
if($payment_method === 'wallet' || $payment_method === 'qr'){
    header("Location: payment_success.php?txn_id=" . $transaction_id);
    exit;
}

// If payment is still pending, show a pending confirmation page
if($payment_method === 'cod' || $payment_method === 'netbanking' || $payment_method === 'razorpay'){
    header("Location: payment_pending.php?txn_id=" . $transaction_id);
    exit;
}

// Otherwise, user will be redirected to gateway above
?>
