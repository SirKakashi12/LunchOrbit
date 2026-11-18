<?php

$savedJobs = $_SESSION["savedJobData"] ?? [];

if (($_GET["page"] ?? "") === "saved") {

    echo '<table class="job-table">';
    echo '    <tr>';
    echo '        <td colspan="3">Saved Jobs (' . escape_value(count($savedJobs)) . ')</td>';
    echo '    </tr>';
    echo '    <tr>';
    echo '        <td colspan="3">';

    if (empty($savedJobs)) {
        echo '<p>Jobs you save will appear here.</p>';
    }

    foreach ($savedJobs as $job) {
        $listingId = $job["job_id"];
        $isOpen = ($job["status"] ?? "") === "published";
        $deadline = $job["application_deadline"] ?? null;
        $deadlinePassed = $deadline !== null && $deadline !== "" && $deadline < date("Y-m-d");
        $canOpen = $isOpen && !$deadlinePassed;
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
        echo '        <td class="job-status' . ($canOpen ? "" : " draft") . '">';
        echo              escape_value($canOpen ? "Open" : label_from_value($job["status"] ?? "closed"));
        echo '        </td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td>' . escape_value($meta);

        if ((int) ($job["has_applied"] ?? 0) > 0) {
            echo '<br>Applied';
        }

        echo '        </td>';
        echo '        <td>';

        if ($canOpen) {
            echo '            <form action="view.php" method="GET">';
            echo '                <input type="hidden" name="page" value="job">';
            echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';
            echo '                <button type="submit" class="job-btn job-btn-secondary">View</button>';
            echo '            </form>';
        } else {
            echo 'Closed';
        }

        echo '        </td>';
        echo '        <td>';
        echo '            <form action="jobSeekers.php" method="POST">';
        echo '                <input type="hidden" name="job_id" value="' . escape_value($listingId) . '">';
        echo '                <input type="hidden" name="return_page" value="saved">';
        echo '                <button type="submit" name="unsave_job" value="1" class="job-btn job-btn-danger">Unsave</button>';
        echo '            </form>';
        echo '        </td>';
        echo '    </tr>';
        echo '</table>';
    }

    echo '        </td>';
    echo '    </tr>';
    echo '</table>';
}
