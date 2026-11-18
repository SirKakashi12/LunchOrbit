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

function selected_option($current, $value): string {
    return ($current === $value) ? ' selected' : '';
}

if(isset($_GET["page"]) &&  $_GET["page"] === "profile") 
{

    // personal form    
    echo '<form action="jobseekers.php" method="POST">';

    echo '    <table class="profile-table">';
    echo '        <tr>';
    echo '            <td>Personal Info</td>';
    echo '            <td colspan="2" id="right">Job Seeker</td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';
    echo '                Username:';
    echo '                <input type="text" name="username" value="'.escape_value($_SESSION["profileInfo"]["username"] ?? "").'"  readonly data-editable>';
    echo '            </td>';
    echo '            <td></td>';
    echo '            <td id="right">  '.escape_value($_SESSION["profileInfo"]["lastupdate"] ?? "") .' </td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';
    echo '                Fullname:';
    echo '                <input type="text" name="fullname" value="'.escape_value($_SESSION["profileInfo"]["fullname"] ?? "").'"  readonly data-editable>';
    echo '            </td>';
    echo '            <td></td>';
    echo '            <td></td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td class="profile-section-heading">';
    echo '                <strong>CONTACT</strong>';
    echo '            </td>';
    echo '            <td></td>';
    echo '            <td></td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';
    echo '                Email:';
    echo '                <input type="email" name="email"  value="'.escape_value($_SESSION["profileInfo"]["email"] ?? "").'"  readonly data-editable>';
    echo '            </td>';
    echo '            <td></td>';
    echo '            <td></td>';
    echo '        </tr>';

    echo '        <tr>';
    echo '            <td>';
    echo '                Cell No:';
    echo '                <input type="text" name="cellno" value="'.escape_value($_SESSION["profileInfo"]["cellno"] ?? "").'" readonly data-editable>';
    echo '            </td>';

    echo '            <td style="text-align: right; padding-right:5px;">';
    echo '                <button type="button" class="edit-btn">Edit</button>';
    echo '            </td>';

    echo '            <td style="text-align: left; padding-left:5px;">';
    echo '                <button type="submit" name="personal_update" value="1"';
    echo '                    class="update-btn" disabled>';
    echo '                    Update';
    echo '                </button>';
    echo '            </td>';
    echo '        </tr>';
    echo '    </table>';

    echo '</form>';

    // my resume
    
    echo '<form action="jobSeekers.php" method="POST">';

    echo '<table class="profile-table">';

    echo '<tr>';
    echo '    <td colspan="3">Job Seeker Profile</td>';
    echo '</tr>';

    /* 
       EDUCATION
     */

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>EDUCATION</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Education Level:';
    echo '        <select name="education_level" disabled data-editable>';

    $educationLevels = [
        "junior_high_school" => "Junior High School",
        "senior_high_school" => "Senior High School",
        "vocational_certificate" => "Vocational Certificate",
        "associate_degree" => "Associate Degree",
        "bachelor_degree" => "Bachelor Degree",
        "masters_degree" => "Master\'s Degree",
        "doctoral_degree" => "Doctoral Degree",
        "other" => "Other"
    ];

    echo '            <option value="">Select Education Level</option>';

    foreach ($educationLevels as $value => $label) {
        echo '            <option value="' . $value . '"'
            . selected_option(
                $_SESSION["profileInfo"]["education_level"] ?? "",
                $value
            )
            . '>' . $label . '</option>';
    }

    echo '        </select>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Education Status:';
    echo '        <select name="education_status" disabled data-editable>';

    $educationStatuses = [
        "currently_studying" => "Currently Studying",
        "graduated" => "Graduated",
        "not_completed" => "Not Completed"
    ];

    echo '            <option value="">Select Education Status</option>';

    foreach ($educationStatuses as $value => $label) {
        echo '            <option value="' . $value . '"'
            . selected_option(
                $_SESSION["profileInfo"]["education_status"] ?? "",
                $value
            )
            . '>' . $label . '</option>';
    }

    echo '        </select>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        School Name:';
    echo '        <input type="text" name="school_name" value="'
        . escape_value($_SESSION["profileInfo"]["school_name"] ?? "")
        . '" readonly data-editable>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Degree / Course:';
    echo '        <input type="text" name="degree_course" value="'
        . escape_value($_SESSION["profileInfo"]["degree_course"] ?? "")
        . '" readonly data-editable>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Graduation Year:';
    echo '        <input type="number" name="graduation_year"'
        . ' min="1900" max="2155"'
        . ' value="'
        . escape_value($_SESSION["profileInfo"]["graduation_year"] ?? "")
        . '" readonly data-editable>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    /* 
       EXPERIENCE
     */

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>EXPERIENCE</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Work Experience Level:';
    echo '        <select name="work_experience_level" disabled data-editable>';

    $experienceLevels = [
        "none" => "No Experience",
        "internship_or_ojt_only" => "Internship / OJT Only",
        "under_2_years" => "Under 2 Years",
        "2_to_5_years" => "2 to 5 Years",
        "over_5_years" => "Over 5 Years"
    ];

    foreach ($experienceLevels as $value => $label) {
        echo '            <option value="' . $value . '"'
            . selected_option(
                $_SESSION["profileInfo"]["work_experience_level"] ?? "none",
                $value
            )
            . '>' . $label . '</option>';
    }

    echo '        </select>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    /* 
       LOCATION
     */

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>LOCATION</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        City:';
    echo '        <input type="text" name="city" value="'
        . escape_value($_SESSION["profileInfo"]["city"] ?? "")
        . '" readonly data-editable>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Province:';
    echo '        <input type="text" name="province" value="'
        . escape_value($_SESSION["profileInfo"]["province"] ?? "")
        . '" readonly data-editable>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    /* 
       PROFILE SUMMARY
     */

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>PROFILE SUMMARY</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Profile Summary:';
    echo '        <textarea name="profile_summary" readonly data-editable>'
        . escape_value($_SESSION["profileInfo"]["profile_summary"] ?? "")
        . '</textarea>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    /* 
       JOB PREFERENCES
     */

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>JOB PREFERENCES</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Preferred Work Setup:';
    echo '        <select name="preferred_work_setup" disabled data-editable>';

    $workSetups = [
        "onsite" => "On-site",
        "hybrid" => "Hybrid",
        "remote" => "Remote",
        "any" => "Any"
    ];

    foreach ($workSetups as $value => $label) {
        echo '            <option value="' . $value . '"'
            . selected_option(
                $_SESSION["profileInfo"]["preferred_work_setup"] ?? "any",
                $value
            )
            . '>' . $label . '</option>';
    }

    echo '        </select>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td>';
    echo '        Preferred Employment Type:';
    echo '        <select name="preferred_employment_type" disabled data-editable>';

    $employmentTypes = [
        "full_time" => "Full-Time",
        "part_time" => "Part-Time",
        "internship" => "Internship",
        "contract" => "Contract",
        "freelance" => "Freelance",
        "any" => "Any"
    ];

    foreach ($employmentTypes as $value => $label) {
        echo '            <option value="' . $value . '"'
            . selected_option(
                $_SESSION["profileInfo"]["preferred_employment_type"] ?? "any",
                $value
            )
            . '>' . $label . '</option>';
    }

    echo '        </select>';
    echo '    </td>';

    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td class="profile-section-heading">';
    echo '        <strong>SKILLS</strong>';
    echo '    </td>';
    echo '    <td></td>';
    echo '    <td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td colspan="3">';
    echo '        <div class="skills-container" id="skills-container">';

    $profileSkills = $_SESSION["profileInfo"]["skills"] ?? [];
    if (!is_array($profileSkills)) {
        $profileSkills = [];
    }

    foreach ($profileSkills as $skill) {
        echo '            <div class="skill-item">';
        echo '                <input type="text" name="skills[]" value="'
            . escape_value($skill)
            . '" readonly data-editable>';
        echo '                <button type="button" class="skill-remove" onclick="removeSkill(this)">&times;</button>';
        echo '            </div>';
    }

    echo '        </div>';
    echo '        <div class="skill-add-area" style="margin-top:12px;">';
    echo '            <input type="text" id="new-skill" placeholder="Enter a skill">';
    echo '            <button type="button" class="job-btn job-btn-secondary" onclick="addSkill()">+ Add Skill</button>';
    echo '        </div>';
    echo '    </td>';
    echo '</tr>';

    echo '<tr>';
    echo '    <td></td>';
    echo '    <td style="text-align: right; padding-right:5px;">';
    echo '        <button type="button" class="edit-btn">Edit</button>';
    echo '    </td>';

    echo '    <td style="text-align: left; padding-left:5px;">';
    echo '        <button type="submit" name="jobseeker_update" value="1"';
    echo '            class="update-btn" disabled>';
    echo '            Update';
    echo '        </button>';
    echo '    </td>';
    echo '</tr>';

    echo '</table>';

    echo '</form>';



}
