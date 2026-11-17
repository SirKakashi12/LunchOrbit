<?php


$jobPostingData = $_SESSION["jobPostingData"] ?? [];
$jobSource = $_SESSION["jobFormInput"] ?? $_SESSION["currentJobPost"] ?? [];

$jobId = $jobSource["job_id"] ?? "";
$jobTitle = $jobSource["job_title"] ?? "";
$jobCategory = $jobSource["job_category"] ?? "";
$companyDepartment = $jobSource["company_department"] ?? "";
$jobDescription = $jobSource["job_description"] ?? "";
$educationLevel = $jobSource["education_level"] ?? "";
$degreeCourse = $jobSource["degree_course"] ?? "";
$jobSkills = $jobSource["skills"] ?? [];
if (!is_array($jobSkills)) {
    $jobSkills = [];
}
$experienceLevel = $jobSource["experience_level"] ?? "";
$employmentType = $jobSource["employment_type"] ?? "";
$workSetup = $jobSource["work_setup"] ?? "";
$jobLocation = $jobSource["job_location"] ?? "";
$minimumSalary = $jobSource["minimum_salary"] ?? "";
$maximumSalary = $jobSource["maximum_salary"] ?? "";
$vacancies = $jobSource["vacancies"] ?? "";
$deadline = $jobSource["deadline"] ?? "";
$contactEmail = $jobSource["contact_email"] ?? "";
$additionalInstructions = $jobSource["additional_instructions"] ?? "";
$jobStatus = $jobSource["status"] ?? "";
$createdAt = $jobSource["created_at"] ?? "";
$totalApplicants = $jobSource["total_applicants"] ?? 0;
$totalReviewed = $jobSource["total_reviewed"] ?? 0;
$totalPending = $jobSource["total_pending"] ?? 0;

$jobAction = "";
if (($_GET["page"] ?? "") === "jobpost") {
    $jobAction = (string) ($_GET["job_action"] ?? "");
}

if (isset($_SESSION["errors_job"]) && is_array($_SESSION["errors_job"])) {
    echo '<div class="profile-alert" role="alert">';

    foreach ($_SESSION["errors_job"] as $value) {
        echo '<p class="profile-error">' . escape_value($value) . '</p>';
    }

    echo '</div>';
    unset($_SESSION["errors_job"]);
}

if (isset($_SESSION["job_notice"])) {
    echo '<div class="profile-alert" role="alert">';
    echo '<p class="success-message">' . escape_value($_SESSION["job_notice"]) . '</p>';
    echo '</div>';
    unset($_SESSION["job_notice"]);
}

?>

<?php

