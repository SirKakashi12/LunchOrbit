<?php
declare(strict_types=1);
require_once "../private/session.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../signIn/view.php");
    exit();
}

require_once "../private/database.php";
require_once "model.php";
require_once "contr.php";

function escape_value($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

function label_from_value($value): string
{
    $text = trim((string) ($value ?? ""));
    if ($text === "") {
        return "Not specified";
    }

    return ucwords(str_replace("_", " ", $text));
}

function format_location($city, $province): string
{
    $cityText = trim((string) ($city ?? ""));
    $provinceText = trim((string) ($province ?? ""));

    if ($cityText !== "" && $provinceText !== "") {
        return $cityText . ", " . $provinceText;
    }

    if ($cityText !== "") {
        return $cityText;
    }

    if ($provinceText !== "") {
        return $provinceText;
    }

    return "Not specified";
}

function format_salary($minimum, $maximum): string
{
    $hasMinimum = $minimum !== null && $minimum !== "";
    $hasMaximum = $maximum !== null && $maximum !== "";

    $formatAmount = static function ($amount): string {
        return "PHP " . number_format((float) $amount, 2);
    };

    if ($hasMinimum && $hasMaximum) {
        return $formatAmount($minimum) . " - " . $formatAmount($maximum);
    }

    if ($hasMinimum) {
        return "From " . $formatAmount($minimum);
    }

    if ($hasMaximum) {
        return "Up to " . $formatAmount($maximum);
    }

    return "Not specified";
}

function display_username(): void
{
    echo escape_value($_SESSION["user_username"] ?? "Job Seeker");
}

$user_id = (int) $_SESSION["user_id"];

if (get_jobseeker_user_info($pdo, $user_id) === false) {
    header("Location: ../signIn/view.php");
    exit();
}

$allowedPages = ["dashboard", "jobs", "job", "applications", "saved", "profile"];
$page = (string) ($_GET["page"] ?? "dashboard");

if (!in_array($page, $allowedPages, true)) {
    $page = "dashboard";
}

$jobseekersId = get_jobseeker_id_by_user($pdo, $user_id);
$seekerId = $jobseekersId === false ? 0 : $jobseekersId;

if ($page === "dashboard") {
    $_SESSION["dashboardData"] = get_jobseeker_dashboard($pdo, $user_id);
}

if ($page === "jobs") {
    $_SESSION["findJobData"] = build_find_job_data(
        $pdo,
        $user_id,
        job_search_filters_from_request($_GET)
    );
}

if ($page === "job") {
    $jobId = (int) ($_GET["job_id"] ?? 0);
    $currentJob = $jobId > 0 ? build_current_open_job($pdo, $user_id, $jobId) : false;

    if ($currentJob === false) {
        $_SESSION["currentJob"] = [];
        $_SESSION["errors_jobseeker"] = [
            "job_missing" => "That job is not open.",
        ];
    } else {
        $_SESSION["currentJob"] = $currentJob;
    }
}

if ($page === "applications") {
    $_SESSION["applicationData"] = get_jobseeker_applications($pdo, $seekerId);
}

if ($page === "saved") {
    $_SESSION["savedJobData"] = get_jobseeker_saved_jobs($pdo, $seekerId);
}

$profileInfo = build_jobseeker_profile_session($pdo, $user_id);

if ($page === "profile" && isset($_SESSION["jobseekerFormInput"]) && is_array($_SESSION["jobseekerFormInput"])) {
    $profileInfo = array_merge($profileInfo, $_SESSION["jobseekerFormInput"]);
}

$_SESSION["profileInfo"] = $profileInfo;

$pageCopy = [
    "dashboard" => ["Job Seeker Dashboard", "See open roles, applications, and saved jobs."],
    "jobs" => ["Find Jobs", "Browse open roles and save the ones you want."],
    "job" => ["Job Details", "Review the role and send an application."],
    "applications" => ["My Applications", "Track the roles you have applied for."],
    "saved" => ["Saved Jobs", "Come back to roles you want to apply for."],
    "profile" => ["My Profile", "Keep your contact details and job preferences up to date."],
];

$pageTitle = $pageCopy[$page][0];
$pageSubtitle = $pageCopy[$page][1];

function check_profile_errors(): void
{
    if (isset($_SESSION["errors_profile"]) && is_array($_SESSION["errors_profile"])) {
        echo '<div class="profile-alert" role="alert">';

        foreach ($_SESSION["errors_profile"] as $key => $value) {
            echo '<p class="profile-error">'
                . escape_value($key)
                . ' : '
                . escape_value($value)
                . '</p>';
        }

        echo '</div>';
        unset($_SESSION["errors_profile"]);
    } elseif (isset($_GET["profile"]) && $_GET["profile"] === "success") {
        echo '<div class="profile-alert" role="alert">';
        echo '<p class="success-message">Profile updated.</p>';
        echo '</div>';
    }

    if (isset($_SESSION["errors_jobseeker"]) && is_array($_SESSION["errors_jobseeker"])) {
        echo '<div class="profile-alert" role="alert">';

        foreach ($_SESSION["errors_jobseeker"] as $value) {
            echo '<p class="profile-error">' . escape_value($value) . '</p>';
        }

        echo '</div>';
        unset($_SESSION["errors_jobseeker"]);
    }

    if (isset($_SESSION["jobseeker_notice"])) {
        echo '<div class="profile-alert" role="alert">';
        echo '<p class="success-message">' . escape_value($_SESSION["jobseeker_notice"]) . '</p>';
        echo '</div>';
        unset($_SESSION["jobseeker_notice"]);
    }

    if (
        isset($_SESSION["profileInfo"]["profileIncomplete"]) &&
        $_SESSION["profileInfo"]["profileIncomplete"] === true
    ) {
        echo '<div class="profile-alert" role="alert">';
        echo '<p class="profile-warning">';
        echo 'Your job seeker profile is incomplete. Add your education and summary before you apply.';
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
    <title>LunchOrbit - <?php echo escape_value($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="dashboard-container">

<?php require_once "nav.php"; ?>

        <main class="main-area">
            <?php check_profile_errors(); ?>

            <header class="topbar">

                <div>
                    <h1><?php echo escape_value($pageTitle); ?></h1>
                    <p><?php echo escape_value($pageSubtitle); ?></p>
                </div>

                <div class="account-info">
                    <div class="account-avatar">
                          <?php echo escape_value(strtoupper(substr($_SESSION["user_username"] ?? "J", 0, 1))); ?>
                   </div>

                    <div>
                        <strong><?php display_username(); ?></strong>
                        <small>Job Seeker</small>
                    </div>
                </div>

            </header>

            <section class="content">

                    <?php
                     require_once "dashboard.php";
                     require_once "findjob.php";
                     require_once "applications.php";
                     require_once "savedjob.php";
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
    const fields = table.querySelectorAll("[data-editable]");

    if (!editBtn || !updateBtn) {
        return;
    }

    editBtn.addEventListener("click", () => {

        const isEditing = editBtn.dataset.editing === "true";

        fields.forEach(field => {
            if (field.tagName === "SELECT") {
                field.disabled = isEditing;
            } else {
                field.readOnly = isEditing;
            }
        });

        updateBtn.disabled = isEditing;
        editBtn.dataset.editing = isEditing ? "false" : "true";
    });

});

function addSkill() {
    const input = document.getElementById("new-skill");
    if (!input) {
        return;
    }

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
    skillInput.readOnly = true;
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

document.getElementById("new-skill")?.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        addSkill();
    }
});

</script>
<?php
unset($_SESSION["profileInfo"]);
unset($_SESSION["jobseekerFormInput"]);
unset($_SESSION["dashboardData"]);
unset($_SESSION["findJobData"]);
unset($_SESSION["currentJob"]);
unset($_SESSION["applicationData"]);
unset($_SESSION["savedJobData"]);
?>
