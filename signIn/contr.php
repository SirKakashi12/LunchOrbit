<?php
declare(strict_types=1);

function is_field_empty(string $usernameoremail , string $pwd ):bool
{
	if (empty($usernameoremail) || empty($pwd)) {
		return true;
	}
	return false;
}


function is_email_invalid(string $email):bool
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}

function is_username_wrong( bool|array $result ):bool
{
	if(!$result){
		return true;
	} else {
		return false;
	}

}

function is_email_wrong( bool|array $result ):bool
{
	if(!$result){
		return true;
	} else {
		return false;
	}

}

function is_password_wrong(string $pwd, string $hashed_pwd): bool
{
    $token = require '../private/inv.php';
    $hashkey = $token['hashkey'];

    return !password_verify(
        $pwd . $hashkey,
        $hashed_pwd
    );
}