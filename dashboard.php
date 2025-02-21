<?php
// Start the session and check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('db_config.php');

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

// Close the statement
$stmt->close();

// Fetch the user's incentive points
$points_sql = "SELECT incentive_points FROM users WHERE id = ?";
$stmt = $conn->prepare($points_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$points_result = $stmt->get_result();

// Check if the user exists and fetch the incentive points
if ($points_result->num_rows > 0) {
    $user_points = $points_result->fetch_assoc()['incentive_points'];  // Get the user's incentive points
} else {
    $user_points = 0; // Default to 0 if no points are found
}
$stmt->close();


// Pagination logic
$projects_per_page = 10; // Number of projects per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page
$offset = ($current_page - 1) * $projects_per_page; // Calculate offset for SQL query

// Fetch the total number of projects to calculate total pages
$total_projects_sql = "SELECT COUNT(*) AS total FROM projects";
$total_projects_result = $conn->query($total_projects_sql);
$total_projects = $total_projects_result->fetch_assoc()['total'];
$total_pages = ceil($total_projects / $projects_per_page);

// Fetch projects with pagination
$projects_sql = "
    SELECT p.*, u.name AS assigned_user
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.id DESC
    LIMIT ?, ?";
$stmt = $conn->prepare($projects_sql);
$stmt->bind_param("ii", $offset, $projects_per_page);
$stmt->execute();
$projects_result = $stmt->get_result();

$projects = [];
while ($project = $projects_result->fetch_assoc()) {
    $projects[] = $project;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light sticky-top w-100" style="min-height: 56px;">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#">Dashboard</a>

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
                            <div class="dropdown-divider"></div>
                        <?php } ?>


                        <!-- Show "User Management" only for Managers & Bosses -->
                        <?php if ($role == 'manager' || $role == 'boss') { ?>
                            <a class="dropdown-item" href="user_management.php">
                                <i class="fas fa-users"></i> User Management
                            </a>
                        <?php } ?>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changeOwnPasswordModal">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>






    <!-- Main Content -->
    <div class="container mt-4">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>

        <!-- Projects Section -->
        <div class="project-section mt-5">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Your Projects</h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#createProjectModal">Create New Project</button>
            </div>

            <!-- Active Projects Table -->
            <h3>Active Projects</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($project['status']); ?></td>
                            <td><?php echo htmlspecialchars($project['points']); ?></td>
                            <td><?php echo htmlspecialchars($project['assigned_user']); ?></td>
                            <td>
                                <?php if ($role == 'team_member' || $role == 'sales_executive') { ?>
                                    <button class="btn btn-info btn-sm update-status-btn"
                                        data-id="<?php echo $project['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($project['project_name']); ?>"
                                        data-status="<?php echo htmlspecialchars($project['status']); ?>">
                                        Update
                                    </button>
                                <?php } ?>
                                <?php if ($role == 'manager' || $role == 'boss') { ?>
                                    <button class="btn btn-warning btn-sm edit-project-btn"
                                        data-toggle="modal"
                                        data-target="#editProjectModal"
                                        data-id="<?php echo $project['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($project['project_name']); ?>"
                                        data-status="<?php echo htmlspecialchars($project['status']); ?>"
                                        data-points="<?php echo $project['points']; ?>"
                                        data-assigned-user="<?php echo htmlspecialchars($project['assigned_user']); ?>">
                                        Edit
                                    </button>
                                    <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    <button class="btn btn-success btn-sm mark-complete-btn" data-id="<?php echo $project['id']; ?>" data-user-id="<?php echo $project['user_id']; ?>" data-points="<?php echo $project['points']; ?>">Mark as Complete</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <nav aria-label="Project Pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($current_page == 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" tabindex="-1">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item <?php echo ($current_page == $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>

            <!-- Completed Projects Table -->
            <h3>Completed Projects</h3>

            <!-- Filter Form -->
            <form method="GET">
                <div class="row">
                    <!-- Assigned User Filter -->
                    <div class="col-md-4">
                        <label for="filterAssigned">Filter by Assigned User:</label>
                        <select name="filter_assigned" id="filterAssigned" class="form-control">
                            <option value="">All</option>
                            <?php
                            // Fetch all users for filtering
                            $users_sql = "SELECT id, name FROM users";
                            $users_result = $conn->query($users_sql);
                            while ($user = $users_result->fetch_assoc()) {
                                $selected = (isset($_GET['filter_assigned']) && $_GET['filter_assigned'] == $user['id']) ? "selected" : "";
                                echo "<option value='" . $user['id'] . "' $selected>" . htmlspecialchars($user['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div class="col-md-4">
                        <label for="filterMonth">Filter by Month:</label>
                        <input type="month" name="filter_month" id="filterMonth" class="form-control" value="<?php echo isset($_GET['filter_month']) ? $_GET['filter_month'] : ''; ?>">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-4">
                        <br>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <?php
            // Pagination logic
            $completed_per_page = 10; // Number of completed projects per page
            $completed_page = isset($_GET['completed_page']) ? (int)$_GET['completed_page'] : 1;
            $completed_offset = ($completed_page - 1) * $completed_per_page;

            // Get filter values
            $filter_assigned = isset($_GET['filter_assigned']) ? $_GET['filter_assigned'] : '';
            $filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : '';

            // Count total completed projects for pagination
            $count_sql = "SELECT COUNT(*) AS total FROM completed_projects WHERE 1";

            // Apply filters to count query
            if (!empty($filter_assigned)) {
                $count_sql .= " AND assigned_to = '$filter_assigned'";
            }
            if (!empty($filter_month)) {
                $count_sql .= " AND DATE_FORMAT(completion_date, '%Y-%m') = '$filter_month'";
            }
            if ($role == 'team_member') {
                $count_sql .= " AND assigned_to = '$user_id'";
            }

            $count_result = $conn->query($count_sql);
            $total_completed_projects = $count_result->fetch_assoc()['total'];
            $total_completed_pages = ceil($total_completed_projects / $completed_per_page);

            // Fetch completed projects with pagination and filters
            $completed_sql = "
    SELECT cp.*, u.name AS assigned_user 
    FROM completed_projects cp
    LEFT JOIN users u ON cp.assigned_to = u.id
    WHERE 1";

            // Apply filters
            if (!empty($filter_assigned)) {
                $completed_sql .= " AND cp.assigned_to = '$filter_assigned'";
            }
            if (!empty($filter_month)) {
                $completed_sql .= " AND DATE_FORMAT(cp.completion_date, '%Y-%m') = '$filter_month'";
            }
            if ($role == 'team_member') {
                $completed_sql .= " AND cp.assigned_to = '$user_id'";
            }

            $completed_sql .= " ORDER BY cp.completion_date DESC LIMIT $completed_offset, $completed_per_page";
            $completed_result = $conn->query($completed_sql);
            ?>

            <!-- Completed Projects Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Assigned To</th>
                        <th>Completion Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($completed_result->num_rows > 0) {
                        while ($completed_project = $completed_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($completed_project['project_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($completed_project['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($completed_project['points']) . "</td>";
                            echo "<td>" . htmlspecialchars($completed_project['assigned_user']) . "</td>";
                            echo "<td>" . htmlspecialchars($completed_project['completion_date']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No completed projects found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <nav aria-label="Completed Projects Pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($completed_page == 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?completed_page=<?php echo $completed_page - 1; ?>&filter_assigned=<?php echo $filter_assigned; ?>&filter_month=<?php echo $filter_month; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_completed_pages; $i++) { ?>
                        <li class="page-item <?php echo ($completed_page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?completed_page=<?php echo $i; ?>&filter_assigned=<?php echo $filter_assigned; ?>&filter_month=<?php echo $filter_month; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item <?php echo ($completed_page == $total_completed_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?completed_page=<?php echo $completed_page + 1; ?>&filter_assigned=<?php echo $filter_assigned; ?>&filter_month=<?php echo $filter_month; ?>">Next</a>
                    </li>
                </ul>
            </nav>





        </div>

        <!-- Modal for Creating New Project -->
        <div class="modal fade" id="createProjectModal" tabindex="-1" role="dialog" aria-labelledby="createProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProjectModalLabel">Create New Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="createProjectForm" action="create_project.php" method="POST">
                            <div class="form-group">
                                <label for="projectName">Project Name</label>
                                <input type="text" class="form-control" id="projectName" name="project_name" required>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option>Project Started</option>
                                    <option>Submitted for Approval</option>
                                    <option>Changes Given by Client</option>
                                    <option>Approved</option>
                                    <option>Payment Received</option>
                                </select>
                            </div>

                            <?php if ($role == 'manager' || $role == 'boss') { ?>
                                <div class="form-group">
                                    <label for="assignedUser">Assigned To</label>
                                    <select class="form-control" id="assignedUser" name="assigned_user">
                                        <?php
                                        $users_sql = "SELECT id, name FROM users";
                                        $users_result = $conn->query($users_sql);
                                        while ($user_row = $users_result->fetch_assoc()) {
                                            echo "<option value='" . $user_row['id'] . "'>" . $user_row['name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="points">Points</label>
                                    <input type="number" class="form-control" id="points" name="points" value="0" required>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="assigned_user" value="<?php echo $user_id; ?>">
                                <input type="hidden" name="points" value="0"> <!-- Auto-set points to 0 -->
                            <?php } ?>

                            <button type="submit" class="btn btn-primary">Create Project</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap Edit Project Modal -->
        <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editProjectForm" action="edit_project.php" method="POST">
                            <input type="hidden" id="editProjectId" name="project_id">

                            <div class="form-group">
                                <label for="editProjectName">Project Name</label>
                                <input type="text" class="form-control" id="editProjectName" name="project_name" required>
                            </div>

                            <div class="form-group">
                                <label for="editStatus">Status</label>
                                <select class="form-control" id="editStatus" name="status">
                                    <option value="Project Started">Project Started</option>
                                    <option value="Submitted for Approval">Submitted for Approval</option>
                                    <option value="Changes Given by Client">Changes Given by Client</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Payment Received">Payment Received</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="editPoints">Points</label>
                                <input type="number" class="form-control" id="editPoints" name="points" required>
                            </div>

                            <select class="form-control" id="editAssignedUser" name="assigned_user">
                                <?php
                                // Fetch users from the database
                                $query = "SELECT id, name FROM users";
                                $result = $conn->query($query);

                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                                }
                                ?>
                            </select>


                            <button type="submit" class="btn btn-primary">Update Project</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update project Modal -->
        <!-- Update Project Status Modal -->
        <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateStatusModalLabel">Update Project Status</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="updateStatusForm">
                            <input type="hidden" id="project_id" name="project_id">

                            <div class="form-group">
                                <label for="project_name">Project Name</label>
                                <input type="text" class="form-control" id="project_name" name="project_name" readonly>
                            </div>

                            <div class="form-group">
                                <label for="new_status">New Status</label>
                                <select class="form-control" id="new_status" name="new_status">
                                    <option value="Project Started">Project Started</option>
                                    <option value="Submitted for Approval">Submitted for Approval</option>
                                    <option value="Changes Given by Client">Changes Given by Client</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Payment Received">Payment Received</option>
                                </select>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Change Own Password Modal -->
        <div id="changeOwnPasswordModal" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="changeOwnPasswordForm">
                            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                            <div class="mb-3">
                                <label>Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label>New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label>Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>









    </div>

    <!-- Include jQuery and Bootstrap JS (Ensure these are loaded first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#changeOwnPasswordForm").submit(function(e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "change_password.php", // ✅ Correct file for own password change
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        alert(response.message);
                        if (response.success) {
                            $("#changeOwnPasswordModal").modal("hide");
                            $("#changeOwnPasswordForm")[0].reset();
                        }
                    },
                    error: function() {
                        alert("Error processing request.");
                    }
                });
            });
        });
    </script>
    <!-- Include Custom Scripts -->
    <script src="scripts.js"></script>

</body>

</html>