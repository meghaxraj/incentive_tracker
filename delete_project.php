<?php
include 'db_config.php';
session_start();

// Check if user is logged in and has permission to delete
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'boss')) {
    echo "<script>alert('Unauthorized access!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Check if project ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $project_id = intval($_GET['id']);

    // Prepare DELETE query
    $sql = "DELETE FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);

    if ($stmt->execute()) {
        echo "<script>alert('Project deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting project: " . $stmt->error . "');</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid project ID!');</script>";
}

// Redirect back to dashboard
echo "<script>window.location.href = 'dashboard.php';</script>";

$conn->close();
