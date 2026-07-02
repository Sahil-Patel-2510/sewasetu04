<?php
include "config/db.php";

function getPriceForWorkType($workType) {
    $workType = strtolower(trim($workType));
    $pricing = array(
        // Electrical Work
        'electrical' => 500,
        'plumbing' => 250,
        'painting' => 300,
        'carpentry' => 400,
        'mason' => 550,
        'ac' => 600,
        'air' => 600,
        'fridge' => 500,
        'washing machine' => 450,
        'appliance' => 400,
        'cleaning' => 200,
        'pest' => 400,
        'gardening' => 250,
        'lock' => 150,
        'geyser' => 300,
        'tv' => 400,
        'laptop' => 500,
        'microwave' => 350,
        'oven' => 450,
        'door' => 350,
        'window' => 250,
    );
    foreach($pricing as $key => $price) {
        if(strpos($workType, $key) !== false) {
            return $price;
        }
    }
    return 300;
}

$selected_skill = "";
$selected_location = "";
$where_clauses = [];

if(isset($_GET['skill']) && $_GET['skill'] !== ''){
    $selected_skill = mysqli_real_escape_string($conn, $_GET['skill']);
    $where_clauses[] = "service_type='$selected_skill'";
}

if(isset($_GET['location']) && $_GET['location'] !== ''){
    $selected_location = mysqli_real_escape_string($conn, $_GET['location']);
    $where_clauses[] = "location='$selected_location'";
}

$query = "SELECT * FROM workers";
if(!empty($where_clauses)){
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
$result = mysqli_query($conn, $query);

$skillResult = mysqli_query($conn, "SELECT DISTINCT service_type FROM workers ORDER BY service_type");
$locationResult = mysqli_query($conn, "SELECT DISTINCT location FROM workers ORDER BY location");

?>

<html>
<head>
    <style>

body{
font-family:Arial, Helvetica, sans-serif;
background:#f4f6f9;
margin:0;
padding:40px;
}

/* Container */

.container{
width:80%;
margin:auto;
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

/* Title */

h2{
text-align:center;
margin-bottom:20px;
color:#333;
}

/* Filter */

.filter-box {
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-start;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-box label {
    font-weight: 600;
    color: #444;
}

.filter-box select,
.filter-box button {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 0.95rem;
}

.filter-box select {
    min-width: 180px;
    background: white;
}

.filter-box button {
    background: #2e86ff;
    color: white;
    border-color: #2e86ff;
    cursor: pointer;
}

.filter-box button:hover {
    background: #2369d9;
}

.subtext {
    margin-top: 0;
    margin-bottom: 18px;
    color: #444;
}

/* Table */

table{
width:100%;
border-collapse:collapse;
font-size:15px;
}

th{
background:#2c7be5;
color:white;
padding:12px;
text-align:left;
}

td{
padding:10px;
border-bottom:1px solid #ddd;
}

tr:hover{
background:#f1f5ff;
}

tr:nth-child(even){
background:#fafafa;
}

</style>
</head>
<body>

<div class="container">
    <h2>Available Workers</h2>

    <div class="filter-box">
        <form method="GET" action="view_workers.php">
            <label for="skill">Select skill</label>
            <select name="skill" id="skill">
                <option value="">All skills</option>
                <?php while($skillRow = mysqli_fetch_assoc($skillResult)): ?>
                    <option value="<?php echo htmlspecialchars($skillRow['service_type']); ?>" <?php echo ($selected_skill === $skillRow['service_type']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($skillRow['service_type']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="location">Select location</label>
            <select name="location" id="location">
                <option value="">All locations</option>
                <?php while($locationRow = mysqli_fetch_assoc($locationResult)): ?>
                    <option value="<?php echo htmlspecialchars($locationRow['location']); ?>" <?php echo ($selected_location === $locationRow['location']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($locationRow['location']); ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Show workers</button>
        </form>
    </div>

    <?php if($selected_skill): ?>
        <p class="subtext">Showing workers with skill: <strong><?php echo htmlspecialchars($selected_skill); ?></strong></p>
    <?php endif; ?>

    <table border="1">

    <tr>
    <th>Name</th>
    <th>Phone</th>
    <th>Skill</th>
    <th>Experience</th>
    <th>Location</th>
    <th>Apply</th>
    </tr>

<?php

while($row = mysqli_fetch_assoc($result))
{
?>

<tr>
<td><?php echo htmlspecialchars($row['username']); ?></td>
<td><?php echo htmlspecialchars($row['mobile']); ?></td>
<td><?php echo htmlspecialchars($row['service_type']); ?></td>
<td><?php echo htmlspecialchars($row['experience']); ?></td>
<td><?php echo htmlspecialchars($row['location']); ?></td>
<td>
<a href="select_work.php?worker_id=<?php echo $row['id']; ?>&worker_name=<?php echo urlencode($row['username']); ?>&location=<?php echo urlencode($row['location']); ?>" class="btn">Request</a>
</td>
</tr>

<?php
}
?>

</table>

</body>
</html>