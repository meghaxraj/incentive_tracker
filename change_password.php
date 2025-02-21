<?php
session_start();
require 'db_config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match!';
        echo json_encode($response);
        exit;
    }

    // Fetch existing password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_password, $hashed_password)) {
        $response['message'] = 'Current password is incorrect!';
        echo json_encode($response);
        exit;
    }

    // Hash new password and update
    $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password changed successfully!';
    } else {
        $response['message'] = 'Failed to change password!';
    }
    $update_stmt->close();
}

echo json_encode($response);
