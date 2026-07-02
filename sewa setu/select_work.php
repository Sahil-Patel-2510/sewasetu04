<?php
session_start();
include "config/db.php";

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

// Check if worker_id is provided
if(!isset($_GET['worker_id'])){
    header("Location: view_workers.php");
    exit;
}

$worker_id = (int)$_GET['worker_id'];
$worker_name = isset($_GET['worker_name']) ? htmlspecialchars($_GET['worker_name']) : "Worker";
$location = isset($_GET['location']) ? htmlspecialchars($_GET['location']) : "";

if(isset($_POST['worker_name'])) {
    $worker_name = htmlspecialchars($_POST['worker_name']);
}

// Get worker details
$query = "SELECT * FROM workers WHERE id=$worker_id LIMIT 1";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0){
    echo "Worker not found!";
    exit;
}

$worker = mysqli_fetch_assoc($result);

// Predefined work types based on worker's specialty
$work_options = array();

$worker_specialty = strtolower($worker['service_type']);

// Map specialties to their specific work types
if(strpos($worker_specialty, 'electric') !== false){
    $work_options = array(
        "Electrical Repair",
        "Wiring Installation",
        "Short Circuit Fix",
        "Panel Repair",
        "Switchboard Installation",
        "Socket & Switch Installation",
        "Light Installation",
        "Fan Repair",
        "Appliance Repair",
        "Maintenance & Inspection"
    );
} elseif(strpos($worker_specialty, 'plumb') !== false){
    $work_options = array(
        "Pipe Installation",
        "Pipe Repair",
        "Water Leak Fix",
        "Tap Installation",
        "Drainage Repair",
        "Water Filter Installation",
        "Valve Repair",
        "Bathroom Fitting",
        "Maintenance",
        "Emergency Repair"
    );
} elseif(strpos($worker_specialty, 'paint') !== false || strpos($worker_specialty, 'painter') !== false){
    $work_options = array(
        "Wall Painting",
        "Interior Painting",
        "Exterior Painting",
        "Texture Coating",
        "Door & Window Painting",
        "Ceiling Painting",
        "Waterproofing Paint",
        "Maintenance & Touch-up",
        "Color Consultation",
        "Wall Cleaning & Prep"
    );
} elseif(strpos($worker_specialty, 'carpenter') !== false || strpos($worker_specialty, 'wood') !== false){
    $work_options = array(
        "Wood Work",
        "Door Installation",
        "Window Frame Work",
        "Cupboard Installation",
        "Shelf Installation",
        "Furniture Repair",
        "Wooden Floor Work",
        "Maintenance",
        "Design & Estimation",
        "Finishing Work"
    );
} elseif(strpos($worker_specialty, 'mason') !== false || strpos($worker_specialty, 'bricklayer') !== false){
    $work_options = array(
        "Wall Construction",
        "Brick Laying",
        "Concrete Work",
        "Tile Work",
        "Floor Leveling",
        "Repair & Maintenance",
        "Waterproofing",
        "Foundation Work",
        "Restoration",
        "Custom Design Work"
    );
} elseif(strpos($worker_specialty, 'ac') !== false || strpos($worker_specialty, 'air') !== false){
    $work_options = array(
        "AC Installation",
        "AC Repair",
        "Gas Refill",
        "Cooling Not Working",
        "Water Leakage",
        "Noise Issues",
        "Filter Cleaning",
        "Maintenance",
        "Compressor Repair",
        "Emergency Service"
    );
} elseif(strpos($worker_specialty, 'appliance') !== false || strpos($worker_specialty, 'fridge') !== false){
    $work_options = array(
        "Washing Machine Repair",
        "Refrigerator Repair",
        "Microwave Repair",
        "Dishwasher Repair",
        "Oven Repair",
        "Water Heater Repair",
        "Gas Stove Repair",
        "Maintenance & Cleaning",
        "Installation",
        "Emergency Service"
    );
} else {
    // Default work types for other specialties
    $work_options = array(
        "General Repair",
        "Installation",
        "Maintenance",
        "Inspection",
        "Consultation",
        "Custom Service",
        "Emergency Service",
        "Follow-up Work",
        "Quality Check",
        "Other"
    );
}