// first page job post
if(isset($_GET["page"]) &&  $_GET["page"] === "jobs")
{


    echo '<table class="job-table">';

    echo '    <tr>';
    echo '        <td colspan="3">Job Postings</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td class="job-stat-number">' . escape_value($jobPostingData["statistics"]["total_jobs"] ?? 0) . '</td>';
    echo '        <td class="job-stat-number">' . escape_value($jobPostingData["statistics"]["published_jobs"] ?? 0) . '</td>';
    echo '        <td class="job-stat-number">' . escape_value($jobPostingData["statistics"]["draft_jobs"] ?? 0) . '</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td class="job-stat-label">Total Jobs</td>';
    echo '        <td class="job-stat-label">Active</td>';
    echo '        <td class="job-stat-label">Draft</td>';
    echo '    </tr>';

    echo '</table>';


    echo '<table class="job-table">';

    echo '    <tr>';
    echo '        <td colspan="2">My Job Listings</td>';

    echo '        <td style="text-align:right;">';
    echo '            <form action="recruiters.php" method="GET">';
    echo '                <input type="hidden" name="page" value="jobpost">';
    echo '                <input type="hidden" name="job_action" value="create">';
    echo '                <button type="submit" class="job-btn job-btn-primary">';
    echo '                    + Create Job';
    echo '                </button>';
    echo '            </form>';
    echo '        </td>';

    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="3">';


    // Loop job listings
    $jobListings = $jobPostingData["job_postings"] ?? [];
    foreach ($jobListings as $job) {

        $listingId = $job["job_id"];
        $listingTitle = $job["job_title"];
        $listingCategory = $job["job_category"];
        $listingType = $job["employment_type"];
        $listingWorkSetup = $job["work_setup"];
        $listingCity = $job["city"];
        $listingProvince = $job["province"];
        $listingSalaryMin = $job["salary_min"];
        $listingSalaryMax = $job["salary_max"];
        $listingSalaryPeriod = $job["salary_period"];
        $listingVacancies = $job["vacancies"];
        $listingStatus = $job["status"];
        $listingDeadline = $job["application_deadline"];
        $listingCreatedAt = $job["created_at"];

        echo '<table class="job-listing">';

        echo '    <tr>';

        echo '        <td class="job-title">';
        echo              escape_value($listingTitle);
        echo '        </td>';

        echo '        <td class="job-id">';
        echo '            #ID-' . escape_value($listingId);
        echo '        </td>';

        echo '        <td  colspan="2" class="job-status ' .
                    ($listingStatus === "draft" ? "draft" : "") . '">';
        echo              escape_value(ucfirst($listingStatus));
        echo '        </td>';

        echo '    </tr>';

        echo '    <tr>';

        echo '        <td>';
        echo              escape_value($listingType);
        echo '        </td>';

        // View
        echo '        <td>';

        echo '            <form action="recruiters.php" method="GET">';
        echo '                <input type="hidden" name="page" value="jobpost">';
        echo '                <input type="hidden" name="job_action" value="view">';
        echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';

        echo '                <button type="submit" class="job-btn job-btn-secondary">';
        echo '                    View';
        echo '                </button>';

        echo '            </form>';

        echo '        </td>';

        // Edit
        echo '        <td>';

        echo '            <form action="recruiters.php" method="GET">';
        echo '                <input type="hidden" name="page" value="jobpost">';
        echo '                <input type="hidden" name="job_action" value="edit">';
        echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';

        echo '                <button type="submit" class="job-btn job-btn-secondary">';
        echo '                    Edit';
        echo '                </button>';

        echo '            </form>';

        echo '        </td>';

        echo '        <td>';

        echo '            <form action="recruiters.php" method="POST">';
        echo '                <input type="hidden" name="job_action" value="delete">';
        echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';

        echo '                <button type="submit" class="job-btn job-btn-danger">';
        echo '                    Delete';
        echo '                </button>';

        echo '            </form>';

        echo '        </td>';

        echo '    </tr>';

        echo '</table>';
    }


    echo '        </td>';
    echo '    </tr>';

    echo '</table>';
}

// create / edit jobposting


