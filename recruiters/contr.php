<?php
declare(strict_types=1);

function is_field_empty_in_personal(string $usernameoremail , string $pwd ):bool
{
	if (empty($usernameoremail) || empty($pwd)) {
		return true;
	}
	return false;
}

function is_field_empty_in_company(string $usernameoremail , string $pwd ):bool
{
	if (empty($usernameoremail) || empty($pwd)) {
		return true;
	}
	return false;
}