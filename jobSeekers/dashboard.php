<?php

if (isset($_GET["page"]) && $_GET["page"] === "dashboard") {

    $dashboard = $_SESSION["dashboardData"] ?? [];
    $openJobs = (int) ($dashboard["open_jobs"] ?? 0);
    $myApplications = (int) ($dashboard["my_applications"] ?? 0);
    $savedJobs = (int) ($dashboard["saved_jobs"] ?? 0);
    $recentApplications = $dashboard["recent_applications"] ?? [];

    echo '<div class="welcome-section">';
    echo '    <h2>Welcome back, ' . escape_value($_SESSION["user_username"] ?? "Job Seeker") . '!</h2>';
    echo '    <p>Here is an overview of open roles, your applications, and saved jobs.</p>';
    echo '</div>';

    echo '<div class="dashboard-cards">';

    echo '    <div class="stat-card">';
    echo '        <p>Open Jobs</p>';
    echo '        <h2>' . escape_value($openJobs) . '</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>My Applications</p>';
    echo '        <h2>' . escape_value($myApplications) . '</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Saved Jobs</p>';
    echo '        <h2>' . escape_value($savedJobs) . '</h2>';
    echo '    </div>';

    echo '</div>';

    echo '<div class="content-panel">';
    echo '    <h3>Recent Applications</h3>';

    if (empty($recentApplications)) {
        echo '    <p>Your applications will appear here after you apply to a job.</p>';
    } else {
        echo '<table class="applicant-table">';
        echo '    <thead>';
        echo '        <tr>';
        echo '            <th>Job</th>';
        echo '            <th>Company</th>';
        echo '            <th>Status</th>';
        echo '            <th>Applied</th>';
        echo '        </tr>';
        echo '    </thead>';
        echo '    <tbody>';

        foreach ($recentApplications as $application) {
            echo '<tr>';
            echo '    <td>' . escape_value($application["job_title"] ?? "") . '</td>';
            echo '    <td>' . escape_value($application["companyName"] ?? "") . '</td>';
            echo '    <td>' . escape_value(label_from_value($application["status"] ?? "")) . '</td>';
            echo '    <td>' . escape_value($application["applied_at"] ?? "") . '</td>';
            echo '</tr>';
        }

        echo '    </tbody>';
        echo '</table>';
    }

    echo '</div>';
}
