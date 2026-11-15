<?php 

if(isset($_GET["page"]) &&  $_GET["page"] === "dashboard") {

    echo '<div class="welcome-section">';
    echo '    <h2>Welcome back,  <?= display_username(); ?> !</h2>';
    echo '    <p>Here\'s an overview of your recruitment workspace.</p>';
    echo '</div>';

    echo '<div class="dashboard-cards">';

    echo '    <div class="stat-card">';
    echo '        <p>Active Job Posts</p>';
    echo '        <h2>0</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Total Applicants</p>';
    echo '        <h2>0</h2>';
    echo '    </div>';

    echo '    <div class="stat-card">';
    echo '        <p>Pending Applications</p>';
    echo '        <h2>0</h2>';
    echo '    </div>';

    echo '</div>';

    echo '<div class="content-panel">';
    echo '    <h3>Recent Activity</h3>';
    echo '    <p>Your recruitment activities will appear here.</p>';
    echo '</div>';
}
