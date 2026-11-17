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


function check_profile_errors(): void
{
    // Validation errors
    if (isset($_SESSION["errors_profile"])) {

        $errors = $_SESSION["errors_profile"];

        echo '<div class="profile-alert" role="alert">';

        foreach ($errors as $key => $value) {
            echo '<p class="profile-error">'
                . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8')
                . ' : '
                . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                . '</p>';
        }

        echo '</div>';

        unset($_SESSION["errors_profile"]);
    } else if (isset($_GET["profile"]) && $_GET["profile"] === "success") {
      echo '<div class="profile-alert" role="alert">';
      echo '<p class="success-message"> Congratiolation Regestration is a Successfull Please Login </p>';
      echo '</div>';
    
    }  
    // Incomplete company profile warning
    if (
        isset($_SESSION["profileInfo"]["companyProfileIncomplete"]) &&
        $_SESSION["profileInfo"]["companyProfileIncomplete"] === true
    ) {
        echo '<div class="profile-alert" role="alert">';
        echo '<p class="profile-warning">';
        echo 'Your company profile is incomplete.Your A New member pls go to Company Profile Page.';
        echo '</p>';
        echo '</div>';
    }
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
            <?php
                check_profile_errors();
            ?>
            <!-- Topbar -->
            <header class="topbar">

                <div>
                    <h1>Recruiter Dashboard</h1>
                    <p>Manage your company and recruitment activities.</p>
                </div>

                <div class="account-info">
                    <div class="account-avatar">
                          <?php echo strtoupper(substr($_SESSION["user_username"] ?? "R", 0, 1)); ?>
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
                     require_once "jobposting.php"; 


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



function addSkill() {
    const input = document.getElementById("new-skill");
    const skillName = input.value.trim();

    if (skillName === "") {
        input.focus();
        return;
    }

    const container = document.getElementById("skills-container");

    const skillItem = document.createElement("div");
    skillItem.className = "skill-item";

    const skillInput = document.createElement("input");
    skillInput.type = "text";
    skillInput.name = "skills[]";
    skillInput.value = skillName;
    skillInput.required = true;

    const removeButton = document.createElement("button");
    removeButton.type = "button";
    removeButton.className = "skill-remove";
    removeButton.innerHTML = "&times;";
    removeButton.onclick = function () {
        removeSkill(this);
    };

    skillItem.appendChild(skillInput);
    skillItem.appendChild(removeButton);

    container.appendChild(skillItem);

    input.value = "";
    input.focus();
}

function removeSkill(button) {
    button.closest(".skill-item").remove();
}

// Allow Enter to add a skill
document.getElementById("new-skill")?.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        addSkill();
    }
});


</script>
<?php
unset($_SESSION["profileInfo"]);
unset($_SESSION["errors_profile"])
?>