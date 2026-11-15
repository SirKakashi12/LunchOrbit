<?php

declare(strict_types=1);


function get_recruiter_user_info(PDO $pdo, int $user_id): array|false
{
    $query = "
        SELECT
            username,
            fullname,
            email,
            cellno,
            created_at
        FROM users
        WHERE user_id = :user_id
        AND role = 'recruiter';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function get_recruiter_company_info(PDO $pdo, int $user_id): array|false
{
    $query = "
        SELECT
            companyName,
            companyWebsite,
            companyNumber,
            PositioninCompany,
            created_at
        FROM recruiters
        WHERE user_id = :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}




function update_user_database ( object $pdo, int $user_id, string $username, string $fullname, string $email, string $cellno):array|false
{
    $query = "
        UPDATE users
        SET
            username = :username,
            fullname = :fullname,
            email = :email,
            cellno = :cellno
        WHERE user_id = :user_id
        AND role = 'recruiter';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":fullname", $fullname);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":cellno", $cellno);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function upsert_company_database(object $pdo, int $user_id, string $companyName, string $companyWebsite, string $companyNumber, string $PositioninCompany):array|false
{
    $query = "
        INSERT INTO recruiters (
            user_id,
            companyName,
            companyWebsite,
            companyNumber,
            PositioninCompany
        )
        VALUES (
            :user_id,
            :companyName,
            :companyWebsite,
            :companyNumber,
            :PositioninCompany
        )
        ON DUPLICATE KEY UPDATE
            companyName = VALUES(companyName),
            companyWebsite = VALUES(companyWebsite),
            companyNumber = VALUES(companyNumber),
            PositioninCompany = VALUES(PositioninCompany);
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":companyName", $companyName);
    $stmt->bindParam(":companyWebsite", $companyWebsite);
    $stmt->bindParam(":companyNumber", $companyNumber);
    $stmt->bindParam(":PositioninCompany", $PositioninCompany);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}



function get_username_except_current_user(object $pdo,int $user_id ,string $username
): array|false
{
    $query = "
        SELECT username
        FROM users
        WHERE username = :username
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function get_email_except_current_user(object $pdo,int $user_id ,string $email
): array|false
{
    $query = "
        SELECT email
        FROM users
        WHERE email = :email
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

