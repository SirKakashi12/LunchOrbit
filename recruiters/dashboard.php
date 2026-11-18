<?php

if (isset($_GET["page"]) && $_GET["page"] === "dashboard") {

    $dashboard = $_SESSION["dashboardData"] ?? [];
    $activeJobs = (int) ($dashboard["active_jobs"] ?? 0);
    $totalApplicants = (int) ($dashboard["total_applicants"] ?? 0);
    $pending = (int) ($dashboard["pending"] ?? 0);
    $accepted = (int) ($dashboard["accepted"] ?? 0);
    $recentApplicants = $dashboard["recent_applicants"] ?? [];

    $jobBreakdown = [
        "Published" => (int) ($dashboard["active_jobs"] ?? 0),
        "Draft" => (int) ($dashboard["draft_jobs"] ?? 0),
        "Paused" => (int) ($dashboard["paused_jobs"] ?? 0),
        "Closed" => (int) ($dashboard["closed_jobs"] ?? 0),
    ];

    $applicationBreakdown = [
        "Pending" => (int) ($dashboard["pending"] ?? 0),
        "Reviewing" => (int) ($dashboard["reviewing"] ?? 0),
        "Shortlisted" => (int) ($dashboard["shortlisted"] ?? 0),
        "Interview" => (int) ($dashboard["interview"] ?? 0),
        "Accepted" => (int) ($dashboard["accepted"] ?? 0),
        "Rejected" => (int) ($dashboard["rejected"] ?? 0),
        "Withdrawn" => (int) ($dashboard["withdrawn"] ?? 0),
    ];

    $username = htmlspecialchars(
        (string) ($_SESSION["user_username"] ?? "Recruiter"),
        ENT_QUOTES,
        "UTF-8"
    );

    echo '<div class="welcome-section">';
    echo '    <h2>Welcome back, ' . $username . '!</h2>';
    echo '    <p>Here is an overview of your jobs and applicants.</p>';
    echo '</div>';

    echo '<div class="dashboard-cards">';

    echo '    <div class="stat-card">';
    echo '        <p>Active Job Posts</p>';
    echo '        <h2>' . $activeJobs . '</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Total Applicants</p>';
    echo '        <h2>' . $totalApplicants . '</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Pending Applications</p>';
    echo '        <h2>' . $pending . '</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Accepted Applicants</p>';
    echo '        <h2>' . $accepted . '</h2>';
    echo '    </div>';

    echo '</div>';

    echo '<div class="analysis-grid">';
    echo dashboard_analysis_panel("Jobs by status", $jobBreakdown);
    echo dashboard_analysis_panel("Applicants by status", $applicationBreakdown);
    echo '</div>';

    echo '<div class="content-panel">';
    echo '    <h3>Recent Applicants</h3>';

    if ($recentApplicants === []) {
        echo '    <p>Applications for your job posts will appear here.</p>';
    } else {
        echo '<table class="applicant-table">';
        echo '    <thead>';
        echo '        <tr>';
        echo '            <th>Applicant</th>';
        echo '            <th>Job</th>';
        echo '            <th>Status</th>';
        echo '            <th>Applied</th>';
        echo '        </tr>';
        echo '    </thead>';
        echo '    <tbody>';

        foreach ($recentApplicants as $applicant) {
            echo '<tr>';
            echo '    <td>' . dashboard_escape($applicant["fullname"] ?? "") . '</td>';
            echo '    <td>' . dashboard_escape($applicant["job_title"] ?? "") . '</td>';
            echo '    <td>' . dashboard_escape(dashboard_label($applicant["status"] ?? "")) . '</td>';
            echo '    <td>' . dashboard_escape($applicant["applied_at"] ?? "") . '</td>';
            echo '</tr>';
        }

        echo '    </tbody>';
        echo '</table>';
    }

    echo '</div>';
}

function dashboard_escape($value): string
{
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}

function dashboard_label($value): string
{
    $text = trim((string) ($value ?? ""));
    if ($text === "") {
        return "Not specified";
    }

    return ucwords(str_replace("_", " ", $text));
}

function dashboard_analysis_panel(string $title, array $rows): string
{
    $total = array_sum($rows);
    $html = '<div class="content-panel">';
    $html .= '<h3>' . dashboard_escape($title) . '</h3>';

    if ($total < 1) {
        $html .= '<p>No records yet.</p>';
        $html .= '</div>';
        return $html;
    }

    foreach ($rows as $label => $count) {
        $width = (int) round(($count / $total) * 100);
        $html .= '<div class="analysis-row">';
        $html .= '<span>' . dashboard_escape($label) . '</span>';
        $html .= '<div class="analysis-track"><div class="analysis-fill" style="width:' . $width . '%"></div></div>';
        $html .= '<strong>' . (int) $count . '</strong>';
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}