if ( isset($_GET["page"], $_GET["job_action"]) && $_GET["page"] === "jobpost" && ($_GET["job_action"] === "create" || $_GET["job_action"] === "edit")) {

    $formTitle = $_GET["job_action"] === "edit" ? "Edit Job Posting" : "Create Job Posting";

    echo '<form action="recruiters.php" method="POST">';

    echo '<input type="hidden" name="page" value="jobpost">';
    echo '<input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';


    /* BASIC INFORMATION */

    echo '<table class="job-table">';

    echo '    <tr>';
    echo '        <td>' . escape_value($formTitle) . '</td>';

    echo '        <td style="text-align:right;">';

    echo '            <button type="button" class="job-btn job-btn-secondary" onclick="history.back()">';
    echo '                Back';
    echo '            </button>';

    echo '        </td>';

    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Job Title</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';
    echo '            <input type="text" name="job_title" value="' . escape_value($jobTitle) . '" required>';
    echo '        </td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Job Category</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';
    echo '            <input type="text" name="job_category" value="' . escape_value($jobCategory) . '" required>';
    echo '        </td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Company / Department</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';
    echo '            <input type="text" name="company_department" value="' . escape_value($companyDepartment) . '" required>';
    echo '        </td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Job Description</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';
    echo '            <textarea name="job_description" placeholder="Write the job description..." required>' . escape_value($jobDescription) . '</textarea>';
    echo '        </td>';
    echo '    </tr>';

    echo '</table>';


    /* JOB REQUIREMENTS */

    echo '<table class="job-table">';

    echo '    <tr>';
    echo '        <td colspan="2">Job Requirements</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Education Level</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';

    echo '            <select name="education_level" required>';

    $educationOptions = [
        "junior_high_school" => "Junior High School",
        "senior_high_school" => "Senior High School",
        "vocational_certificate" => "Vocational Certificate",
        "associate_degree" => "Associate Degree",
        "bachelor_degree" => "Bachelor Degree",
        "masters_degree" => "Masters Degree",
        "doctoral_degree" => "Doctoral Degree",
        "other" => "Other"
    ];

    foreach ($educationOptions as $value => $label) {

        $selected = ($educationLevel === $value) ? "selected" : "";

        echo '<option value="' . escape_value($value) . '" ' . $selected . '>';
        echo escape_value($label);
        echo '</option>';
    }

    echo '            </select>';

    echo '        </td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">Degree / Course</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';
    echo '            <input type="text" name="degree_course" value="' . escape_value($degreeCourse) . '">';
    echo '        </td>';
    echo '    </tr>';

    /* REQUIRED SKILLS */

    echo '    <tr>';
    echo '        <td colspan="2">Required Skills</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';

    echo '            <div class="skills-container" id="skills-container">';

    foreach ($jobSkills as $skill) {

        echo '                <div class="skill-item">';

        echo '                    <input type="text" name="skills[]"  value="' . escape_value($skill) . '" placeholder="Skill" readonly required>';

        echo '                    <button type="button" class="skill-remove" onclick="removeSkill(this)">';
        echo '                        &times;';
        echo '                    </button>';

        echo '                </div>';
    }

    echo '            </div>';

    echo '            <div class="skill-add-area" style="margin-top:12px;">';

    echo '                <input type="text" id="new-skill" placeholder="Enter a skill">';

    echo '                <button type="button" class="job-btn job-btn-secondary" onclick="addSkill()">';
    echo '                    + Add Skill';
    echo '                </button>';

    echo '            </div>';

    echo '        </td>';
    echo '    </tr>';


    echo '    <tr>';
    echo '        <td colspan="2">Experience Level</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="2">';

    echo '            <select name="experience_level" required>';

    $experienceOptions = [
        "entry_level" => "Entry-Level",
        "internship_or_ojt_only" => "Internship / OJT Only",
        "under_2_years" => "Under 2 Years",
        "2_to_5_years" => "2 to 5 Years",
        "over_5_years" => "Over 5 Years"
    ];

    foreach ($experienceOptions as $value => $label) {

        $selected = ($experienceLevel === $value) ? "selected" : "";

        echo '<option value="' . escape_value($value) . '" ' . $selected . '>';
        echo escape_value($label);
        echo '</option>';
    }

    echo '            </select>';

    echo '        </td>';
    echo '    </tr>';

    echo '</table>';


    /* EMPLOYMENT DETAILS */

    echo '<table class="job-table">';

    echo '    <tr>';
    echo '        <td colspan="3">Employment Details</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td>Employment Type</td>';
    echo '        <td>Work Setup</td>';
    echo '        <td>Job Location</td>';
    echo '    </tr>';

    echo '    <tr>';

    echo '        <td>';
    echo '            <select name="employment_type" required>';

    $employmentOptions = [
        "full_time" => "Full Time",
        "part_time" => "Part Time",
        "internship" => "Internship",
        "contract" => "Contract",
        "freelance" => "Freelance",
        "any" => "Any"
    ];

    foreach ($employmentOptions as $value => $label) {

        $selected = ($employmentType === $value) ? "selected" : "";

        echo '<option value="' . escape_value($value) . '" ' . $selected . '>';
        echo escape_value($label);
        echo '</option>';
    }

    echo '            </select>';
    echo '        </td>';

    echo '        <td>';
    echo '            <select name="work_setup" required>';

    $workOptions = [
        "onsite" => "Onsite",
        "hybrid" => "Hybrid",
        "remote" => "Remote",
        "any" => "Any"
    ];

    foreach ($workOptions as $value => $label) {

        $selected = ($workSetup === $value) ? "selected" : "";

        echo '<option value="' . escape_value($value) . '" ' . $selected . '>';
        echo escape_value($label);
        echo '</option>';
    }

    echo '            </select>';
    echo '        </td>';

    echo '        <td>';
    echo '            <input type="text" name="job_location" value="' . escape_value($jobLocation) . '" required>';
    echo '        </td>';

    echo '    </tr>';

    echo '    <tr>';
    echo '        <td>Minimum Salary</td>';
    echo '        <td>Maximum Salary</td>';
    echo '        <td>Number of Vacancies</td>';
    echo '    </tr>';

    echo '    <tr>';

    echo '        <td>';
    echo '            <input type="number" name="minimum_salary" value="' . escape_value($minimumSalary) . '" min="0">';
    echo '        </td>';

    echo '        <td>';
    echo '            <input type="number" name="maximum_salary" value="' . escape_value($maximumSalary) . '" min="0">';
    echo '        </td>';

    echo '        <td>';
    echo '            <input type="number" name="vacancies" value="' . escape_value($vacancies) . '" min="1" required>';
    echo '        </td>';

    echo '    </tr>';

    echo '    <tr>';
    echo '        <td>Application Deadline</td>';
    echo '        <td colspan="2">Contact Email</td>';
    echo '    </tr>';

    echo '    <tr>';

    echo '        <td>';
    echo '            <input type="date" name="deadline" value="' . escape_value($deadline) . '">';
    echo '        </td>';

    echo '        <td colspan="2">';
    echo '            <input type="email" name="contact_email" value="' . escape_value($contactEmail) . '" required>';
    echo '        </td>';

    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="3">Additional Instructions</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td colspan="3">';
    echo '            <textarea name="additional_instructions" placeholder="Additional instructions for applicants...">' . escape_value($additionalInstructions) . '</textarea>';
    echo '        </td>';
    echo '    </tr>';

    echo '    <tr>';

    echo '        <td colspan="3" style="text-align:right;">';

    echo '            <button type="submit" name="job_status" value="draft" class="job-btn job-btn-secondary" formnovalidate>';
    echo '                Save as Draft';
    echo '            </button>';

    echo '            <button type="submit" name="job_status" value="active" class="job-btn job-btn-primary">';
    echo '                Publish Job';
    echo '            </button>';

    echo '        </td>';

    echo '    </tr>';

    echo '</table>';

    echo '</form>';
}

