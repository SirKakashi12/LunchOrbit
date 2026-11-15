<?php
declare(strict_types=1);



function is_field_empty_in_personal(  string $username,  string $fullname,  string $email,  string $cellno
): bool {
    return empty($username) || empty($fullname) || empty($email) || empty($cellno);
}

function is_email_invalid(string $email):bool
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}





function update_user( object $pdo, int $user_id, string $username, string $fullname, string $email, string $cellno): bool{
	if (update_user_database($pdo,$user_id,$username,$fullname,$email,$cellno)){
		return true;
	}
	return false;
}

function is_username_taken_for_other_user( object $pdo, int $user_id, string $username,
): bool
{
    return get_username_except_current_user($pdo,  $user_id, $username,) !== false;
}


function is_email_registered_for_other_user( object $pdo, int $user_id, string $email,
): bool
{
    return get_email_except_current_user($pdo, $user_id, $email) !== false;
}




function is_field_empty_in_company( string $companyName, string $companyWebsite, string $companyNumber, string $positioninCompany
): bool {
    return empty($companyName) || empty($companyWebsite) || empty($companyNumber) || empty($positioninCompany);
}



function is_url_invalid(string $companyWebsite):bool
{
	if (!filter_var($companyWebsite, FILTER_VALIDATE_URL)) {
		return true;
	}
	return false;
}

function validate_company_website(string $companyWebsite): array
{
    $companyWebsite = trim($companyWebsite);

    // Allow an empty website because it is optional
    if (empty($companyWebsite)) {
        return [
            "value" => "",
            "invalid" => false
        ];
    }

    if (!preg_match("~^https?://~i", $companyWebsite)) {
        $companyWebsite = "https://" . $companyWebsite;
    }

    if (!filter_var($companyWebsite, FILTER_VALIDATE_URL)) {
        return [
            "value" => $companyWebsite,
            "invalid" => true
        ];
    }

    return [
        "value" => $companyWebsite,
        "invalid" => false
    ];
}

