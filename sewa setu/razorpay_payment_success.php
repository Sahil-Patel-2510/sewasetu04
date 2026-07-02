<?php
session_start();
include "config/db.php";
include "config/razorpay.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if(!isset($_POST['request_id'], $_POST['razorpay_payment_id'], $_POST['razorpay_order_id'], $_POST['razorpay_signature'])){
    die("Invalid payment response.");
}

$request_id = (int)$_POST['request_id'];
$payment_id = $_POST['razorpay_payment_id'];
$order_id = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];
$customer_name = $_SESSION['username'];

$payload = $order_id . '|' . $payment_id;
$expected_signature = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);

if($expected_signature !== $signature){
    die("Payment verification failed.");
}

$query = "SELECT * FROM requests WHERE id='$request_id' AND customer_name='$customer_name'";
$result = mysqli_query($conn, $query);
if(!$result || mysqli_num_rows($result) == 0){
    die("Request not found.");
}

$request = mysqli_fetch_assoc($result);

function getPriceForSingleWork($workType) {
    $workType = strtolower(trim($workType));
    $pricing = array(
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
        'wood work' => 400,
        'door installation' => 350,
        'window frame work' => 450,
        'cupboard installation' => 600,
        'shelf installation' => 300,
        'furniture repair' => 350,
        'wooden floor work' => 500,
        'design & estimation' => 250,
        'finishing work' => 300,
        'ac installation' => 800,
        'ac repair' => 600,
        'gas refill' => 400,
        'cooling not working' => 550,
        'water leakage' => 450,
        'noise issues' => 500,
        'filter cleaning' => 200,
        'compressor repair' => 700,
        'emergency service' => 650,
        'washing machine repair' => 450,
        'refrigerator repair' => 500,
        'microwave repair' => 350,
        'dishwasher repair' => 400,
        'oven repair' => 450,
        'water heater repair' => 400,
        'gas stove repair' => 300,
        'general repair' => 300,
        'installation' => 250,
        'inspection' => 200,
        'consultation' => 150,
        'custom service' => 400,
        'follow-up work' => 200,
        'quality check' => 150,
        'other' => 300,
        'wall construction' => 600,
        'brick laying' => 550,
        'concrete work' => 500,
        'tile work' => 400,
        'floor leveling' => 350,
        'waterproofing' => 450,
        'foundation work' => 800,
        'restoration' => 700,
        'custom design work' => 600,
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

    if(isset($pricing[$workType])){
        return $pricing[$workType];
    }
    foreach($pricing as $key => $price){
        if(strpos($workType, $key) !== false || strpos($key, $workType) !== false){
            return $price;
        }
    }
    return 0;
}

function calculateAmountForWork($workString) {
    $parts = array_map('trim', explode(',', $workString));
    $total = 0;
    foreach($parts as $part){
        if($part === '') continue;
        $total += getPriceForSingleWork($part);
    }
    return $total;
}

$amount = calculateAmountForWork($request['work']);

$transaction_id = $payment_id;
$payment_date = date('Y-m-d H:i:s');

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

$payment_details = json_encode(array(
    'method' => 'Razorpay',
    'order_id' => $order_id,
    'razorpay_signature' => $signature
));

$insert_query = "INSERT INTO payments 
(work_id, customer_name, worker_name, amount, transaction_id, payment_method, payment_status, payment_details, payment_date)
VALUES 
('$request_id', '$customer_name', '{$request['worker_name']}', '$amount', '$transaction_id', 'razorpay', 'completed', '$payment_details', '$payment_date')";

if(!mysqli_query($conn, $insert_query)){
    die("Error saving payment: " . mysqli_error($conn));
}

header("Location: payment_success.php?txn_id=" . urlencode($transaction_id));
exit;
