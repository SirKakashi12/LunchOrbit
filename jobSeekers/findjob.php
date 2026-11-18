<?php

$findJobData = $_SESSION["findJobData"] ?? [];
$filters = $findJobData["filters"] ?? [
    "keyword" => "",
    "employment_type" => "",
    "work_setup" => "",
];
$jobs = $findJobData["jobs"] ?? [];
$currentJob = $_SESSION["currentJob"] ?? [];

if (($_GET["page"] ?? "") === "jobs") {

    echo '<form action="view.php" method="GET">';
    echo '    <input type="hidden" name="page" value="jobs">';
    echo '    <table class="job-table">';
    echo '        <tr>';
    echo '            <td colspan="3">Find Jobs</td>';
    echo '        </tr>';
    echo '        <tr>';
    echo '            <td>Keyword</td>';
    echo '            <td>Employment Type</td>';
    echo '            <td>Work Setup</td>';
    echo '        </tr>';
    echo '        <tr>';
    echo '            <td>';
    echo '                <input type="text" name="keyword" value="' . escape_value($filters["keyword"] ?? "") . '" placeholder="Title, company, or category">';
    echo '            </td>';
    echo '            <td>';
    echo '                <select name="employment_type">';
    echo '                    <option value="">Any employment type</option>';

    $employmentOptions = [
        "full_time" => "Full Time",
        "part_time" => "Part Time",
        "internship" => "Internship",
        "contract" => "Contract",
        "freelance" => "Freelance",
    ];

    foreach ($employmentOptions as $value => $label) {
        $selected = (($filters["employment_type"] ?? "") === $value) ? " selected" : "";
        echo '<option value="' . escape_value($value) . '"' . $selected . '>' . escape_value($label) . '</option>';
    }

    echo '                </select>';
    echo '            </td>';
    echo '            <td>';
    echo '                <select name="work_setup">';
    echo '                    <option value="">Any work setup</option>';

    $workOptions = [
        "onsite" => "Onsite",
        "hybrid" => "Hybrid",
        "remote" => "Remote",
    ];

    foreach ($workOptions as $value => $label) {
        $selected = (($filters["work_setup"] ?? "") === $value) ? " selected" : "";
        echo '<option value="' . escape_value($value) . '"' . $selected . '>' . escape_value($label) . '</option>';
    }

    echo '                </select>';
    echo '            </td>';
    echo '        </tr>';
    echo '        <tr>';
    echo '            <td colspan="3">';
    echo '                <button type="submit" class="job-btn job-btn-primary">Search</button>';
    echo '            </td>';
    echo '        </tr>';
    echo '    </table>';
    echo '</form>';

    echo '<table class="job-table">';
    echo '    <tr>';
    echo '        <td colspan="3">Open Roles (' . escape_value(count($jobs)) . ')</td>';
    echo '    </tr>';
    echo '    <tr>';
    echo '        <td colspan="3">';

    if (empty($jobs)) {
        echo '<p>No open jobs match this search.</p>';
    }

    foreach ($jobs as $job) {
        $listingId = $job["job_id"];
        $location = format_location($job["city"] ?? "", $job["province"] ?? "");
        $meta = label_from_value($job["employment_type"] ?? "")
            . " · "
            . label_from_value($job["work_setup"] ?? "")
            . " · "
            . $location;

        echo '<table class="job-listing">';
        echo '    <tr>';
        echo '        <td class="job-title">' . escape_value($job["job_title"] ?? "") . '</td>';
        echo '        <td class="job-id">' . escape_value($job["companyName"] ?? "") . '</td>';
        echo '        <td class="job-status">Open</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>' . escape_value($meta) . '<br>' . escape_value(format_salary($job["salary_min"] ?? null, $job["salary_max"] ?? null));

        $matches = (int) ($job["skill_matches"] ?? 0);
        if ($matches > 0) {
            echo '<br>' . escape_value($matches) . ' skill match' . ($matches === 1 ? "" : "es");
        }

        if ((int) ($job["has_applied"] ?? 0) > 0) {
            echo '<br>Applied';
        }

        echo '        </td>';
        echo '        <td>';
        echo '            <form action="view.php" method="GET">';
        echo '                <input type="hidden" name="page" value="job">';
        echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';
        echo '                <button type="submit" class="job-btn job-btn-secondary">View</button>';
        echo '            </form>';
        echo '        </td>';
        echo '        <td>';

        if ((int) ($job["is_saved"] ?? 0) > 0) {
            echo '            <form action="jobSeekers.php" method="POST">';
            echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';
            echo '                <input type="hidden" name="return_page" value="jobs">';
            echo '                <button type="submit" name="unsave_job" value="1" class="job-btn job-btn-danger">Unsave</button>';
            echo '            </form>';
        } else {
            echo '            <form action="jobSeekers.php" method="POST">';
            echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';
            echo '                <input type="hidden" name="return_page" value="jobs">';
            echo '                <button type="submit" name="save_job" value="1" class="job-btn job-btn-secondary">Save</button>';
            echo '            </form>';
        }

        echo '        </td>';
        echo '    </tr>';
        echo '</table>';
    }

    echo '        </td>';
    echo '    </tr>';
    echo '</table>';
}

