# incentive_tracker
This guide will help you set up and run the Incentive Tracking System from scratch. Follow the steps carefully.


🚀 1. Setup Instructions

1️⃣ Install a Local Server

Ensure you have a local server environment like:

<br>XAMPP (Windows, macOS)
<br>MAMP (macOS)
<br>WAMP (Windows)
<br>Laragon (Windows)

2️⃣ Database Setup
<br>Open phpMyAdmin or any MySQL client.
<br>Create a new database:
<br>CREATE DATABASE incentive_tracking;
<br>Select the database:
<br>USE incentive_tracking;

3️⃣ Create Tables
<br>🔹 Users Table<br>
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('team_member', 'sales_executive', 'manager', 'boss') NOT NULL,
    incentive_points INT DEFAULT 0
);
<br>🔹 Projects Table<br>
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status ENUM('Project Started', 'Submitted for Approval', 'Changes Given by Client', 'Approved', 'Payment Received') NOT NULL DEFAULT 'Project Started',
    assigned_to INT NOT NULL,
    payment_status ENUM('Pending', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE
);
<br>🔹 Points Table (For Overwriting)<br>
CREATE TABLE points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    updated_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE
);

4️⃣ Setup the Project
<br>Copy all files to htdocs/incentive_tracking/ (if using XAMPP).

<br>Open db_config.php and update database details:<br>
<?php
$host = "localhost"; 
$user = "root";      
$pass = "";          
$dbname = "incentive_tracking"; 
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<br>Start Apache & MySQL in XAMPP.<br>

Open a browser and go to:

http://localhost/incentive_tracking/
<br>First-Time Setup:<br>

The first user who signs up will automatically be assigned the Boss role.
After logging in, the Boss can add new users.
