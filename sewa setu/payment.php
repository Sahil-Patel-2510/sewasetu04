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

// Get work ID from URL
if(!isset($_GET['work_id'])){
    header("Location: view_workers.php");
    exit;
}

$work_id = (int)$_GET['work_id'];

// Fetch work details for the request
$query = "SELECT * FROM requests WHERE id='$work_id' AND customer_name='$customer_name'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    die("Work not found!");
}

$work = mysqli_fetch_assoc($result);
$request_status = strtolower($work['status']);

$timing = isset($_GET['timing']) ? $_GET['timing'] : '';
if($timing !== 'before_work' && $timing !== 'after_work'){
    $timing = ($request_status === 'completed') ? 'after_work' : 'before_work';
}

$payment_method = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : '';

// Pricing based on work type
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

// Calculate amount based on work type
$amount = getPriceForWorkType($work['work']);

// Handle payment method selection
$payment_method = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - Seva Setu</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.header {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.header a {
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    transition: 0.3s;
}

.header a:hover {
    background: rgba(255,255,255,0.3);
}

.container {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 600px;
    width: 100%;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 28px;
}

.subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.work-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    border-left: 4px solid #667eea;
}

.detail-row {
    margin: 10px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-row strong {
    color: #333;
}

.detail-row span {
    color: #666;
    font-size: 14px;
}

.amount-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 30px;
}

.amount-label {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 10px;
}

.amount-value {
    font-size: 48px;
    font-weight: bold;
    font-variant-numeric: tabular-nums;
}

.amount-currency {
    font-size: 24px;
    vertical-align: super;
}

.payment-methods {
    margin-bottom: 30px;
}

.methods-title {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}

.method-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.method-btn {
    border: 2px solid #e0e0e0;
    padding: 20px 15px;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
    color: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    font-weight: bold;
    font-size: 15px;
}

.method-btn small {
    font-size: 12px;
    font-weight: normal;
    color: #666;
    margin-top: 4px;
}

.method-btn:hover {
    border-color: #667eea;
    background: #f8f9fa;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.2);
}

.method-btn.disabled {
    opacity: 0.55;
    pointer-events: none;
    background: #f4f5f7;
    border-color: #d1d5db;
    color: #8b95a1;
}

.method-icon {
    font-size: 32px;
    display: block;
}

.method-btn.active {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    color: #667eea;
}

.method-btn.active small {
    color: #667eea;
}

