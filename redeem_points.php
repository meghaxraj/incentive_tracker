<?php
include 'db_config.php';

if (isset($_POST['id'])) {
    $userId = $_POST['id'];
    $stmt = $conn->prepare("UPDATE users SET incentive_points=0 WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    echo "Points redeemed successfully.";
}
