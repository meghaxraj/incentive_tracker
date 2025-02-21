<?php
session_start();
include('db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $new_status = $_POST['new_status'];

    if (empty($project_id) || empty($new_status)) {
        echo json_encode(["success" => false, "message" => "Invalid input."]);
        exit;
    }

    $update_sql = "UPDATE projects SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $project_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Project status updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update status."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