$message = "";
if(isset($_POST['submit_request'])){
    $selected_work = '';
    if(isset($_POST['work_type'])){
        if(is_array($_POST['work_type'])){
            $selected_work = implode(', ', array_map('trim', $_POST['work_type']));
        } else {
            $selected_work = trim($_POST['work_type']);
        }
    }
    $custom_work = mysqli_real_escape_string($conn, $_POST['custom_work']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    // Use custom work if provided, otherwise use selected work
    $final_work = !empty($custom_work) ? $custom_work : $selected_work;
    
    if(empty($final_work)){
        $message = "Please select or enter a work type!";
    } else {
        $customer_name = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : '';
        $worker_name_db = mysqli_real_escape_string($conn, $worker_name);
        $final_work_db = mysqli_real_escape_string($conn, $final_work);
        $location_db = mysqli_real_escape_string($conn, $location);
        $description_db = mysqli_real_escape_string($conn, $description);

        $insert_query = "INSERT INTO requests (customer_name, worker_name, work, location, status) VALUES ('$customer_name', '$worker_name_db', '$final_work_db', '$location_db', 'pending')";

        if(mysqli_query($conn, $insert_query)){
            $request_id = mysqli_insert_id($conn);
            header("Location: payment.php?work_id=" . $request_id);
            exit;
        } else {
            $message = "Error sending request: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Work - Sewa Setu</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 100%;
}

h2 {
    color: #333;
    margin-bottom: 10px;
    text-align: center;
}

.worker-info {
    background: #f0f4f8;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #667eea;
}

.worker-info p {
    margin: 8px 0;
    color: #555;
    font-size: 14px;
}

.worker-info strong {
    color: #333;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

select, textarea, input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: Arial, Helvetica, sans-serif;
}

select:focus, textarea:focus, input[type="text"]:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea {
    resize: vertical;
    min-height: 80px;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color 0.3s, background 0.3s;
}

.checkbox-item input {
    width: 16px;
    height: 16px;
}

.checkbox-item:hover {
    border-color: #667eea;
    background: #eef2ff;
}

.selected-summary {
    background: #f8fafc;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 14px;
}

.custom-work-section {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-top: 12px;
}

.custom-work-section p {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.button-group {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button, .btn-back {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

button {
    background: #667eea;
    color: white;
}

button:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-back {
    background: #e0e0e0;
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-back:hover {
    background: #d0d0d0;
}

.message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
}

.message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.note {
    background: #fff3cd;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
    color: #856404;
    border-left: 4px solid #ffc107;
    margin-top: 15px;
}
</style>
</head>
<body>

<div class="container">
    <h2>Request Work from <?php echo $worker_name; ?></h2>

    <div class="worker-info">
        <p><strong>Worker:</strong> <?php echo htmlspecialchars($worker['username']); ?></p>
        <p><strong>Specialty:</strong> <?php echo htmlspecialchars($worker['service_type']); ?></p>
        <p><strong>Experience:</strong> <?php echo htmlspecialchars($worker['experience']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($worker['location']); ?></p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="worker_id" value="<?php echo $worker_id; ?>">
        <input type="hidden" name="worker_name" value="<?php echo htmlspecialchars($worker_name); ?>">
        <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
        <div class="form-group">
            <label>Select Work Type</label>
            <div class="checkbox-grid" id="workTypeGrid">
                <?php foreach($work_options as $work): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="work_type[]" value="<?php echo htmlspecialchars($work); ?>">
                        <?php echo htmlspecialchars($work); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="selected-summary" style="margin-bottom: 20px;">
            <p><strong>Selected work details:</strong></p>
            <ul id="selectedList" style="list-style-type: disc; margin-left: 18px; color: #333;"></ul>
            <div style="margin-top: 10px; padding: 10px; background: #e8f5e8; border-radius: 6px; border-left: 3px solid #10b981;">
                <strong>Total estimated price: <span id="totalPrice" style="color: #059669; font-size: 16px;">₹0</span></strong>
            </div>
        </div>

        <div class="custom-work-section">
            <p><strong>Or describe your custom work need:</strong></p>
            <input type="text" name="custom_work" placeholder="e.g., LED bulb replacement, pipe fitting, etc." maxlength="100">
        </div>

        <div class="form-group">
            <label for="description">Additional Details / Description</label>
            <textarea name="description" id="description" placeholder="Describe what exactly needs to be done, any special requirements, best time to contact, etc."></textarea>
        </div>

        <div class="note">
            💡 <strong>Tip:</strong> Provide detailed information to help the worker understand your requirements better.
        </div>

        <div id="finalSummarySection" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: none;">
            <p style="margin-bottom: 12px;"><strong>Final Summary:</strong></p>
            <ul id="finalSummaryList" style="list-style-type: none; margin-left: 0; color: white; margin-bottom: 15px;"></ul>
            <div style="font-size: 18px; border-top: 2px solid rgba(255,255,255,0.3); padding-top: 12px;">
                <strong>Total Amount to Pay: <span id="finalTotalPrice" style="font-size: 22px;">₹0</span></strong>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" name="submit_request">Send Request</button>
            <a href="view_workers.php" class="btn-back">Back</a>
        </div>
    </form>
</div>

<script>
    const priceEstimate = document.getElementById('totalPrice');
    const finalTotalPrice = document.getElementById('finalTotalPrice');
    const finalSummarySection = document.getElementById('finalSummarySection');
    const workCheckboxes = document.querySelectorAll('input[name="work_type[]"]');
    const customWorkInput = document.querySelector('input[name="custom_work"]');
    const selectedList = document.getElementById('selectedList');
    const finalSummaryList = document.getElementById('finalSummaryList');

    const workPricing = {
        <?php foreach($work_options as $index => $work): ?>
            "<?php echo addslashes($work); ?>": <?php echo getPriceForWorkType($work); ?><?php echo $index < count($work_options)-1 ? ',' : ''; ?>
        <?php endforeach; ?>
    };

    const defaultEstimate = <?php echo getPriceForWorkType($worker['service_type']); ?>;

    function refreshSelectedSummary() {
        const selected = Array.from(workCheckboxes)
            .filter(chk => chk.checked)
            .map(chk => chk.value);

        selectedList.innerHTML = '';
        finalSummaryList.innerHTML = '';

        if(selected.length === 0 && customWorkInput.value.trim() === '') {
            const li = document.createElement('li');
            li.textContent = 'No work selected yet. Choose options above or type custom work.';
            selectedList.appendChild(li);
            finalSummarySection.style.display = 'none';
            return;
        }

        selected.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            selectedList.appendChild(li);
            
            const finalLi = document.createElement('li');
            finalLi.textContent = '✓ ' + item;
            finalLi.style.marginBottom = '6px';
            finalSummaryList.appendChild(finalLi);
        });

        if(customWorkInput.value.trim() !== '') {
            const li = document.createElement('li');
            li.textContent = 'Custom work: ' + customWorkInput.value.trim();
            selectedList.appendChild(li);
            
            const finalLi = document.createElement('li');
            finalLi.textContent = '✓ Custom work: ' + customWorkInput.value.trim();
            finalLi.style.marginBottom = '6px';
            finalSummaryList.appendChild(finalLi);
        }

        finalSummarySection.style.display = 'block';
    }

    function updateEstimate() {
        const selected = Array.from(workCheckboxes)
            .filter(chk => chk.checked)
            .map(chk => chk.value);
        const custom = customWorkInput.value.trim().toLowerCase();
        let amount = 0;

        // Sum all selected work prices
        if(selected.length > 0) {
            amount = selected.reduce((total, item) => {
                return total + (workPricing[item] || 0);
            }, 0);
        }

        // Check custom work and add its price if it matches
        if(custom.length > 0) {
            let customPrice = 0;
            for(const keyword in workPricing) {
                if(custom.includes(keyword.toLowerCase())) {
                    customPrice = Math.max(customPrice, workPricing[keyword]);
                }
            }
            if(customPrice > 0) {
                amount += customPrice;
            }
        }

        priceEstimate.textContent = '₹' + amount;
        finalTotalPrice.textContent = '₹' + amount;
    }

    workCheckboxes.forEach(chk => chk.addEventListener('change', () => {
        refreshSelectedSummary();
        updateEstimate();
    }));
    customWorkInput.addEventListener('input', () => {
        refreshSelectedSummary();
        updateEstimate();
    });
    refreshSelectedSummary();
    updateEstimate();
</script>

</body>
</html>
