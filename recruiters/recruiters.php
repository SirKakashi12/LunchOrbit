<?php

require_once '../private/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["logout"])) {

    // Remove all session variables
    $_SESSION = [];

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy server-side session data
    session_destroy();

    header("Location: ../signIn/view.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["personal_update"])) {
        $username = $_POST["username"];
        $fullname = $_POST["fullname"];
        $email = $_POST["email"];
        $cellno = $_POST["cellno"];
    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';
        
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
        
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["company_update"])) {
    $companyName = $_POST["companyName"];
    $companyWebsite = $_POST["companyWebsite"];
    $companyNumber = $_POST["companyNumber"];
    $PositioninCompany = $_POST["PositioninCompany"];
    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';
        
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
        
    }
}

header("Location: view.php");
exit();