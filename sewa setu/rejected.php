<?php
include "config/db.php";

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Update status
    $sql = "UPDATE requests SET status='rejected' WHERE id='$id'";
    mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rejected</title>
    <style>
        body{
            font-family: Arial;
            background:#f4f4f4;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }
        .box{
            background:white;
            padding:30px;
            border-radius:10px;
            text-align:center;
            box-shadow:0 0 10px gray;
        }
        .box h2{
            color:red;
        }
        .penalty{
            color:#ff0000;
            font-size:20px;
            margin-top:10px;
        }
        button{
            margin-top:20px;
            padding:10px 20px;
            background:blue;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }
        button:hover{
            background:darkblue;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>❌ Work Rejected</h2>
    <p>The work order has been rejected successfully.</p>

    <div class="penalty">
        ⚠ Penalty: ₹500 Applied
    </div>

    <a href="worker.php">
        <button>Back to Dashboard</button>
    </a>
</div>

</body>
</html>