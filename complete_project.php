<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];

    // Fetch project details before deleting
    $fetch_sql = "SELECT project_name, user_id, status, points FROM projects WHERE id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $project_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        $project_name = $project['project_name'];
        $assigned_user = $project['user_id']; // This should match 'assigned_to' in completed_project table
        $status = "Completed";
        $points = $project['points'];
        $completion_date = date("Y-m-d H:i:s");

        // Insert into completed_project table
        $insert_sql = "INSERT INTO completed_projects(project_name, assigned_to, status, completion_date, points) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sissi", $project_name, $assigned_user, $status, $completion_date, $points);
        $insert_stmt->execute();

        if ($insert_stmt->affected_rows > 0) {
            // Update user points
            $update_points_sql = "UPDATE users SET incentive_points = incentive_points + ? WHERE id = ?";
            $update_points_stmt = $conn->prepare($update_points_sql);
            $update_points_stmt->bind_param("ii", $points, $assigned_user);
            $update_points_stmt->execute();

            // Delete from projects table
            $delete_sql = "DELETE FROM projects WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $project_id);
            $delete_stmt->execute();

            // ✅ Return a proper JSON response
            echo json_encode(["success" => true, "message" => "Project marked as completed and points credited."]);
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "Error inserting into completed projects."]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Project not found."]);
        exit();
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit();
}
