<?php
declare(strict_types=1);
require_once "../private/session.php";

function display_username():void{
    echo htmlspecialchars(
        $_SESSION["user_username"] ?? "Recruiter",
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LunchOrbit - Recruiter Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="dashboard-container">

        <!-- Sidebar -->
<?php  require_once "nav.php"; ?>

        <!-- Main Area -->
        <main class="main-area">

            <!-- Topbar -->
            <header class="topbar">

                <div>
                    <h1>Recruiter Dashboard</h1>
                    <p>Manage your company and recruitment activities.</p>
                </div>

                <div class="account-info">
                    <div class="account-avatar">
                        R
                    </div>

                    <div>
                        <strong><?php display_username(); ?></strong>
                        <small>Recruiter</small>
                    </div>
                </div>

            </header>


            <!-- Main Content -->
            <section class="content">

                    <!-- taee -->
                    <?php 
                     require_once "dashboard.php"; 
                     require_once "profile.php"; 


                     ?>

            </section>

        </main>

    </div>

</body>
</html>

<script>
    
document.querySelectorAll(".profile-table").forEach(table => {

    const editBtn = table.querySelector(".edit-btn");
    const updateBtn = table.querySelector(".update-btn");
    const inputs = table.querySelectorAll("input");

    editBtn.addEventListener("click", () => {

        const isEditing = editBtn.dataset.editing === "true";

        if (isEditing) {
            // Disable editing
            inputs.forEach(input => {
                input.readOnly = true;
            });

            updateBtn.disabled = true;
            editBtn.dataset.editing = "false";

        } else {
            // Enable editing
            inputs.forEach(input => {
                input.readOnly = false;
            });

            updateBtn.disabled = false;
            editBtn.dataset.editing = "true";
        }
    });

});

</script>
