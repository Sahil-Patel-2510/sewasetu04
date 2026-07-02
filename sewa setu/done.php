<?php
session_start();
include "config/db.php";

// STATUS UPDATE
if(isset($_POST['done'])){
    $id = $_POST['id'];

    $sql = "UPDATE requests SET status='completed' WHERE id='$id'";
    mysqli_query($conn, $sql);

    // Redirect back to same page
    header("Location: work.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Work Page</title>
    <style>
        body{
            font-family: Arial;
            background:#f4f4f4;
        }
        .box{
            background:white;
            padding:15px;
            margin:15px;
            border-radius:10px;
            box-shadow:0 0 10px gray;
        }
        button{
            padding:8px 15px;
            margin-top:5px;
            background:green;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }
        button:hover{
            background:darkgreen;
        }
        .back-btn{
            background:blue;
        }
        .back-btn:hover{
            background:darkblue;
        }
    </style>
</head>
<body>

<h2 align="center">My Work Requests</h2>

<!-- 🔹 Back to Worker Page -->
<div style="text-align:center; margin:20px;">
    <a href="worker.php">
        <button class="back-btn">⬅ Back to Worker</button>
    </a>
</div>

<?php
// FETCH DATA
$sql = "SELECT * FROM requests";
$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){
?>

<div class="box">
    <p><b>Work:</b> <?php echo $row['work']; ?></p>
    <p><b>Status:</b> <?php echo $row['status']; ?></p>

    <?php if($row['status'] != 'completed'){ ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <button type="submit" name="done">Work is Done</button>
        </form>
    <?php } else { ?>
        <p style="color:green;"><b>✔ Completed</b></p>
    <?php } ?>
</div>

<?php } ?>

</body>
</html>