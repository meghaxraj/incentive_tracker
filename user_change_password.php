<?php
include 'db_config.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $new_password = isset($_POST['password']) ? trim($_POST['password']) : "";

    // Check if inputs are valid
    if ($user_id <= 0 || empty($new_password)) {
        echo "Invalid user ID or password.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    // Prepare SQL query
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt) {
        echo "SQL Error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("si", $hashedPassword, $user_id);

    if ($stmt->execute()) {
        echo "Password updated successfully!";
    } else {
        echo "Database Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