if (($_GET["page"] ?? "") === "job") {

    if (empty($currentJob)) {
        echo '<table class="job-table">';
        echo '    <tr><td>Job</td></tr>';
        echo '    <tr><td>That job is not open.</td></tr>';
        echo '</table>';
    } else {
        $jobId = (int) ($currentJob["job_id"] ?? 0);
        $skills = $currentJob["skills"] ?? [];
        if (!is_array($skills)) {
            $skills = [];
        }

        echo '<table class="job-table">';
        echo '    <tr>';
        echo '        <td colspan="2">' . escape_value($currentJob["job_title"] ?? "") . '</td>';
        echo '        <td style="text-align:right;">';
        echo '            <form action="view.php" method="GET">';
        echo '                <input type="hidden" name="page" value="jobs">';
        echo '                <button type="submit" class="job-btn job-btn-secondary">Back to jobs</button>';
        echo '            </form>';
        echo '        </td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Company</td>';
        echo '        <td colspan="2">' . escape_value($currentJob["companyName"] ?? "") . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Category</td>';
        echo '        <td colspan="2">' . escape_value($currentJob["job_category"] ?? "Not specified") . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Employment</td>';
        echo '        <td colspan="2">' . escape_value(label_from_value($currentJob["employment_type"] ?? "")) . ' · ' . escape_value(label_from_value($currentJob["work_setup"] ?? "")) . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Location</td>';
        echo '        <td colspan="2">' . escape_value(format_location($currentJob["city"] ?? "", $currentJob["province"] ?? "")) . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Salary</td>';
        echo '        <td colspan="2">' . escape_value(format_salary($currentJob["salary_min"] ?? null, $currentJob["salary_max"] ?? null)) . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Experience</td>';
        echo '        <td colspan="2">' . escape_value(label_from_value($currentJob["experience_level"] ?? "")) . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Education</td>';
        echo '        <td colspan="2">' . escape_value(label_from_value($currentJob["education_level"] ?? "")) . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Vacancies</td>';
        echo '        <td colspan="2">' . escape_value($currentJob["vacancies"] ?? "") . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Deadline</td>';
        echo '        <td colspan="2">' . escape_value(($currentJob["application_deadline"] ?? "") !== "" && $currentJob["application_deadline"] !== null ? $currentJob["application_deadline"] : "Open") . '</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>Description</td>';
        echo '        <td colspan="2">' . nl2br(escape_value($currentJob["job_description"] ?? "")) . '</td>';
        echo '    </tr>';

        if (trim((string) ($currentJob["job_requirements"] ?? "")) !== "") {
            echo '    <tr>';
            echo '        <td>Requirements</td>';
            echo '        <td colspan="2">' . nl2br(escape_value($currentJob["job_requirements"])) . '</td>';
            echo '    </tr>';
        }

        if (trim((string) ($currentJob["job_responsibilities"] ?? "")) !== "") {
            echo '    <tr>';
            echo '        <td>Responsibilities</td>';
            echo '        <td colspan="2">' . nl2br(escape_value($currentJob["job_responsibilities"])) . '</td>';
            echo '    </tr>';
        }

        echo '    <tr>';
        echo '        <td>Skills</td>';
        echo '        <td colspan="2">';

        if (empty($skills)) {
            echo 'Not specified';
        } else {
            echo '<div class="job-skill-display">';
            foreach ($skills as $skill) {
                echo '<span>' . escape_value($skill) . '</span>';
            }
            echo '</div>';
        }

        echo '        </td>';
        echo '    </tr>';

        if (trim((string) ($currentJob["additional_instructions"] ?? "")) !== "") {
            echo '    <tr>';
            echo '        <td>Instructions</td>';
            echo '        <td colspan="2">' . nl2br(escape_value($currentJob["additional_instructions"])) . '</td>';
            echo '    </tr>';
        }

        echo '    <tr>';
        echo '        <td>Save</td>';
        echo '        <td colspan="2">';

        if ((int) ($currentJob["is_saved"] ?? 0) > 0) {
            echo '            <form action="jobSeekers.php" method="POST">';
            echo '                <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';
            echo '                <input type="hidden" name="return_page" value="job">';
            echo '                <button type="submit" name="unsave_job" value="1" class="job-btn job-btn-danger">Unsave</button>';
            echo '            </form>';
        } else {
            echo '            <form action="jobSeekers.php" method="POST">';
            echo '                <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';
            echo '                <input type="hidden" name="return_page" value="job">';
            echo '                <button type="submit" name="save_job" value="1" class="job-btn job-btn-secondary">Save Job</button>';
            echo '            </form>';
        }

        echo '        </td>';
        echo '    </tr>';
        echo '</table>';

        echo '<form action="jobSeekers.php" method="POST">';
        echo '    <input type="hidden" name="job_id" value="' . escape_value($jobId) . '">';
        echo '    <table class="job-table">';
        echo '        <tr>';
        echo '            <td colspan="2">Apply</td>';
        echo '        </tr>';

        if ((int) ($currentJob["has_applied"] ?? 0) > 0) {
            echo '        <tr>';
            echo '            <td colspan="2">You already applied to this job.</td>';
            echo '        </tr>';
        } else {
            echo '        <tr>';
            echo '            <td colspan="2">Cover letter</td>';
            echo '        </tr>';
            echo '        <tr>';
            echo '            <td colspan="2">';
            echo '                <textarea name="cover_letter" placeholder="Tell the recruiter why you are a fit."></textarea>';
            echo '            </td>';
            echo '        </tr>';
            echo '        <tr>';
            echo '            <td colspan="2">';
            echo '                <button type="submit" name="apply_job" value="1" class="job-btn job-btn-primary">Submit Application</button>';
            echo '            </td>';
            echo '        </tr>';
        }

        echo '    </table>';
        echo '</form>';
    }
}
