$(document).ready(function () {
    // ✅ Search Users
    $("#searchUser").on("keyup", function () {
        let value = $(this).val().toLowerCase();
        $("#userTableBody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // ✅ Open Edit User Modal & Populate Fields
    $(document).on("click", ".edit-user-btn", function () {
        let userId = $(this).data("id");
        let userName = $(this).data("name");
        let userEmail = $(this).data("email");
        let userRole = $(this).data("role");

        $("#userId").val(userId);
        $("#userName").val(userName);
        $("#userEmail").val(userEmail);
        $("#userRole").val(userRole);
        $("#passwordField").hide(); // Hide password field when editing
        $("#userModal").modal("show");
    });

    // ✅ Open Add New User Modal
    $("#addUserBtn").click(function () {
        $("#userForm")[0].reset(); // Clear form
        $("#userId").val(""); // No user ID (new user)
        $("#passwordField").show(); // Show password field for new users
        $("#userModal").modal("show");
    });

    // ✅ Save User (Create/Edit)
    $("#userForm").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "save_user.php",
            type: "POST",
            data: $("#userForm").serialize(),
            success: function (response) {
                alert(response.trim());
                location.reload();
            },
            error: function () {
                alert("Error processing request.");
            }
        });
    });

    // ✅ Delete User
    $(document).on("click", ".delete-user-btn", function () {
        let userId = $(this).data("id");
        if (confirm("Are you sure you want to delete this user?")) {
            $.post("delete_user.php", { id: userId }, function (response) {
                alert(response.trim());
                location.reload();
            });
        }
    });

    // ✅ Change Password
    $(document).on("click", ".change-password-btn", function () {
        let userId = $(this).data("id");
        $("#changeUserId").val(userId);
        $("#changePasswordModal").modal("show");
    });

    // ✅ Submit Change Password Form
    $("#changePasswordForm").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "user_change_password.php",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                alert(response.trim());
                $("#changePasswordModal").modal("hide");
                $("#changePasswordForm")[0].reset();
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Error processing request.");
            }
        });
    });

    // ✅ Redeem Points
    $(document).on("click", ".redeem-points-btn", function () {
        let userId = $(this).data("id");
        if (confirm("Are you sure you want to redeem points?")) {
            $.post("redeem_points.php", { id: userId }, function (response) {
                alert(response.trim());
                location.reload();
            });
        }
    });

    // ✅ Update Project Status (AJAX)
    $(document).on("click", ".update-status-btn", function () {
        let projectId = $(this).data("id");
        let projectName = $(this).data("name");
        let status = $(this).data("status");

        $("#project_id").val(projectId);
        $("#project_name").val(projectName);
        $("#new_status").val(status);
        $("#updateStatusModal").modal("show");
    });

    // ✅ Submit Update Status Form
    $("#updateStatusForm").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "update_status.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $("#updateStatusModal").modal("hide");
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function () {
                alert("AJAX request failed.");
            }
        });
    });

    // ✅ Mark Project as Complete
    $(document).on("click", ".mark-complete-btn", function () {
        let projectId = $(this).data("id");
        $.ajax({
            url: "complete_project.php",
            type: "POST",
            data: { project_id: projectId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function () {
                alert("AJAX request failed.");
            }
        });
    });

    // ✅ Open Edit Project Modal & Populate Fields
    $(document).on("click", ".edit-project-btn", function () {
        let projectId = $(this).data("id");
        let projectName = $(this).data("name") || '';
        let status = $(this).data("status") || 'Project Started';
        let points = $(this).data("points") || 0;
        let assignedUserId = $(this).data("assigned-user");

        $("#editProjectId").val(projectId);
        $("#editProjectName").val(projectName);
        $("#editStatus").val(status);
        $("#editPoints").val(points);

        // Ensure Assigned User is selected properly
        $("#editAssignedUser option").each(function () {
            $(this).prop("selected", $(this).val() == assignedUserId);
        });

        $("#editProjectModal").modal("show");
    });
});
