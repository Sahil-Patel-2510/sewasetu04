<?php
session_start();
include "config/db.php";

if(!isset($_GET['txn_id'])){
    header("Location: view_workers.php");
    exit;
}

$transaction_id = htmlspecialchars($_GET['txn_id']);

$query = "SELECT * FROM payments WHERE transaction_id='$transaction_id'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    die("Payment not found!");
}

$payment = mysqli_fetch_assoc($result);

$work_query = "SELECT * FROM requests WHERE id='{$payment['work_id']}'";
$work_result = mysqli_query($conn, $work_query);
$work = mysqli_fetch_assoc($work_result);

$payment_details = json_decode($payment['payment_details'], true);
$payment_method = $payment['payment_method'];
$method_label = ucfirst(str_replace('_', ' ', $payment_method));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Pending - Seva Setu</title>
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
.container {
    background: white;
    padding: 50px 40px;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 600px;
    width: 100%;
    text-align: center;
    animation: slideUp 0.5s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.status-badge {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 10px 18px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 20px;
}
h1 {
    color: #d97706;
    margin-bottom: 14px;
    font-size: 32px;
}
.subtitle {
    color: #555;
    margin-bottom: 30px;
    font-size: 15px;
}
.info-box {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    text-align: left;
    color: #374151;
}
.info-box strong {
    display: block;
    margin-bottom: 10px;
    color: #b45309;
}
.receipt-box {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    text-align: left;
    border: 1px solid #e2e8f0;
}
.receipt-row {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
}
.receipt-row label {
    color: #4b5563;
    font-size: 14px;
}
.receipt-row span {
    color: #111827;
    font-weight: 600;
}
.btn {
    display: inline-block;
    padding: 14px 22px;
    border-radius: 10px;
    border: none;
    background: #667eea;
    color: white;
    font-size: 15px;
    text-decoration: none;
    margin-top: 24px;
}
.btn:hover {
    opacity: 0.95;
}
</style>
</head>
<body>
<div class="container">
    <div class="status-badge">Payment Pending</div>
    <h1>Payment Not Completed Yet</h1>
    <p class="subtitle">You can only proceed once the payment is confirmed.</p>

    <div class="info-box">
        <strong>Payment method:</strong>
        <?php echo htmlspecialchars($method_label); ?>
        <?php if($payment_method === 'cod'): ?>
            <br><br>
            The worker will collect cash when they come to your location. Your order is reserved but won’t move to completed until payment is received.
        <?php elseif($payment_method === 'netbanking'): ?>
            <br><br>
            Please complete the net banking transfer and then return to confirm your payment.
        <?php else: ?>
            <br><br>
            Please complete the payment and then refresh this page.
        <?php endif; ?>
    </div>

    <div class="receipt-box">
        <div class="receipt-row"><label>Transaction ID</label><span><?php echo htmlspecialchars($payment['transaction_id']); ?></span></div>
        <div class="receipt-row"><label>Worker</label><span><?php echo htmlspecialchars($payment['worker_name']); ?></span></div>
        <div class="receipt-row"><label>Work</label><span><?php echo htmlspecialchars($work['work']); ?></span></div>
        <div class="receipt-row"><label>Amount</label><span>₹<?php echo number_format($payment['amount']); ?></span></div>
        <div class="receipt-row"><label>Timing</label><span><?php echo htmlspecialchars(str_replace('_', ' ', $payment['timing'])); ?></span></div>
    </div>

    <button class="btn" onclick="startTimer()">✓ Done - Start 15 Min Timer</button>
</div>

<script>
    function startTimer() {
        // Store timer start time
        const timerStartTime = new Date().getTime();
        localStorage.setItem('timerStartTime', timerStartTime);
        localStorage.setItem('timerDuration', 15 * 60 * 1000); // 15 minutes in milliseconds
        
        // Redirect to view_workers.php
        window.location.href = 'view_workers.php';
    }
</script>
