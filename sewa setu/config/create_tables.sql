-- Users Table (Customers)
CREATE TABLE IF NOT EXISTS users (
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
);

-- Workers Table
CREATE TABLE IF NOT EXISTS workers (
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
);

-- Requests Table (Work Requests)
CREATE TABLE IF NOT EXISTS requests (
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
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
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
);

-- Worker Notifications Table
CREATE TABLE IF NOT EXISTS worker_notifications (
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
);
