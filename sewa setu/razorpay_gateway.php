<?php
session_start();
include "config/db.php";
include "config/razorpay.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['request_id'])){
    header("Location: view_workers.php");
    exit;
}

$request_id = (int)$_GET['request_id'];
$customer_name = $_SESSION['username'];

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
$order_id = '';
$order_error = '';
$hasRazorpayKeys = RAZORPAY_KEY_ID !== 'rzp_test_YOUR_KEY_ID' && RAZORPAY_KEY_SECRET !== 'YOUR_KEY_SECRET';

if($amount <= 0){
    $order_error = 'Unable to calculate order amount for this work. Please modify your request or contact support.';
}

if(empty($order_error) && $hasRazorpayKeys){
    $postData = array(
        'amount' => $amount * 100,
        'currency' => RAZORPAY_CURRENCY,
        'receipt' => 'receipt_' . $request_id . '_' . time(),
        'payment_capture' => 1,
        'notes' => array(
            'request_id' => $request_id,
            'customer_name' => $customer_name,
            'worker_name' => $request['worker_name']
        )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $order = json_decode($response, true);

    if($http_status === 200 && isset($order['id'])){
        $order_id = $order['id'];
    } else {
        $order_error = 'Could not create Razorpay order. Please check your API keys and internet connection.';
        if(isset($order['error']['description'])){
            $order_error .= ' (' . $order['error']['description'] . ')';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pay with Razorpay - Seva Setu</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
.container { width: 100%; max-width: 620px; background: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.12); padding: 30px; }
h1 { font-size: 28px; margin-bottom: 14px; color: #1f2937; }
p.subtitle { color: #4b5563; margin-bottom: 24px; font-size: 15px; }
.box { background: #f8fafc; border-left: 4px solid #6366f1; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.box p { margin-bottom: 10px; color: #334155; }
.button-group { display: flex; gap: 12px; flex-wrap: wrap; }
.btn { padding: 14px 22px; border-radius: 10px; border: none; cursor: pointer; font-size: 15px; font-weight: 700; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.btn-primary { background: #6366f1; color: white; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(99,102,241,0.24); }
.btn-secondary { background: #e2e8f0; color: #1f2937; }
.error { background: #fee2e2; color: #991b1b; padding: 18px; border-radius: 12px; margin-bottom: 20px; }
</style>
</head>
<body>
<div class="container">
    <h1>Pay with Razorpay</h1>
    <p class="subtitle">Complete your payment for this service request to confirm your order.</p>

    <?php if($order_error): ?>
        <div class="error"><?php echo htmlspecialchars($order_error); ?></div>
        <a href="view_workers.php" class="btn btn-secondary">Back to Workers</a>
    <?php else: ?>
        <div class="box">
            <p><strong>Service Request ID:</strong> <?php echo $request_id; ?></p>
            <p><strong>Worker:</strong> <?php echo htmlspecialchars($request['worker_name']); ?></p>
            <p><strong>Work:</strong> <?php echo htmlspecialchars($request['work']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($request['location']); ?></p>
            <p><strong>Amount to pay:</strong> ₹<?php echo number_format($amount); ?></p>
        </div>

        <div class="button-group">
            <?php if($hasRazorpayKeys && $order_id): ?>
                <button id="rzp-button" class="btn btn-primary">Pay ₹<?php echo number_format($amount); ?> with Razorpay</button>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(RAZORPAY_PAYMENT_LINK); ?>" target="_blank" class="btn btn-primary">Pay ₹<?php echo number_format($amount); ?> via Razorpay Link</a>
            <?php endif; ?>
            <a href="view_workers.php" class="btn btn-secondary">Cancel</a>
        </div>

        <?php if($hasRazorpayKeys && $order_id): ?>
        <form id="payment-form" method="POST" action="razorpay_payment_success.php" style="display:none;">
            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        </form>

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById('rzp-button').onclick = function(e) {
                var options = {
                    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                    "amount": "<?php echo $amount * 100; ?>",
                    "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
                    "name": "Seva Setu",
                    "description": "Service payment for request #<?php echo $request_id; ?>",
                    "order_id": "<?php echo htmlspecialchars($order_id); ?>",
                    "handler": function (response){
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        document.getElementById('payment-form').submit();
                    },
                    "prefill": {
                        "name": "<?php echo addslashes($customer_name); ?>",
                        "email": "",
                        "contact": ""
                    },
                    "theme": {
                        "color": "#6366f1"
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.open();
                e.preventDefault();
            }
        </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
