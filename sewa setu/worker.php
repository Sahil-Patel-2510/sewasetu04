<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config/db.php";

// Check if worker is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['username'])){
    header("Location: login2.php");
    exit;
}

// Use the logged-in worker username from session
$worker_name = $_SESSION['username'];

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

// Handle work completion
if(isset($_POST['complete_work'])){
    $id = (int)$_POST['work_id'];
    $update_sql = "UPDATE requests SET status='completed' WHERE id='$id' AND worker_name='$worker_name'";
    mysqli_query($conn,$update_sql);
}

// Handle work acceptance
if(isset($_POST['accept_work'])){
    $id = (int)$_POST['work_id'];
    $update_sql = "UPDATE requests SET status='accepted' WHERE id='$id' AND worker_name='$worker_name'";
    mysqli_query($conn, $update_sql);
}

// Fetch pending requests
$sql_pending = "SELECT * FROM requests 
        WHERE worker_name='$worker_name' 
        AND status='pending'";

// Fetch accepted/in-progress requests
$sql_active = "SELECT * FROM requests 
        WHERE worker_name='$worker_name' 
        AND status='accepted'";

// Fetch completed requests
$sql_completed = "SELECT * FROM requests 
        WHERE worker_name='$worker_name' 
        AND status='completed'
        ORDER BY id DESC LIMIT 10";

$result_pending = mysqli_query($conn, $sql_pending);
$result_active = mysqli_query($conn, $sql_active);
$result_completed = mysqli_query($conn, $sql_completed);

// Fetch the latest 5 completed items for worker history
$sql_recent_completed = "SELECT * FROM requests 
        WHERE worker_name='$worker_name' 
        AND status='completed'
        ORDER BY id DESC LIMIT 5";
$result_recent_completed = mysqli_query($conn, $sql_recent_completed);

?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seva Setu Worker Dashboard</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial, Helvetica, sans-serif;
}

body{
background:#eef1f6;
}

/* HEADER */

.header{
background:#1f2937;
color:white;
padding:18px 30px;
display:flex;
justify-content:space-between;
align-items:center;
}

.worker{
font-size:22px;
font-weight:bold;
}

.badge{
background:#16a34a;
padding:6px 14px;
border-radius:20px;
font-size:14px;
}

/* LAYOUT */

.main{
display:flex;
height:92vh;
}

/* ORDERS */

.orders{
width:75%;
padding:25px;
overflow-y:auto;
}

.title{
font-size:24px;
margin-bottom:20px;
font-weight:bold;
}

.order-card{
background:white;
border-left:6px solid #3b82f6;
padding:18px;
margin-bottom:15px;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
transition:0.2s;
}

.order-card:hover{
transform:scale(1.02);
}

.order-card h3{
margin-bottom:6px;
}

.order-info{
font-size:14px;
color:#555;
margin-bottom:10px;
}

.btn{
background:#3b82f6;
color:white;
border:none;
padding:8px 14px;
border-radius:6px;
cursor:pointer;
font-size:14px;
}

.btn:hover{
background:#2563eb;
}

/* HISTORY */

.history{
width:25%;
background:white;
border-left:2px solid #ddd;
padding:20px;
overflow-y:auto;
}

.history-title{
font-size:20px;
font-weight:bold;
margin-bottom:15px;
}

.history-card{
background:#f3f4f6;
padding:10px;
border-radius:6px;
margin-bottom:10px;
font-size:14px;
border-left:4px solid #f59e0b;
}

.history-card strong{
color:#111;
}

</style>
</head>

<body>

<!-- HEADER -->

<div class="header">

<div class="worker">
Worker: <?php echo $worker_name; ?>
</div>

<div style="display: flex; align-items: center; gap: 15px;">
<div class="badge">
Active
</div>
<a href="worker_notifications.php" style="color: white; text-decoration: none; font-size: 14px; background: #f59e0b; padding: 6px 12px; border-radius: 6px;">📬 Notifications</a>
<a href="Afrist.php" style="color: white; text-decoration: none; font-size: 14px; background: #dc2626; padding: 6px 12px; border-radius: 6px;">Logout</a>
</div>

</div>

<!-- MAIN -->

<div class="main">

<!-- ORDERS -->

<div class="orders">

<div class="title">📋 New Work Requests (Pending)</div>

<?php
if(mysqli_num_rows($result_pending) === 0) {
    echo '<div class="order-card"><p style="color: #666;">No new requests at the moment.</p></div>';
}
while($row = mysqli_fetch_assoc($result_pending)){
?>
<div class="order-card">
<h3>👤 <?php echo htmlspecialchars($row['customer_name']); ?></h3>

<div class="order-info">
📍 Work: <?php echo htmlspecialchars($row['work']); ?><br>
📌 Location: <?php echo htmlspecialchars($row['location']); ?><br>
<span style="color: #667eea; font-weight: bold; font-size: 16px;">💰 Earning: ₹<?php echo getPriceForWorkType($row['work']); ?></span><br>
<small style="color: #999;">Request ID: #<?php echo $row['id']; ?></small>
</div>

<form method="POST" style="margin-top: 12px;">
<input type="hidden" name="work_id" value="<?php echo $row['id']; ?>">
<button type="submit" name="accept_work" class="btn" style="background: #16a34a; margin-right: 8px;">✓ Accept Work (₹<?php echo getPriceForWorkType($row['work']); ?>)</button>
<a href="rejected.php?id=<?php echo $row['id']; ?>" class="btn" style="background: #dc2626; text-decoration: none; display: inline-block;">✗ Reject</a>
</form>
</div>
<?php
}
?>

