<?php
include "config/db.php";

if (!isset($_POST['id'], $_POST['action'])) {
    header("Location: worker_dashboard.php");
    exit;
}

$id = (int) $_POST['id'];
$status = mysqli_real_escape_string($conn, $_POST['action']);

$sql = "UPDATE requests SET status='$status' WHERE id='$id'";
mysqli_query($conn, $sql);

header("Location: worker_dashboard.php");
exit;
?>