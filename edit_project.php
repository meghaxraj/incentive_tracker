<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];
    $project_name = $_POST['project_name'];
    $status = $_POST['status'];
    $points = $_POST['points'];
    $user_id = $_POST['assigned_user']; // Ensure this is correctly sent from the form

    // Prepare the SQL query
    $sql = "UPDATE projects SET project_name=?, status=?, points=?, user_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiii", $project_name, $status, $points, $user_id, $project_id);

    if ($stmt->execute()) {
        echo "<script>alert('Project updated successfully!');</script>";
        echo "<script>window.location.href = 'dashboard.php';</script>";
    } else {
        echo "Error updating project: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
