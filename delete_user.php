<?php
include 'db_config.php';

if (isset($_POST['id'])) {
    $userId = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    echo "User deleted successfully.";
}
