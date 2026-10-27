<?php
declare(strict_types=1);

function is_field_empty(string $username , string $fullname , string $pwd , string $repwd , string $email , string $cellno, string $role):bool
{
	if (empty($username) || empty($fullname) || empty($pwd) || empty($repwd) || empty($email)|| empty($cellno) || empty($role)) {
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

function is_username_taken(object $pdo, string $username):bool
{
	if (get_username($pdo, $username)) {
		return true;
	}
	return false;
}

function is_email_regesterd(object $pdo, string $email):bool
{
	if (get_email($pdo, $email)) {
		return true;
	}
	return false;
}

function is_password_and_repassword_not_the_same(string $pwd,  string $repwd):bool
{
	if($pwd !== $repwd){
		return true;
	}
	return false;
}




function create_user(object $pdo, string $username, string $fullname, string $pwd, string $email, string $cellno, string $role):void 
{

	insert_user($pdo, $username, $fullname, $pwd, $email, $cellno, $role);

}