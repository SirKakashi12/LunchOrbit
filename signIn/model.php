<?php
declare(strict_types=1);

function get_user(object $pdo, string $identifier): array|false
{
    $query = "
        SELECT user_id, username, fullname, password, email, role
        FROM users
        WHERE username = :username
        OR email = :email
        LIMIT 1;
    ";

    $stmt = $pdo->prepare($query);

    $stmt->bindValue(":username", $identifier);
    $stmt->bindValue(":email", $identifier);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}