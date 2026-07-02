<?php
$conn = mysqli_connect("localhost","root","","sewa_setu");

$sql = "SELECT * FROM requests LIMIT 1";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Service Request</title>

<style>
body{
    font-family: Arial;
    background:#f2f2f2;
}

.container{
    width:400px;
    margin:80px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0px 0px 10px gray;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

.info{
    margin:10px 0;
    font-size:18px;
}

.label{
    font-weight:bold;
    color:#333;
}

.value{
    color:#555;
}

.status{
    background:#ffc107;
    padding:5px 10px;
    border-radius:5px;
}

/* button */
button{
    margin-top:20px;
    width:100%;
    padding:10px;
    background:green;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
</style>

</head>

<body>

<div class="container">

<h2>Service Request Details</h2>

<?php if($row){ ?>

<div class="info">
<span class="label">Customer Name :</span>
<span class="value"><?php echo $row['customer_name']; ?></span>
</div>

<div class="info">
<span class="label">Worker Name :</span>
<span class="value"><?php echo $row['worker_name']; ?></span>
</div>

<div class="info">
<span class="label">Work :</span>
<span class="value"><?php echo $row['work']; ?></span>
</div>

<div class="info">
<span class="label">Location :</span>
<span class="value"><?php echo $row['location']; ?></span>
</div>

<div class="info">
<span class="label">Status :</span>
<span class="status"><?php echo $row['status']; ?></span>
</div>

<!-- DONE BUTTON -->
<form method="POST" action="done.php">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <button type="submit">Work Done</button>
</form>

<?php } else { ?>
    <p>No request found</p>
<?php } ?>

</div>

</body>
</html>