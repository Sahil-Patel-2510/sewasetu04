<?php
session_start();
include "config/db.php";

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

// Fetch customer's work requests
$pending_query = "SELECT * FROM requests WHERE customer_name='$customer_name' AND status='pending' ORDER BY id DESC";
$active_query = "SELECT * FROM requests WHERE customer_name='$customer_name' AND status='accepted' ORDER BY id DESC";
$completed_query = "SELECT * FROM requests WHERE customer_name='$customer_name' AND status='completed' ORDER BY id DESC";

$pending_result = mysqli_query($conn, $pending_query);
$active_result = mysqli_query($conn, $active_query);
$completed_result = mysqli_query($conn, $completed_query);

// Check if payment exists for completed work
function hasPayment($work_id, $conn) {
    $query = "SELECT id FROM payments WHERE work_id='$work_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard - Seva Setu</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: #f8f9fa;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.header h1 {
    font-size: 24px;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.header-actions a {
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    background: rgba(255,255,255,0.2);
    border-radius: 6px;
    font-size: 13px;
    transition: 0.3s;
}

.header-actions a:hover {
    background: rgba(255,255,255,0.3);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
    text-align: center;
}

.stat-card h3 {
    color: #666;
    font-size: 13px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
}

.section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.work-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    border-left: 4px solid #3b82f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: 0.3s;
}

.work-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.work-card.pending {
    border-left-color: #dc2626;
}

.work-card.active {
    border-left-color: #f59e0b;
}

.work-card.completed {
    border-left-color: #10b981;
}

.work-info {
    flex: 1;
}

.work-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
    font-size: 16px;
}

.work-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.detail {
    display: flex;
    align-items: center;
    gap: 6px;
}

.work-actions {
    display: flex;
    gap: 10px;
    margin-left: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.3s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
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
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}

.status-pending {
    background: #fee2e2;
    color: #991b1b;
}

.status-active {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.empty-state {
    background: white;
    padding: 60px 40px;
    border-radius: 10px;
    text-align: center;
    color: #666;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-state a {
    display: inline-block;
    margin-top: 15px;
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
    .header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .work-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .work-details {
        grid-template-columns: 1fr;
    }

    .work-actions {
        margin-left: 0;
        margin-top: 15px;
        width: 100%;
    }

    .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>👤 <?php echo htmlspecialchars($customer_name); ?></h1>
        <p style="font-size: 13px; opacity: 0.9;">My Service Requests</p>
    </div>
    <div class="header-actions">
        <a href="payment_history.php">💳 Payment History</a>
        <a href="view_workers.php">🔍 Find Workers</a>
        <a href="Afrist.php">🚪 Logout</a>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Pending</h3>
            <div class="stat-value"><?php echo mysqli_num_rows($pending_result); ?></div>
        </div>
        <div class="stat-card">
            <h3>In Progress</h3>
            <div class="stat-value"><?php echo mysqli_num_rows($active_result); ?></div>
        </div>
        <div class="stat-card">
            <h3>Completed</h3>
            <div class="stat-value"><?php echo mysqli_num_rows($completed_result); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Spent</h3>
            <div class="stat-value">
                <?php
                $sum_query = "SELECT SUM(amount) as total FROM payments WHERE customer_name='$customer_name'";
                $sum_result = mysqli_query($conn, $sum_query);
                $sum_row = mysqli_fetch_assoc($sum_result);
                $total = $sum_row['total'] ?? 0;
                echo "₹" . number_format($total);
                ?>
            </div>
        </div>
    </div>

    <!-- PENDING REQUESTS -->
    <div class="section">
        <div class="section-title">⏳ Awaiting Acceptance</div>
        
        <?php if(mysqli_num_rows($pending_result) == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No pending requests</p>
                <a href="view_workers.php">Browse Workers</a>
            </div>
        <?php else: ?>
            <?php while($work = mysqli_fetch_assoc($pending_result)): ?>
                <div class="work-card pending">
                    <div class="work-info">
                        <div class="work-title">👷 <?php echo htmlspecialchars($work['worker_name']); ?></div>
                        <div class="work-details">
                            <div class="detail">📋 <?php echo htmlspecialchars($work['work']); ?></div>
                            <div class="detail">📍 <?php echo htmlspecialchars($work['location']); ?></div>
                            <div class="detail"><span class="status-badge status-pending">Pending</span></div>
                        </div>
                    </div>
                    <div class="work-actions">
                        <button class="btn btn-secondary" disabled>⏳ Waiting...</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- ACTIVE REQUESTS -->
    <div class="section">
        <div class="section-title">⚙️ In Progress</div>
        
        <?php if(mysqli_num_rows($active_result) == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">✨</div>
                <p>No active work orders</p>
            </div>
        <?php else: ?>
            <?php while($work = mysqli_fetch_assoc($active_result)): ?>
                <div class="work-card active">
                    <div class="work-info">
                        <div class="work-title">👷 <?php echo htmlspecialchars($work['worker_name']); ?> - Working</div>
                        <div class="work-details">
                            <div class="detail">📋 <?php echo htmlspecialchars($work['work']); ?></div>
                            <div class="detail">📍 <?php echo htmlspecialchars($work['location']); ?></div>
                            <div class="detail"><span class="status-badge status-active">In Progress</span></div>
                        </div>
                    </div>
                    <div class="work-actions">
                        <button class="btn btn-secondary" disabled>🔄 In Progress</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- COMPLETED REQUESTS -->
    <div class="section">
        <div class="section-title">✅ Completed & Ready to Pay</div>
        
        <?php if(mysqli_num_rows($completed_result) == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <p>No completed work yet</p>
            </div>
        <?php else: ?>
            <?php while($work = mysqli_fetch_assoc($completed_result)): ?>
                <div class="work-card completed">
                    <div class="work-info">
                        <div class="work-title">👷 <?php echo htmlspecialchars($work['worker_name']); ?> - Completed ✓</div>
                        <div class="work-details">
                            <div class="detail">📋 <?php echo htmlspecialchars($work['work']); ?></div>
                            <div class="detail">📍 <?php echo htmlspecialchars($work['location']); ?></div>
                            <div class="detail">
                                <span class="status-badge status-completed">Completed</span>
                                <?php if(!hasPayment($work['id'], $conn)): ?>
                                    <span style="margin-left: 10px; color: #667eea; font-weight: bold;">₹<?php echo getPriceForWorkType($work['work']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="work-actions">
                        <?php if(hasPayment($work['id'], $conn)): ?>
                            <button class="btn btn-secondary" disabled>✓ Paid</button>
                            <a href="payment_history.php" class="btn btn-secondary">📄 Receipt</a>
                        <?php else: ?>
                            <a href="payment.php?work_id=<?php echo $work['id']; ?>" class="btn btn-success">💳 Pay Now (₹<?php echo getPriceForWorkType($work['work']); ?>)</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
