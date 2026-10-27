<?php


declare(strict_types=1);


function get_username(object $pdo, string $username):array|false
{
	$query = "SELECT username FROM users WHERE username =:username;";
	$stmt = $pdo->prepare($query);
	$stmt->bindParam(":username", $username);
	$stmt->execute();


	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	return  $result;
}

function get_email(object $pdo, string $email):array|false
{
	$query = "SELECT email FROM users WHERE email =:email;";
	$stmt = $pdo->prepare($query);
	$stmt->bindParam(":email", $email);
	$stmt->execute();


	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	return  $result;
}


function insert_user(object $pdo, string $username, string $fullname, string $pwd, string $email, string $cellno, string $role):void
{
		$query = "
		INSERT INTO users (username, fullname, password, email, cellno, role) 
		VALUES (:username, :fullname, :pwd, :email, :cellno, :role);";
	$stmt = $pdo->prepare($query);
	$options = ['cost' => 12];

	$token = require '../private/inv.php';
	$hashkey = $token['hashkey'];

	$hashed_pwd = password_hash(
	    $pwd . $hashkey,
	    PASSWORD_BCRYPT,
	    $options
	);
	$stmt->bindParam(":username", $username);
	$stmt->bindParam(":fullname", $fullname);
	$stmt->bindParam(":pwd", $hashed_pwd);
	$stmt->bindParam(":email", $email);
	$stmt->bindParam(":cellno", $cellno);
	$stmt->bindParam(":role", $role);
	$stmt->execute();


}