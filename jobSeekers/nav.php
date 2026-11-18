
<?php

$currentPage = (string) ($_GET["page"] ?? "dashboard");
if ($currentPage === "job") {
    $currentPage = "jobs";
}

function nav_link_class(string $currentPage, string $page): string
{
    return $currentPage === $page ? "nav-link active" : "nav-link";
}

echo '<aside class="sidebar">';

echo '    <div class="brand">';
echo '        <h2>Lunch<span>Orbit</span></h2>';
echo '        <p>Job Seeker Portal</p>';
echo '    </div>';

echo '    <nav class="sidebar-nav">';
echo '        <p class="nav-heading">WORKSPACE</p>';

// Dashboard
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="navigate" value="dashboard" class="' . nav_link_class($currentPage, "dashboard") . '">';
echo '                <span>▦</span> Dashboard';
echo '            </button>';
echo '        </form>';

// Find Jobs
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="navigate" value="jobs" class="' . nav_link_class($currentPage, "jobs") . '">';
echo '                <span>⌕</span> Find Jobs';
echo '            </button>';
echo '        </form>';

// Applications
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="navigate" value="applications" class="' . nav_link_class($currentPage, "applications") . '">';
echo '                <span>▤</span> My Applications';
echo '            </button>';
echo '        </form>';

// Saved Jobs
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="navigate" value="saved" class="' . nav_link_class($currentPage, "saved") . '">';
echo '                <span>▣</span> Saved Jobs';
echo '            </button>';
echo '        </form>';

// Profile
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="navigate" value="profile" class="' . nav_link_class($currentPage, "profile") . '">';
echo '                <span>♙</span> My Profile';
echo '            </button>';
echo '        </form>';

echo '    </nav>';

echo '    <div class="sidebar-bottom">';
echo '        <form action="jobseekers.php" method="POST">';
echo '            <button type="submit" name="logout" value="1" class="nav-link logout">';
echo '                <span>↪</span> Logout';
echo '            </button>';
echo '        </form>';
echo '    </div>';

echo '</aside>';

?>
