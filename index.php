<?php
include 'db_config.php';
session_start();

// Check if any user exists in the database
$result = $conn->query("SELECT id FROM users LIMIT 1");
if ($result->num_rows > 0) {
    // Redirect to login.php if at least one user exists
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $role = 'boss'; // First user will always be 'Boss'

    $sql = "INSERT INTO users (name, email, password, role, incentive_points) VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sign Up as Boss</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Sign Up as Boss</h2>
    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="login-btn">Sign Up</button>
    </form>
</body>

</html>