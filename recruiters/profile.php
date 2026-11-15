<?php
function escape_value($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

if(isset($_GET["page"]) &&  $_GET["page"] === "company") 
{

	// personal form	
	echo '<form action="recruiters.php" method="POST">';

	echo '    <table class="profile-table">';
	echo '        <tr>';
	echo '            <td>Personal Info</td>';
	echo '            <td colspan="2" id="right">Recruiter</td>';
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

	// company form
	echo '<form action="recruiters.php" method="POST">';

	echo '    <table class="profile-table">';
	echo '        <tr>';
	echo '            <td colspan="3">Company Info</td>';
	echo '        </tr>';

	echo '        <tr>';
	echo '            <td>';
	echo '                Company Name:';
	echo '                <input type="text" name="companyName" value="'.escape_value($_SESSION["profileInfo"]["companyName"] ?? "").'"  readonly data-editable>';
	echo '            </td>';
	echo '            <td></td>';
	echo '            <td id="right"> '.escape_value($_SESSION["profileInfo"]["companyCreatedAt"] ?? "") .'</td>';
	echo '        </tr>';

	echo '        <tr>';
	echo '            <td>';
	echo '                Company Website:';
	echo '                <input type="text" name="companyWebsite" value="'.escape_value($_SESSION["profileInfo"]["companyWebsite"] ?? "").'" readonly data-editable>';
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
	echo '                Company Number:';
	echo '                <input type="text" name="companyNumber" value="'.escape_value($_SESSION["profileInfo"]["companyNumber"] ?? "").'" readonly data-editable>';
	echo '            </td>';
	echo '            <td></td>';
	echo '            <td></td>';
	echo '        </tr>';

	echo '        <tr>';
	echo '            <td>';
	echo '                Position in Company:';
	echo '                <input type="text" name="PositioninCompany" value="'.escape_value($_SESSION["profileInfo"]["positionInCompany"] ?? "").'" readonly data-editable>';
	echo '            </td>';

	echo '            <td style="text-align: right; padding-right:5px;">';
	echo '                <button type="button" class="edit-btn">Edit</button>';
	echo '            </td>';

	echo '            <td style="text-align: left; padding-left:5px;">';
	echo '                <button type="submit" name="company_update" value="1"';
	echo '                    class="update-btn" disabled>';
	echo '                    Update';
	echo '                </button>';
	echo '            </td>';
	echo '        </tr>';
	echo '    </table>';

	echo '</form>';



}