// view jobposting

if ($jobAction === "view") {

    echo '<div class="job-view-section">';

    echo '    <table>';

    echo '        <tr>';
    echo '            <td>';

    echo '                <span class="job-status">' . escape_value(ucfirst($jobStatus)) . '</span>';

    echo '            </td>';

    echo '            <td style="text-align:right;">';
    echo '                <span class="job-view-meta">Created: ' . escape_value($createdAt) . '</span>';
    echo '            </td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td colspan="2">';

    echo '                <div class="job-view-title">' . escape_value($jobTitle) . '</div>';

    echo '                <div class="job-view-meta" style="margin-top:8px;">';
    echo                      escape_value($companyDepartment);
    echo '                </div>';

    echo '            </td>';
    echo '        </tr>';

    echo '    </table>';

    echo '</div>';


    /* JOB OVERVIEW */

    echo '<div class="job-view-section">';

    echo '    <table>';

    echo '        <tr>';
    echo '            <td colspan="2">Job Overview</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Location</td>';
    echo '            <td>Salary</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>' . escape_value($jobLocation) . '</td>';
    echo '            <td>₱' . escape_value($minimumSalary) . ' - ₱' . escape_value($maximumSalary) . '</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Vacancies</td>';
    echo '            <td>Deadline</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>' . escape_value($vacancies) . ' Positions</td>';
    echo '            <td>' . escape_value($deadline ?: "Not specified") . '</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Employment Type</td>';
    echo '            <td>Work Setup</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>' . escape_value(ucwords(str_replace("_", " ", $employmentType))) . '</td>';
    echo '            <td>' . escape_value(ucfirst($workSetup)) . '</td>';
    echo '        </tr>';

    echo '    </table>';

    echo '</div>';

    echo '<div class="job-view-section">';

    echo '    <table>';

    echo '        <tr>';
    echo '            <td>Job Description</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>' . nl2br(escape_value($jobDescription)) . '</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Qualifications</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';

    echo '                • ' . escape_value(ucwords(str_replace("_", " ", $educationLevel))) . ' in ' . escape_value($degreeCourse);

    echo '                <br><br>• Experience: ' . escape_value(ucwords(str_replace("_", " ", $experienceLevel)));

    echo '            </td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Required Skills</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';

    echo '                <div class="job-skill-display">';

    foreach ($jobSkills as $skill) {

        if (trim((string)$skill) === "") {
            continue;
        }

        echo '<span>' . escape_value($skill) . '</span>';
    }

    echo '                </div>';

    echo '            </td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>Additional Instructions</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>' . nl2br(escape_value($additionalInstructions)) . '</td>';
    echo '        </tr>';

    echo '    </table>';

    echo '</div>';



    echo '<div class="job-view-section">';

    echo '    <table>';

    echo '    <tr>';
    echo '        <td colspan="3">Application Statistics</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td class="job-stat-number">' . escape_value($totalApplicants) . '</td>';
    echo '        <td class="job-stat-number">' . escape_value($totalReviewed) . '</td>';
    echo '        <td class="job-stat-number">' . escape_value($totalPending) . '</td>';
    echo '    </tr>';

    echo '    <tr>';
    echo '        <td class="job-stat-label">Total Applicants</td>';
    echo '        <td class="job-stat-label">Reviewed</td>';
    echo '        <td class="job-stat-label">Pending</td>';
    echo '    </tr>';

    echo '    </table>';

    echo '</div>';



    echo '<table class="job-table">';

    echo '    <tr>';

    echo '        <td>';

    echo '            <form action="recruiters.php" method="GET">';

    echo '                <input type="hidden" name="page" value="jobpost">';
    echo '                <input type="hidden" name="job_action" value="edit">';
    echo '                <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';

    echo '                <button type="submit" class="job-btn job-btn-primary">';
    echo '                    Edit Job';
    echo '                </button>';

    echo '            </form>';

    echo '        </td>';

    echo '        <td>';

    echo '            <form action="recruiters.php" method="POST">';

    echo '                <input type="hidden" name="job_action" value="close">';
    echo '                <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';

    echo '                <button type="submit" class="job-btn job-btn-secondary">';
    echo '                    Close Job';
    echo '                </button>';

    echo '            </form>';

    echo '        </td>';

    echo '        <td>';

    echo '            <form action="recruiters.php" method="POST">';

    echo '                <input type="hidden" name="job_action" value="delete">';
    echo '                <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';

    echo '                <button type="submit" class="job-btn job-btn-danger">';
    echo '                    Delete Job';
    echo '                </button>';

    echo '            </form>';

    echo '        </td>';

    echo '    </tr>';

    echo '</table>';
}

?>
