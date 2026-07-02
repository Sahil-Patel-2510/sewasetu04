<?php
session_start();
include "config/db.php";

// Check if customer is logged in
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

$customer_name = $_SESSION['username'];

// Create payments table if not exists
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

// Fetch all payments for this customer
$query = "SELECT * FROM payments WHERE customer_name='$customer_name' ORDER BY payment_date DESC";
$result = mysqli_query($conn, $query);
$total_payments = mysqli_num_rows($result);

// Calculate total spent
$sum_query = "SELECT SUM(amount) as total FROM payments WHERE customer_name='$customer_name' AND payment_status='completed'";
$sum_result = mysqli_query($conn, $sum_query);
$sum_row = mysqli_fetch_assoc($sum_result);
$total_spent = $sum_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment History - Seva Setu</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: #f8f9fa;
    min-height: 100vh;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 20px;
    text-align: center;
}

.header h1 {
    font-size: 28px;
    margin-bottom: 10px;
}

.header p {
    opacity: 0.9;
    font-size: 14px;
}

.container {
    max-width: 1000px;
    margin: -40px auto 20px;
    padding: 0 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.stat-card h3 {
    color: #666;
    font-size: 13px;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

.table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h2 {
    color: #333;
    font-size: 18px;
}

.back-btn {
    color: white;
    background: #667eea;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    transition: 0.3s;
}

.back-btn:hover {
    background: #5568d3;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    padding: 15px 20px;
    text-align: left;
    font-weight: bold;
    color: #333;
    font-size: 13px;
    text-transform: uppercase;
    border-bottom: 2px solid #e0e0e0;
}

td {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 14px;
}

tr:hover {
    background: #f9f9f9;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.amount {
    font-weight: bold;
    color: #10b981;
}

.method-badge {
    background: #f0f4ff;
    color: #667eea;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.action-link:hover {
    color: #5568d3;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

.empty-state p {
    color: #999;
    font-size: 14px;
}

.empty-state a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
}

.empty-state a:hover {
    background: #5568d3;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    table {
        font-size: 12px;
    }

    th, td {
        padding: 10px;
    }

    .table-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>
</head>
<body>

<div class="header">
    <h1>💳 Payment History</h1>
    <p>View all your transactions and receipts</p>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Payments</h3>
            <div class="stat-value"><?php echo $total_payments; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Spent</h3>
            <div class="stat-value">₹<?php echo number_format($total_spent); ?></div>
        </div>
        <div class="stat-card">
            <h3>Completed</h3>
            <div class="stat-value">
                <?php 
                $completed_query = "SELECT COUNT(*) as count FROM payments WHERE customer_name='$customer_name' AND payment_status='completed'";
                $completed_result = mysqli_query($conn, $completed_query);
                $completed_row = mysqli_fetch_assoc($completed_result);
                echo $completed_row['count'];
                ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>Pending</h3>
            <div class="stat-value">
                <?php 
                $pending_query = "SELECT COUNT(*) as count FROM payments WHERE customer_name='$customer_name' AND payment_status='pending'";
                $pending_result = mysqli_query($conn, $pending_query);
                $pending_row = mysqli_fetch_assoc($pending_result);
                echo $pending_row['count'];
                ?>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2>📋 All Transactions</h2>
            <a href="view_workers.php" class="back-btn">← Back</a>
        </div>

        <?php if($total_payments == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">💸</div>
                <h3>No Payments Yet</h3>
                <p>You haven't made any payments. Start by booking a worker!</p>
                <a href="view_workers.php">Browse Workers</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Worker</th>
                        <th>Work Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($payment = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><code style="background: #f0f4ff; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo htmlspecialchars($payment['transaction_id']); ?></code></td>
                            <td><?php echo htmlspecialchars($payment['worker_name']); ?></td>
                            <td>
                                <?php 
                                $work_query = "SELECT work FROM requests WHERE id='{$payment['work_id']}'";
                                $work_result = mysqli_query($conn, $work_query);
                                $work_row = mysqli_fetch_assoc($work_result);
                                echo htmlspecialchars($work_row['work'] ?? 'N/A');
                                ?>
                            </td>
                            <td class="amount">₹<?php echo number_format($payment['amount']); ?></td>
                            <td><span class="method-badge"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <a href="payment_success.php?txn_id=<?php echo htmlspecialchars($payment['transaction_id']); ?>" class="action-link">📄 Receipt</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
