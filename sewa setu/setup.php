<?php
include "config/db.php";

// Array of all SQL queries to create tables
$tables = array(
    // Users Table (Customers)
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        address TEXT,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(username),
        INDEX(email)
    )",
    
    // Workers Table
    "CREATE TABLE IF NOT EXISTS workers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        mobile VARCHAR(20),
        phone VARCHAR(20),
        password VARCHAR(255),
        skill VARCHAR(100),
        service_type VARCHAR(100),
        experience INT,
        location VARCHAR(255),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(username),
        INDEX(name)
    )",
    
    // Requests Table (Work Requests)
    "CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        worker_name VARCHAR(100),
        work VARCHAR(255),
        location VARCHAR(255),
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(customer_name),
        INDEX(worker_name),
        INDEX(status)
    )",
    
    // Payments Table
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        work_id INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        worker_name VARCHAR(100),
        amount DECIMAL(10, 2) NOT NULL,
        transaction_id VARCHAR(255) UNIQUE,
        payment_method VARCHAR(50),
        payment_status VARCHAR(50) DEFAULT 'pending',
        payment_details TEXT,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(work_id),
        INDEX(customer_name),
        INDEX(worker_name),
        INDEX(payment_status)
    )",
    
    // Worker Notifications Table
    "CREATE TABLE IF NOT EXISTS worker_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        worker_name VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        notification_type VARCHAR(50),
        work_id INT,
        payment_id INT,
        read_status INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(worker_name),
        INDEX(read_status),
        INDEX(work_id)
    )"
);

// Execute all table creation queries
$created = 0;
$failed = 0;
$errors = array();

foreach ($tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        $created++;
    } else {
        $failed++;
        $errors[] = mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .success { color: #27ae60; font-size: 18px; margin: 15px 0; }
        .error { color: #e74c3c; font-size: 18px; margin: 15px 0; }
        .table-list { background: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .table-list ul { list-style: none; padding: 0; }
        .table-list li { padding: 8px 0; border-bottom: 1px solid #bdc3c7; }
        .table-list li:last-child { border-bottom: none; }
        .note { background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px; color: #856404; }
        .center { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Setup Complete</h1>
        
        <div class="success">✓ <?php echo $created; ?> Table(s) Created Successfully!</div>
        
        <?php if ($failed > 0): ?>
            <div class="error">✗ <?php echo $failed; ?> Error(s) Occurred</div>
            <?php foreach ($errors as $error): ?>
                <p style="color: #e74c3c; font-size: 12px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="table-list">
            <strong>Tables Created:</strong>
            <ul>
                <li>✓ users - Customer accounts</li>
                <li>✓ workers - Worker accounts</li>
                <li>✓ requests - Work requests</li>
                <li>✓ payments - Payment records</li>
                <li>✓ worker_notifications - Worker notifications</li>
            </ul>
        </div>
        
        <div class="note">
            <strong>⚠️ Important:</strong> Your database is now ready! You can delete this setup.php file as it's no longer needed. Your application code has not been modified.
        </div>
        
        <div class="center">
            <a href="login.php" style="background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Go to Login →</a>
        </div>
    </div>
</body>
</html>