<hr style="margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;">

<div class="title">⚙️ Active Work (In Progress)</div>

<?php
if(mysqli_num_rows($result_active) === 0) {
    echo '<div class="order-card"><p style="color: #666;">No active work orders right now.</p></div>';
}
while($row = mysqli_fetch_assoc($result_active)){
?>
<div class="order-card" style="border-left: 6px solid #f59e0b;">
<h3>👤 <?php echo htmlspecialchars($row['customer_name']); ?></h3>

<div class="order-info">
📍 Work: <?php echo htmlspecialchars($row['work']); ?><br>
📌 Location: <?php echo htmlspecialchars($row['location']); ?><br>
<span style="color: #16a34a; font-weight: bold; font-size: 16px;">💰 Earning: ₹<?php echo getPriceForWorkType($row['work']); ?></span><br>
<small style="color: #999;">Request ID: #<?php echo $row['id']; ?></small>
</div>

<form method="POST" style="margin-top: 12px;">
<input type="hidden" name="work_id" value="<?php echo $row['id']; ?>">
<button type="submit" name="complete_work" class="btn" style="background: #059669;">✓ Mark as Complete (₹<?php echo getPriceForWorkType($row['work']); ?>)</button>
</form>
<a href="loc2.php?id=<?php echo $row['id']; ?>" class="btn" style="background: #3b82f6; margin-top: 8px; display: inline-block; text-decoration: none;">📍 Location Details</a>
</div>
<?php
}
?>

<hr style="margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;">

<div class="title">✅ Completed Work</div>

<?php
if(mysqli_num_rows($result_completed) === 0) {
    echo '<div class="order-card"><p style="color: #666;">No completed work yet.</p></div>';
}
while($row = mysqli_fetch_assoc($result_completed)){
?>
<div class="order-card" style="border-left: 6px solid #10b981; opacity: 0.9;">
<h3>👤 <?php echo htmlspecialchars($row['customer_name']); ?></h3>

<div class="order-info">
✓ Work: <?php echo htmlspecialchars($row['work']); ?><br>
📌 Location: <?php echo htmlspecialchars($row['location']); ?><br>
<span style="color: #10b981; font-weight: bold;">Status: COMPLETED</span><br>
<span style="color: #10b981; font-weight: bold; font-size: 16px;">💰 Earned: ₹<?php echo getPriceForWorkType($row['work']); ?></span><br>
<small style="color: #999;">Request ID: #<?php echo $row['id']; ?></small>
</div>

<div style="margin-top: 12px; padding: 10px; background: #f0fdf4; border-radius: 6px; border-left: 3px solid #10b981;">
<p style="margin: 5px 0; font-size: 13px;"><strong>Great work! 🎉</strong> This task has been successfully completed.</p>
<button type="button" class="btn" style="background: #8b5cf6; font-size: 12px; padding: 6px 12px;">⭐ Leave Feedback</button>
</div>
</div>
<?php
}
?>

</div>

<!-- HISTORY -->

<div class="history">

<div class="history-title">📊 Quick Stats</div>

<div style="padding: 10px; background: #f0f4f8; border-radius: 8px; margin-bottom: 15px;">
<p style="margin: 8px 0;"><strong>Pending:</strong> <span style="color: #dc2626; font-size: 18px; font-weight: bold;"><?php echo mysqli_num_rows($result_pending); ?></span></p>
<p style="margin: 8px 0;"><strong>Active:</strong> <span style="color: #f59e0b; font-size: 18px; font-weight: bold;"><?php echo mysqli_num_rows($result_active); ?></span></p>
<p style="margin: 8px 0;"><strong>Completed:</strong> <span style="color: #10b981; font-size: 18px; font-weight: bold;"><?php echo mysqli_num_rows($result_completed); ?></span></p>
</div>

<div style="border-bottom: 1px solid #e0e0e0; margin: 15px 0;"></div>

<div style="padding: 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 15px; text-align: center;">
<a href="payment_history.php" style="display: block; text-decoration: none; color: #667eea; font-weight: bold; padding: 10px; border-radius: 6px; transition: 0.3s; background: #f0f4ff;">
💳 Payment History
</a>
</div>

<div class="history-title">📝 Recent Completed Work</div>

<?php
if(mysqli_num_rows($result_recent_completed) === 0) {
    echo '<div class="order-card"><p style="color: #666;">No completed history yet.</p></div>';
} else {
    while($row = mysqli_fetch_assoc($result_recent_completed)){
?>
<div class="history-card"><strong>✓</strong> <?php echo htmlspecialchars($row['customer_name']); ?> - <?php echo htmlspecialchars($row['work']); ?></div>
<?php
    }
}
?>

<div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px; text-align: center;">
<p style="font-size: 13px; color: #666; margin: 5px 0;">💡 Keep accepting more requests to increase your rating!</p>
</div>

</div>

</div>

</body>
</html>
