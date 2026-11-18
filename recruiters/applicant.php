<?php

if (!function_exists("escape_value")) {
    function escape_value($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ""),
            ENT_QUOTES,
            "UTF-8"
        );
    }
}

function applicant_label($value): string
{
    $text = trim((string) ($value ?? ""));
    if ($text === "") {
        return "Not specified";
    }

    $labels = [
        "accepted" => "Approved",
        "rejected" => "Declined",
        "junior_high_school" => "Junior High School",
        "senior_high_school" => "Senior High School",
        "vocational_certificate" => "Vocational Certificate",
        "associate_degree" => "Associate Degree",
        "bachelor_degree" => "Bachelor Degree",
        "masters_degree" => "Master's Degree",
        "doctoral_degree" => "Doctoral Degree",
        "currently_studying" => "Currently Studying",
        "graduated" => "Graduated",
        "not_completed" => "Not Completed",
        "none" => "No Experience",
        "internship_or_ojt_only" => "Internship / OJT Only",
        "under_2_years" => "Under 2 Years",
        "2_to_5_years" => "2 to 5 Years",
        "over_5_years" => "Over 5 Years",
        "onsite" => "On-site",
        "hybrid" => "Hybrid",
        "remote" => "Remote",
        "any" => "Any",
        "full_time" => "Full-Time",
        "part_time" => "Part-Time",
        "internship" => "Internship",
        "contract" => "Contract",
        "freelance" => "Freelance",
        "pending" => "Pending",
        "reviewing" => "Reviewing",
        "shortlisted" => "Shortlisted",
        "interview" => "Interview",
        "withdrawn" => "Withdrawn",
    ];

    return $labels[$text] ?? ucwords(str_replace("_", " ", $text));
}

function applicant_notices(): void
{
    if (isset($_SESSION["errors_applicant"]) && is_array($_SESSION["errors_applicant"])) {
        echo '<div class="profile-alert" role="alert">';
        foreach ($_SESSION["errors_applicant"] as $message) {
            echo '<p class="profile-error">' . escape_value($message) . '</p>';
        }
        echo '</div>';
        unset($_SESSION["errors_applicant"]);
    }

    if (isset($_SESSION["applicant_notice"])) {
        echo '<div class="profile-alert" role="alert">';
        echo '<p class="success-message">' . escape_value($_SESSION["applicant_notice"]) . '</p>';
        echo '</div>';
        unset($_SESSION["applicant_notice"]);
    }
}

function applicant_decision_form(int $applicationId, string $status, string $returnPage): void
{
    if ($status === "withdrawn") {
        echo '<span class="applicant-muted">Withdrawn</span>';
        return;
    }

    echo '<form action="recruiters.php" method="POST" class="applicant-actions">';
    echo '<input type="hidden" name="application_id" value="' . $applicationId . '">';
    echo '<input type="hidden" name="return_page" value="' . escape_value($returnPage) . '">';

    if ($status !== "accepted") {
        echo '<button type="submit" name="applicant_action" value="approve" class="applicant-approve">Approve</button>';
    }

    if ($status !== "rejected") {
        echo '<button type="submit" name="applicant_action" value="decline" class="applicant-decline">Decline</button>';
    }

    echo '</form>';
}

$applicants = $_SESSION["applicantData"] ?? [];
$jobOptions = $_SESSION["applicantJobOptions"] ?? [];
$selectedJob = (int) ($_SESSION["applicantJobFilter"] ?? 0);

if (($_GET["page"] ?? "") === "applicants"):
    applicant_notices();
?>

<h2>Applicants</h2>
<p>Manage applicants who applied to your job postings.</p>

<form action="recruiters.php" method="POST" class="applicant-filter">
    <input type="hidden" name="navigate" value="applicants">
    <label for="job_filter">Job post</label>
    <select name="job_filter" id="job_filter">
        <option value="0">All job posts</option>
        <?php foreach ($jobOptions as $job): ?>
            <option value="<?= (int) $job["job_id"] ?>"<?= $selectedJob === (int) $job["job_id"] ? " selected" : "" ?>>
                <?= escape_value($job["job_title"]) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<table class="applicant-table">

    <thead>
        <tr>
            <th>Applicant</th>
            <th>Position</th>
            <th>Education</th>
            <th>Experience</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

        <?php if (empty($applicants)): ?>

            <tr>
                <td colspan="7">
                    No applicants found.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($applicants as $applicant): ?>
                <?php $status = (string) ($applicant["status"] ?? ""); ?>

                <tr>

                    <td>
                        <strong>
                            <?= escape_value($applicant["fullname"]) ?>
                        </strong>
                        <br>
                        <?= escape_value($applicant["email"]) ?>
                    </td>

                    <td>
                        <?= escape_value($applicant["job_title"]) ?>
                    </td>

                    <td>
                        <?= escape_value(applicant_label($applicant["education_level"] ?? "")) ?>
                        <br>
                        <?= escape_value($applicant["degree_course"] ?? "") ?>
                    </td>

                    <td>
                        <?= escape_value(applicant_label($applicant["work_experience_level"] ?? "none")) ?>
                    </td>

                    <td>
                        <?= escape_value($applicant["applied_at"]) ?>
                    </td>

                    <td>
                        <?= escape_value(applicant_label($status)) ?>
                    </td>

                    <td>
                        <form action="recruiters.php" method="POST" class="applicant-actions">
                            <input type="hidden" name="application_id" value="<?= (int) $applicant["application_id"] ?>">
                            <button type="submit" name="applicant_action" value="view">View</button>
                        </form>
                        <?php applicant_decision_form((int) $applicant["application_id"], $status, "applicants"); ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </tbody>

