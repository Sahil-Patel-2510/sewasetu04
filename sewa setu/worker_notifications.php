<?php
session_start();
include "config/db.php";

// Check if worker is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['username'])){
    header("Location: login2.php");
    exit;
}

$worker_name = $_SESSION['username'];

// Mark notification as read if requested
if(isset($_GET['mark_read'])){
    $notification_id = (int)$_GET['mark_read'];
    $update_query = "UPDATE worker_notifications SET read_status=1 WHERE id='$notification_id' AND worker_name='$worker_name'";
    mysqli_query($conn, $update_query);
    header("Location: worker_notifications.php");
    exit;
}

// Fetch all notifications for this worker
$query = "SELECT wn.*, r.work, r.location, r.customer_name 
          FROM worker_notifications wn
          LEFT JOIN requests r ON wn.work_id = r.id
          WHERE wn.worker_name='$worker_name'
          ORDER BY wn.read_status ASC, wn.created_at DESC";

$result = mysqli_query($conn, $query);

if(!$result){
    die("Query failed: " . mysqli_error($conn));
}

$notifications = [];
$unread_count = 0;

while($row = mysqli_fetch_assoc($result)){
    $notifications[] = $row;
    if($row['read_status'] == 0) $unread_count++;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - Seva Setu Worker</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background: #f5f5f5;
    min-height: 100vh;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.header h1 {
    font-size: 28px;
    margin-bottom: 5px;
}

.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}

.back-btn {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    margin-bottom: 20px;
    transition: 0.3s;
}

.back-btn:hover {
    background: #764ba2;
}

.notification-count {
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notification-count strong {
    color: #667eea;
}

.notification-item {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 5px solid #f59e0b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.notification-item.unread {
    background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
    border-left-color: #f97316;
}

.notification-content {
    flex: 1;
}

.notification-icon {
    font-size: 24px;
    margin-right: 10px;
}

.notification-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
    font-size: 15px;
}

.notification-message {
    color: #555;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 10px;
}

.notification-details {
    font-size: 12px;
    color: #888;
}

.notification-action {
    display: flex;
    gap: 10px;
    align-items: center;
}

.mark-read-btn {
    background: #10b981;
    color: white;
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: 0.3s;
}

.mark-read-btn:hover {
    background: #059669;
}

.unread-badge {
    display: inline-block;
    background: #f97316;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-state h2 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
}

.notification-time {
    font-size: 12px;
    color: #aaa;
    text-align: right;
}
</style>
</head>
<body>

<div class="header">
    <h1>📬 Notifications</h1>
    <p>Payment and request updates</p>
</div>

<div class="container">
    <a href="worker.php" class="back-btn">← Back to Dashboard</a>

    <?php if($unread_count > 0): ?>
    <div class="notification-count">
        You have <strong><?php echo $unread_count; ?></strong> unread notification<?php echo $unread_count > 1 ? 's' : ''; ?>
    </div>
    <?php endif; ?>

    <?php if(count($notifications) > 0): ?>
        <?php foreach($notifications as $notif): ?>
            <div class="notification-item <?php echo $notif['read_status'] == 0 ? 'unread' : ''; ?>">
                <div class="notification-content">
                    <div class="notification-title">
                        💳 Payment After Work Scheduled
                        <?php if($notif['read_status'] == 0): ?>
                            <span class="unread-badge">NEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-message">
                        <?php echo htmlspecialchars($notif['message']); ?>
                    </div>
                    <div class="notification-details">
                        Customer: <strong><?php echo htmlspecialchars($notif['customer_name']); ?></strong> | 
                        Work: <strong><?php echo htmlspecialchars($notif['work'] ?? 'Not specified'); ?></strong> |
                        Created: <strong><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></strong>
                    </div>
                </div>
                <div class="notification-action">
                    <?php if($notif['read_status'] == 0): ?>
                        <a href="?mark_read=<?php echo $notif['id']; ?>" class="mark-read-btn">Mark as Read</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h2>All caught up!</h2>
            <p>You don't have any pending payment notifications.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
