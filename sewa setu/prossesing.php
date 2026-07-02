<?php
session_start();
include "config/db.php";

// Check if worker is logged in
if(!isset($_SESSION['username'])){
    header("Location: login2.php");
    exit;
}

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
    
    if(isset($pricing[$workType])){
        return $pricing[$workType];
    }
    
    foreach($pricing as $type => $price){
        if(strpos($workType, $type) !== false || strpos($type, $workType) !== false){
            return $price;
        }
    }
    
    return 500;
}

// Get current work details
$query = "SELECT * FROM requests WHERE worker_name='$worker_name' AND status='accepted' ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$current_work = null;
if($result && mysqli_num_rows($result) > 0){
    $current_work = mysqli_fetch_assoc($result);
}

// Handle work completion
if(isset($_POST['complete_work'])){
    // Get the latest active work for this worker
    $query = "SELECT id FROM requests WHERE worker_name='$worker_name' AND status='accepted' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if($result && mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $work_id = $row['id'];
        
        // Update status to completed
        $update_query = "UPDATE requests SET status='completed' WHERE id='$work_id'";
        if(mysqli_query($conn, $update_query)){
            // Redirect to worker dashboard
            header("Location: worker.php?completed=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Work In Progress</title>

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:Arial, sans-serif;
    }

    body{
        background:linear-gradient(135deg,#0f172a,#1e293b);
        display:flex;
        justify-content:center;
        align-items:center;
        min-height:100vh;
        padding: 20px;
    }

    .container{
        background:#ffffff;
        width:100%;
        max-width: 450px;
        padding:40px;
        border-radius:20px;
        text-align:center;
        box-shadow:0 10px 30px rgba(0,0,0,0.25);
        animation:fadeIn 0.8s ease-in-out;
    }

    .icon{
        width:90px;
        height:90px;
        margin:0 auto 20px;
        border-radius:50%;
        border:6px solid #f59e0b;
        border-top:6px solid transparent;
        animation:spin 1s linear infinite;
    }

    h1{
        font-size:28px;
        color:#1e293b;
        margin-bottom:10px;
    }

    p{
        font-size:15px;
        color:#64748b;
        margin-bottom:10px;
        line-height: 1.5;
    }

    .loading-text{
        font-size:16px;
        font-weight:bold;
        color:#f59e0b;
        margin:20px 0;
        animation:blink 1s infinite;
    }

    .timer{
        background: #fef3c7;
        border: 2px solid #f59e0b;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
    }

    .timer-display{
        font-size: 48px;
        font-weight: bold;
        color: #f59e0b;
        font-variant-numeric: tabular-nums;
    }

    .timer-label{
        font-size: 14px;
        color: #92400e;
        margin-top: 10px;
    }

    .button-group{
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }

    .btn{
        flex: 1;
        padding: 14px 20px;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-complete{
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-complete:hover{
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-back{
        background: #e5e7eb;
        color: #374151;
    }

    .btn-back:hover{
        background: #d1d5db;
    }

    .notes{
        background: #f3f4f6;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #f59e0b;
        margin-top: 20px;
        text-align: left;
        font-size: 13px;
        color: #666;
    }

    .notes strong{
        display: block;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .notes ul{
        margin-left: 20px;
        margin-top: 8px;
    }

    .notes li{
        margin: 5px 0;
    }

    @keyframes spin{
        100%{ transform:rotate(360deg); }
    }

    @keyframes blink{
        50%{ opacity:0.4; }
    }

    @keyframes fadeIn{
        from{
            opacity:0;
            transform:translateY(20px);
        }
        to{
            opacity:1;
            transform:translateY(0);
        }
    }
</style>
</head>
<body>

<div class="container">
    <div class="icon"></div>
    <h1>⚙️ Work in Progress</h1>
    <p>You have reached the location and started working on the assigned task.</p>
    
    <?php if($current_work): ?>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #f59e0b;">
        <div style="margin: 10px 0;"><strong>👤 Customer:</strong> <?php echo htmlspecialchars($current_work['customer_name']); ?></div>
        <div style="margin: 10px 0;"><strong>⚙️ Work Type:</strong> <?php echo htmlspecialchars($current_work['work']); ?></div>
        <div style="margin: 10px 0;"><strong>📍 Location:</strong> <?php echo htmlspecialchars($current_work['location']); ?></div>
        <div style="margin: 10px 0; font-size: 18px; font-weight: bold; color: #16a34a;">💰 Earning: ₹<?php echo getPriceForWorkType($current_work['work']); ?></div>
    </div>
    <?php endif; ?>
    
    <div class="loading-text">Currently Working...</div>
    
    <div class="timer">
        <div class="timer-label">⏱️ Time Elapsed</div>
        <div class="timer-display" id="timer">00:00:00</div>
    </div>

    <div class="notes">
        <strong>📋 Work Progress</strong>
        <ul>
            <li>✓ Location reached</li>
            <li>🔄 Work in progress</li>
            <li>⏳ Timer running</li>
        </ul>
    </div>

    <div class="button-group">
        <a href="worker.php" class="btn btn-back">← Back</a>
        <form method="POST" style="flex: 1;">
            <input type="hidden" name="complete_work" value="1">
            <button type="submit" class="btn btn-complete" style="width: 100%; margin: 0;">✓ Complete Work (₹<?php echo $current_work ? getPriceForWorkType($current_work['work']) : '500'; ?>)</button>
        </form>
    </div>
</div>

<script>
    let timeElapsed = 0;

    function updateTimer() {
        const hours = Math.floor(timeElapsed / 3600);
        const minutes = Math.floor((timeElapsed % 3600) / 60);
        const seconds = timeElapsed % 60;
        
        document.getElementById('timer').textContent = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
        
        timeElapsed++;
        setTimeout(updateTimer, 1000);
    }

    updateTimer();
</script>

</body>
</html>