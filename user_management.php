<?php
include 'db_config.php';
session_start();

// Check if user is Manager or Boss
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'manager' && $_SESSION['role'] != 'boss')) {
    header("Location: dashboard.php"); // Redirect if unauthorized
    exit();
}
// Get the logged-in user's data from the session
$user_id = $_SESSION['user_id'];

// Fetch user's role from the database
$sql = "SELECT role, name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists and fetch the role
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = $user['role'];  // Get the role of the logged-in user
    $user_name = $user['name']; // Get the user's name
} else {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <nav class="navbar navbar-light bg-light sticky-top w-100" style="min-height: 56px;">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="dashboard.php">Dashboard</a>

            <!-- Profile Dropdown -->
            <ul class="navbar-nav position-relative">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right position-absolute mt-2" aria-labelledby="profileDropdown" style="z-index: 1050;">
                        <h6 class="dropdown-header"><?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($role); ?>)</h6>

                        <?php if ($role == 'team_member') { ?>
                            <div class="dropdown-item">
                                <strong>Total Points:</strong> <?php echo $user_points; ?>
                            </div>
                        <?php } ?>
                        <a class="dropdown-item" href="dashboard.php">Back to Dashboard</a>
                        <a class="dropdown-item" href="logout.php">Logout
                        </a>

                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center">User Management</h2>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchUser" class="form-control" placeholder="Search Users...">
        </div>

        <!-- User Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>User ID</th>
                        <th>Total Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php
                    $result = $conn->query("SELECT id, name, email, role, incentive_points FROM users");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . ucfirst($row['role']) . "</td>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['incentive_points'] . "</td>";
                        echo "<td>
                        <button class='btn btn-sm btn-warning edit-user-btn' data-id='{$row['id']}' data-name='{$row['name']}' data-email='{$row['email']}' data-role='{$row['role']}'>Edit</button>
                        <button class='btn btn-sm btn-danger delete-user-btn' data-id='{$row['id']}'>Delete</button>
                        <button class='btn btn-sm btn-info change-password-btn' data-id='{$row['id']}'>Change Password</button>
                        <button class='btn btn-sm btn-success redeem-points-btn' data-id='{$row['id']}'>Redeem Points</button>
                    </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add User Button -->
        <div class="text-center mt-3">
            <button id="addUserBtn" class="btn btn-primary">Add New User</button>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId" name="user_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" id="userName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select class="form-control" id="userRole" name="role">
                                <option value="team_member">Team Member</option>
                                <option value="sales_executive">Sales Executive</option>
                                <option value="manager">Manager</option>
                                <option value="boss">Boss</option>
                            </select>
                        </div>
                        <div class="form-group" id="passwordField">
                            <label>Password</label>
                            <input type="password" class="form-control" id="userPassword" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <input type="hidden" id="changeUserId" name="user_id">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>

</body>

</html>