</table>

<?php endif; ?>

<?php if (($_GET["page"] ?? "") === "applicant"):
    $profile = $_SESSION["applicantProfile"] ?? [];
    applicant_notices();

    if ($profile === [] || !is_array($profile)):
?>
    <h2>Applicant Profile</h2>
    <p>That applicant profile could not be loaded.</p>
    <form action="recruiters.php" method="POST">
        <input type="hidden" name="job_filter" value="<?= $selectedJob ?>">
        <button type="submit" name="navigate" value="applicants" class="job-btn job-btn-secondary">Back to Applicants</button>
    </form>
<?php else:
    $skills = $profile["skills"] ?? [];
    if (!is_array($skills)) {
        $skills = [];
    }
    $status = (string) ($profile["application_status"] ?? "");
?>

<h2>Applicant Profile</h2>
<p>Read-only profile for <?= escape_value($profile["fullname"]) ?>.</p>

<form action="recruiters.php" method="POST" class="applicant-profile-nav">
    <input type="hidden" name="job_filter" value="<?= $selectedJob ?>">
    <button type="submit" name="navigate" value="applicants" class="job-btn job-btn-secondary">Back to Applicants</button>
</form>

<table class="profile-table">
    <tr>
        <td>Application</td>
        <td colspan="2" id="right"><?= escape_value(applicant_label($status)) ?></td>
    </tr>
    <tr>
        <td>Position: <?= escape_value($profile["job_title"]) ?></td>
        <td></td>
        <td id="right"><?= escape_value($profile["applied_at"]) ?></td>
    </tr>
    <tr>
        <td>Vacancies: <?= (int) $profile["vacancies"] ?></td>
        <td>Job status: <?= escape_value(applicant_label($profile["job_status"])) ?></td>
        <td>
            <?php applicant_decision_form((int) $profile["application_id"], $status, "applicant"); ?>
        </td>
    </tr>
    <?php if (trim((string) $profile["cover_letter"]) !== ""): ?>
    <tr>
        <td colspan="3">
            Cover letter:
            <textarea readonly><?= escape_value($profile["cover_letter"]) ?></textarea>
        </td>
    </tr>
    <?php endif; ?>
</table>

<table class="profile-table">
    <tr>
        <td>Personal Info</td>
        <td colspan="2" id="right">Job Seeker</td>
    </tr>
    <tr>
        <td>
            Username:
            <input type="text" value="<?= escape_value($profile["username"]) ?>" readonly>
        </td>
        <td></td>
        <td id="right"><?= escape_value($profile["lastupdate"]) ?></td>
    </tr>
    <tr>
        <td>
            Fullname:
            <input type="text" value="<?= escape_value($profile["fullname"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>CONTACT</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Email:
            <input type="email" value="<?= escape_value($profile["email"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Cell No:
            <input type="text" value="<?= escape_value($profile["cellno"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
</table>

<table class="profile-table">
    <tr>
        <td colspan="3">Job Seeker Profile</td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>EDUCATION</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Education Level:
            <input type="text" value="<?= escape_value(applicant_label($profile["education_level"])) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Education Status:
            <input type="text" value="<?= escape_value($profile["education_status"] === "" ? "Not specified" : applicant_label($profile["education_status"])) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            School Name:
            <input type="text" value="<?= escape_value($profile["school_name"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Degree / Course:
            <input type="text" value="<?= escape_value($profile["degree_course"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Graduation Year:
            <input type="text" value="<?= escape_value($profile["graduation_year"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>EXPERIENCE</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Work Experience Level:
            <input type="text" value="<?= escape_value(applicant_label($profile["work_experience_level"])) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>LOCATION</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            City:
            <input type="text" value="<?= escape_value($profile["city"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Province:
            <input type="text" value="<?= escape_value($profile["province"]) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>PROFILE SUMMARY</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3">
            Profile Summary:
            <textarea readonly><?= escape_value($profile["profile_summary"]) ?></textarea>
        </td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>JOB PREFERENCES</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Preferred Work Setup:
            <input type="text" value="<?= escape_value(applicant_label($profile["preferred_work_setup"])) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            Preferred Employment Type:
            <input type="text" value="<?= escape_value(applicant_label($profile["preferred_employment_type"])) ?>" readonly>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="profile-section-heading"><strong>SKILLS</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3">
            <?php if ($skills === []): ?>
                <input type="text" value="No skills listed" readonly>
            <?php else: ?>
                <div class="skills-container">
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-item">
                            <input type="text" value="<?= escape_value($skill) ?>" readonly>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </td>
    </tr>
</table>

<?php endif; ?>
<?php endif; ?>
