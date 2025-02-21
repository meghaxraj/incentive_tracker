<?php
include 'db_config.php';
session_start();

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : "";
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = isset($_POST['password']) ? trim($_POST['password']) : "";

    if ($user_id) {
        // Update existing user (don't update password)
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $role, $user_id);
        if ($stmt->execute()) {
            echo "User updated successfully!";
        } else {
            echo "Error updating user.";
        }
        $stmt->close();
    } else {
        // Create new user (password is required)
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $role, $hashedPassword);
            if ($stmt->execute()) {
                echo "User created successfully!";
            } else {
                echo "Error creating user.";
            }
            $stmt->close();
        } else {
            echo "Password is required for new users.";
        }
    }
}

$conn->close();
