<?php
session_start();
include "config/db.php";

// Check login
if(!isset($_SESSION['username'])){
    die("Please login first");
}

// Data from form
$worker_name = $_POST['worker_name'];
$work = $_POST['work'];
$location = $_POST['location'];

// Logged in user = customer
$customer_name = $_SESSION['username'];

// Insert query
$sql = "INSERT INTO requests (customer_name, worker_name, work, location, status)
VALUES ('$customer_name', '$worker_name', '$work', '$location', 'pending')";

if(mysqli_query($conn, $sql)){
    header("Location: view_workers.php?msg=Request Sent");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>