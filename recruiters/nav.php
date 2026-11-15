<?php

echo '<aside class="sidebar">';

echo '    <div class="brand">';
echo '        <h2>Lunch<span>Orbit</span></h2>';
echo '        <p>Recruiter Portal</p>';
echo '    </div>';

echo '    <nav class="sidebar-nav">';
echo '        <p class="nav-heading">WORKSPACE</p>';

// Dashboard
echo '        <form action="recruiters.php" method="POST">';
echo '            <button type="submit" name="navigate" value="dashboard" class="nav-link">';
echo '                <span>▦</span> Dashboard';
echo '            </button>';
echo '        </form>';

// Company Profile
echo '        <form action="recruiters.php" method="POST">';
echo '            <button type="submit" name="navigate" value="company" class="nav-link">';
echo '                <span>▤</span> Company Profile';
echo '            </button>';
echo '        </form>';

// Job Posts
echo '        <form action="recruiters.php" method="POST">';
echo '            <button type="submit" name="navigate" value="jobs" class="nav-link">';
echo '                <span>▣</span> Job Posts';
echo '            </button>';
echo '        </form>';

// Applicants
echo '        <form action="recruiters.php" method="POST">';
echo '            <button type="submit" name="navigate" value="applicants" class="nav-link">';
echo '                <span>♙</span> Applicants';
echo '            </button>';
echo '        </form>';

echo '    </nav>';

echo '    <div class="sidebar-bottom">';
echo '        <form action="recruiters.php" method="POST">';
echo '            <button type="submit" name="logout" value="1" class="nav-link logout">';
echo '                <span>↪</span> Logout';
echo '            </button>';
echo '        </form>';
echo '    </div>';

echo '</aside>';