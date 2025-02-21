<?php
include 'db_config.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit;
}

// Get logged-in user details
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // Ensure this is stored in session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_name = $_POST['project_name'];
    $status = $_POST['status'];
    $points = $_POST['points'];

    // Assigning user logic
    if ($role == 'manager' || $role == 'boss') {
        $assigned_user = $_POST['assigned_user']; // Allow selection
    } else {
        $assigned_user = $user_id; // Default to logged-in user
    }

    // Insert project into database
    $sql = "INSERT INTO projects (project_name, status, points, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $project_name, $status, $points, $assigned_user);

    if ($stmt->execute()) {
        echo "<script>alert('Project created successfully!');</script>";
        echo "<script>window.location.href = 'dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