.section-divider {
    height: 1px;
    background: #e0e0e0;
    margin: 30px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: Arial;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.button-group {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}

.btn {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #e0e0e0;
    color: #333;
}

.btn-secondary:hover {
    background: #d0d0d0;
}

.info-box {
    background: #e8f4f8;
    border-left: 4px solid #3b82f6;
    padding: 15px;
    border-radius: 8px;
    font-size: 13px;
    color: #333;
    margin-bottom: 20px;
}

.info-box strong {
    display: block;
    margin-bottom: 5px;
}

@media (max-width: 600px) {
    .container {
        padding: 25px;
    }

    h1 {
        font-size: 22px;
    }

    .amount-value {
        font-size: 36px;
    }

    .method-options {
        grid-template-columns: 1fr;
    }

    .button-group {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<div class="header">
    <a href="view_workers.php">← Back</a>
</div>

<div class="container">
    <h1>💳 Payment Options</h1>
    <p class="subtitle">Choose payment timing and method for this service order.</p>

    <div class="payment-methods">
        <div class="methods-title">When would you like to pay?</div>
        <div class="method-options">
            <a href="?work_id=<?php echo $work_id; ?>&timing=before_work" class="method-btn <?php echo ($timing === 'before_work') ? 'active' : ''; ?>">
                <span class="method-icon">💳</span>
                <span>Pay Before Work</span>
                <small>Secure your booking now</small>
            </a>

            <a href="?work_id=<?php echo $work_id; ?>&timing=after_work" class="method-btn <?php echo ($timing === 'after_work') ? 'active' : ''; ?>">
                <span class="method-icon">✓</span>
                <span>Pay After Work</span>
                <small>Pay when work is done</small>
            </a>
        </div>
        <div class="info-box">
            <?php if($timing === 'before_work'): ?>
                <strong>💳 Pay Before Work</strong>
                Complete payment now to confirm your booking. The worker can start immediately.
            <?php else: ?>
                <strong>✓ Pay After Work</strong>
                Pay directly to the worker or online after the work is completed. You can verify the work quality before paying.
            <?php endif; ?>
        </div>
    </div>

    <div class="work-details">
        <div class="detail-row">
            <strong>Worker:</strong>
            <span><?php echo htmlspecialchars($work['worker_name']); ?></span>
        </div>
        <div class="detail-row">
            <strong>Work Type:</strong>
            <span><?php echo htmlspecialchars($work['work']); ?></span>
        </div>
        <div class="detail-row">
            <strong>Location:</strong>
            <span><?php echo htmlspecialchars($work['location']); ?></span>
        </div>
        <div class="detail-row">
            <strong>Status:</strong>
            <span style="color: #10b981; font-weight: bold;">✓ Completed</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">Amount to Pay</div>
        <div>
            <span class="amount-currency">₹</span><span class="amount-value"><?php echo $amount; ?></span>
        </div>
    </div>

    <div class="info-box">
        <strong>💡 Secure Payment</strong>
        Your payment is processed securely through our payment gateway. No hidden charges.
    </div>

    <div class="payment-methods">
        <div class="methods-title">Payment Option</div>
        <div class="method-options">
            <a href="?work_id=<?php echo $work_id; ?>&timing=<?php echo $timing; ?>&method=qr" class="method-btn <?php echo ($payment_method == 'qr') ? 'active' : ''; ?>">
                <span class="method-icon">🔳</span>
                <span>QR Pay</span>
            </a>
            <a href="?work_id=<?php echo $work_id; ?>&timing=<?php echo $timing; ?>&method=cod" class="method-btn <?php echo ($payment_method == 'cod') ? 'active' : ''; ?>">
                <span class="method-icon">💰</span>
                <span>Cash on Site</span>
            </a>
        </div>
        <div class="subtitle" style="margin-top: 12px;">Choose QR payment for instant UPI transfer, or select cash on delivery if you want to pay when the worker arrives.</div>

    <?php if($payment_method): ?>
        <div class="section-divider"></div>

        <?php if($payment_method === 'razorpay'): ?>
        <form id="paymentForm" method="POST" action="process_payment.php">
            <input type="hidden" name="work_id" value="<?php echo $work_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="payment_method" value="razorpay">
            <input type="hidden" name="payment_timing" value="<?php echo $timing; ?>">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your.email@example.com" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="+91XXXXXXXXXX" required>
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Clear</button>
                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
            </div>
        </form>

        <?php elseif($payment_method === 'wallet'): ?>
        <form method="POST" action="process_payment.php">
            <input type="hidden" name="work_id" value="<?php echo $work_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="payment_method" value="wallet">
            <input type="hidden" name="payment_timing" value="<?php echo $timing; ?>">

            <div class="info-box">
                <strong>💼 Wallet Balance</strong>
                Available Balance: <strong>₹2,500</strong>
            </div>

            <div class="form-group">
                <label>Wallet PIN (for verification)</label>
                <input type="password" name="wallet_pin" placeholder="Enter your 4-digit PIN" maxlength="4" required>
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Pay from Wallet</button>
            </div>
        </form>

        <?php elseif($payment_method === 'netbanking'): ?>
        <form method="POST" action="process_payment.php">
            <input type="hidden" name="work_id" value="<?php echo $work_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="payment_method" value="netbanking">
            <input type="hidden" name="payment_timing" value="<?php echo $timing; ?>">

            <div class="form-group">
                <label>Select Your Bank</label>
                <select name="bank_name" required>
                    <option value="">-- Choose a bank --</option>
                    <option value="HDFC">HDFC Bank</option>
                    <option value="ICICI">ICICI Bank</option>
                    <option value="SBI">State Bank of India</option>
                    <option value="Axis">Axis Bank</option>
                    <option value="KOTAK">Kotak Bank</option>
                    <option value="IndusInd">IndusInd Bank</option>
                </select>
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Pay via Net Banking</button>
            </div>
        </form>

        <?php elseif($payment_method === 'qr'): ?>
        <?php
            $upiUri = "upi://pay?pa=" . urlencode(UPI_ID) .
                      "&pn=" . urlencode(UPI_NAME) .
                      "&am=" . urlencode($amount) .
                      "&cu=INR" .
                      "&tn=" . urlencode(UPI_NOTE . " - work #" . $work_id) .
                      "&tr=" . urlencode("TXN" . $work_id . time());
        ?>
        <div class="info-box">
            <strong>🔳 Scan UPI QR to pay</strong>
            <p style="margin-top: 10px;">Scan this QR code with your UPI app. Payment will be sent directly to our UPI ID.</p>
        </div>

        <div class="box" style="text-align:center;">
            <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo urlencode($upiUri); ?>&choe=UTF-8" alt="UPI QR Code" style="width:300px;height:300px;margin-bottom:15px;border:1px solid #ddd;border-radius:12px;">
            <p style="margin-top:10px;font-weight:bold;">UPI ID: <?php echo htmlspecialchars(UPI_ID); ?></p>
            <p>Amount: <strong>₹<?php echo number_format($amount); ?></strong></p>
            <p><button type="button" onclick="copyUpiId()" class="btn btn-secondary">Copy UPI ID</button></p>
        </div>

        <form method="POST" action="process_payment.php">
            <input type="hidden" name="work_id" value="<?php echo $work_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="payment_method" value="qr">
            <input type="hidden" name="payment_timing" value="<?php echo $timing; ?>">

            <div class="form-group">
                <label>
                    <input type="checkbox" name="qr_confirm" required>
                    I have scanned the UPI QR code and completed the payment.
                </label>
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm UPI Payment</button>
            </div>
        </form>
        <script>
            function copyUpiId() {
                navigator.clipboard.writeText('<?php echo addslashes(UPI_ID); ?>')
                    .then(() => alert('UPI ID copied to clipboard'))
                    .catch(() => alert('Unable to copy UPI ID'));
            }
        </script>

        <?php elseif($payment_method === 'cod'): ?>
        <div class="info-box">
            <strong>📍 Cash Payment at Site</strong>
            <p style="margin-top: 10px;">The worker will collect payment directly from you. Please have the exact amount ready when they arrive.</p>
            <p style="margin-top: 10px; font-size: 12px;">Amount: <strong>₹<?php echo $amount; ?></strong></p>
        </div>

        <form method="POST" action="process_payment.php">
            <input type="hidden" name="work_id" value="<?php echo $work_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="payment_method" value="cod">
            <input type="hidden" name="payment_timing" value="<?php echo $timing; ?>">

            <div class="form-group">
                <label>
                    <input type="checkbox" name="cod_confirm" required>
                    I agree to pay ₹<?php echo $amount; ?> in cash when the worker arrives
                </label>
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Cash Payment</button>
            </div>
        </form>

        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
