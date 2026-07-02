<?php
session_start();
include "config/db.php";

if(!isset($_GET['txn_id'])){
    header("Location: view_workers.php");
    exit;
}

$transaction_id = htmlspecialchars($_GET['txn_id']);

// Fetch payment details
$query = "SELECT * FROM payments WHERE transaction_id='$transaction_id'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    die("Payment not found!");
}

$payment = mysqli_fetch_assoc($result);

// Get work details
$work_query = "SELECT * FROM requests WHERE id='{$payment['work_id']}'";
$work_result = mysqli_query($conn, $work_query);
$work = mysqli_fetch_assoc($work_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful - Seva Setu</title>
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
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    margin: 0 auto 30px;
    animation: bounce 0.6s ease-out;
}

@keyframes bounce {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

h1 {
    color: #10b981;
    margin-bottom: 10px;
    font-size: 32px;
}

.subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 15px;
}

.receipt-box {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin: 30px 0;
    text-align: left;
    border-left: 4px solid #10b981;
}

.receipt-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    font-size: 16px;
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.receipt-row:last-child {
    border-bottom: none;
}

.receipt-row label {
    color: #666;
    font-size: 14px;
}

.receipt-row span {
    color: #333;
    font-weight: 500;
    font-size: 14px;
}

.amount-row span {
    font-size: 18px;
    color: #10b981;
    font-weight: bold;
}

.transaction-id {
    background: #f0f4ff;
    padding: 12px;
    border-radius: 6px;
    margin: 15px 0;
    font-family: monospace;
    font-size: 12px;
    color: #667eea;
    word-break: break-all;
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
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e0e0e0;
    color: #333;
}

.btn-secondary:hover {
    background: #d0d0d0;
}

.status-badge {
    display: inline-block;
    background: #d1fae5;
    color: #065f46;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 20px;
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 15px;
    border-radius: 8px;
    font-size: 13px;
    color: #333;
    margin: 20px 0;
    text-align: left;
}
</style>
</head>
<body>

<div class="container">
    <div class="success-icon">✓</div>
    <h1>Payment Successful!</h1>
    <p class="subtitle">Your payment has been processed successfully</p>

    <div class="status-badge">
        <?php 
        $status_text = ucfirst(str_replace('_', ' ', $payment['payment_status']));
        echo $status_text;
        ?>
    </div>

    <div class="receipt-box">
        <div class="receipt-title">📄 Payment Receipt</div>

        <div class="receipt-row">
            <label>Transaction ID:</label>
            <span><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
        </div>

        <div class="receipt-row">
            <label>Worker:</label>
            <span><?php echo htmlspecialchars($payment['worker_name']); ?></span>
        </div>

        <div class="receipt-row">
            <label>Work Type:</label>
            <span><?php echo htmlspecialchars($work['work']); ?></span>
        </div>

        <div class="receipt-row">
            <label>Location:</label>
            <span><?php echo htmlspecialchars($work['location']); ?></span>
        </div>

        <div class="receipt-row">
            <label>Payment Method:</label>
            <span><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
        </div>

        <div class="receipt-row">
            <label>Date & Time:</label>
            <span><?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?></span>
        </div>

        <div class="receipt-row amount-row">
            <label>Amount Paid:</label>
            <span>₹<?php echo number_format($payment['amount']); ?></span>
        </div>
    </div>

    <div class="info-box">
        <strong>✓ Payment Confirmed</strong>
        <p style="margin-top: 8px;">A receipt has been sent to your registered email. You can view your payment history anytime.</p>
    </div>

    <div class="button-group">
        <a href="payment_history.php" class="btn btn-secondary">📋 View History</a>
        <a href="view_workers.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>

</body>
</html>
