<?php
session_start();
include "config/db.php";

// Check if worker is logged in
if(!isset($_SESSION['username'])){
    header("Location: login2.php");
    exit;
}

$worker_name = $_SESSION['username'];
$work_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

function getPriceForWorkType($workType) {
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

    foreach($pricing as $type => $price){
        if(strpos($workType, $type) !== false || strpos($type, $workType) !== false){
            return $price;
        }
    }

    return 300;
}

$query = "SELECT * FROM requests WHERE id='$work_id' AND worker_name='$worker_name' AND status='accepted'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    die("Work not found!");
}

$work = mysqli_fetch_assoc($result);
$amount = getPriceForWorkType($work['work']);
?>

<style>
body{
    font-family:Arial;
    background:#eef1f6;
    margin:0;
    padding: 20px;
}

.container{
    max-width: 600px;
    margin: 0 auto;
}

.header{
    background:#1f2937;
    color:white;
    padding:20px;
    font-size:20px;
    font-weight:bold;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.back-link{
    color: white;
    text-decoration: none;
    font-size: 14px;
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 6px;
}

.back-link:hover{
    background: rgba(255,255,255,0.3);
}

.order-card{
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 5px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.order-card h2{
    margin-top: 0;
    color: #1f2937;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 10px;
}

.info-row{
    margin: 15px 0;
    padding: 10px;
    background: #f9fafb;
    border-left: 4px solid #3b82f6;
    border-radius: 4px;
}

.info-row strong{
    color: #1f2937;
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.info-row span{
    font-size: 16px;
    color: #333;
}

.btn{
    padding:12px 20px;
    border:none;
    border-radius:8px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
    width: 100%;
    margin-top: 10px;
}

.btn-primary{
    background:#16a34a;
    color:white;
}

.btn-primary:hover{
    background:#15803d;
}

.btn-secondary{
    background:#3b82f6;
    color:white;
}

.btn-secondary:hover{
    background:#2563eb;
}

.status-badge{
    display: inline-block;
    padding: 6px 12px;
    background: #10b981;
    color: white;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.timer-box{
    text-align:center;
    margin: 20px 0;
    padding: 20px;
    background: #f0f9ff;
    border: 2px solid #3b82f6;
    border-radius: 10px;
}

.timer-title{
    font-weight:bold;
    margin-bottom:10px;
    color: #1f2937;
}

.timer-circle{
    width:100px;
    height:100px;
    border-radius:50%;
    border:4px solid #3b82f6;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:bold;
    margin:auto;
    background:#ffffff;
    color: #3b82f6;
}
</style>
</head>

<body>

<div class="header">
    <div>Work Details</div>
    <a href="worker.php" class="back-link">← Back</a>
</div>

<div class="container">
    <div class="order-card">
        <h2>📋 Job Information</h2>
        
        <div class="info-row">
            <strong>CUSTOMER NAME</strong>
            <span>👤 <?php echo htmlspecialchars($work['customer_name']); ?></span>
        </div>

        <div class="info-row">
            <strong>WORK TYPE</strong>
            <span>⚙️ <?php echo htmlspecialchars($work['work']); ?></span>
        </div>

        <div class="info-row">
            <strong>LOCATION</strong>
            <span>📍 <?php echo htmlspecialchars($work['location']); ?></span>
        </div>

        <div class="info-row">
            <strong>STATUS</strong>
            <span><span class="status-badge">✓ ACCEPTED</span></span>
        </div>

        <div class="info-row" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-left: 4px solid #667eea; color: white;">
            <strong style="color: rgba(255,255,255,0.9);">EARNING FOR THIS WORK</strong>
            <span style="font-size: 24px; color: white; font-weight: bold;">💰 ₹<?php echo $amount; ?></span>
        </div>

        <div class="timer-box">
            <div class="timer-title">⏱️ Time to Reach Location</div>
            <div class="timer-circle" id="timer">15:00</div>
            <p style="margin: 10px 0; color: #666; font-size: 14px;">Expected time to reach the location</p>
        </div>

        <button class="btn btn-primary" onclick="location.href='prossesing.php?id=<?php echo $work_id; ?>'">✓ Confirm I'm On The Way</button>
        <button class="btn btn-secondary" onclick="location.href='worker.php'">← Back to Dashboard</button>
    </div>

    <p style="text-align: center; color: #666; margin-top: 30px; font-size: 13px;">Once you reach the location, confirm to start the work</p>
</div>

<script>
// Simple countdown timer
let timeLeft = 15 * 60; // 15 minutes in seconds

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('timer').textContent = 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');
    timeLeft--;
    if(timeLeft >= 0) {
        setTimeout(updateTimer, 1000);
    }
}

updateTimer();
</script>

</body>
</html>

<script>
let time = 900;
const timer = document.getElementById("timer");

setInterval(function(){
    let minutes = Math.floor(time / 60);
    let seconds = time % 60;

    seconds = seconds < 10 ? "0" + seconds : seconds;
    timer.innerHTML = minutes + ":" + seconds;

    if(time > 0){
        time--;
    }
},1000);

function reachLocation(){
    alert("You have reached the customer location.");
}
</script>

</body>
</html>