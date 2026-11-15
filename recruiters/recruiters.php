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

    $username = $_POST["username"] ?? "";
    $fullname = $_POST["fullname"] ?? "";
    $email = $_POST["email"] ?? "";
    $cellno = $_POST["cellno"] ?? "";

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];

        $errors = [];

        if (is_field_empty_in_personal($username, $fullname, $email, $cellno)) {
            $errors["empty_input"] = "Fill in all the fields!";
        }

        if (is_username_taken_for_other_user($pdo, $user_id , $username)) {
            $errors["username_taken"] = "Username already taken!";
        }

        if (is_email_invalid($email)) {
            $errors["invalid_email"] = "Invalid email address!";
        }

        if (is_email_registered_for_other_user($pdo, $user_id , $email)) {
            $errors["email_used"] = "Email already taken!";
        }

        if (!empty($errors)) {
            $_SESSION["errors_profile"] = $errors;

            header("Location: view.php?page=company");
            exit();
        }

        update_user($pdo, $user_id, $username, $fullname, $email, $cellno
        );

        $userInfo = get_recruiter_user_info($pdo, $user_id);
        $companyInfo = get_recruiter_company_info($pdo, $user_id);

        $_SESSION["profileInfo"] = [
            "username" => $userInfo["username"] ?? "",
            "lastupdate" => $userInfo["created_at"] ?? "",
            "fullname" => $userInfo["fullname"] ?? "",
            "email" => $userInfo["email"] ?? "",
            "cellno" => $userInfo["cellno"] ?? "",

            "companyName" => $companyInfo["companyName"] ?? "",
            "companyCreatedAt" => $companyInfo["created_at"] ?? "",
            "companyWebsite" => $companyInfo["companyWebsite"] ?? "",
            "companyNumber" => $companyInfo["companyNumber"] ?? "",
            "positionInCompany" => $companyInfo["PositioninCompany"] ?? "",

            "companyProfileIncomplete" => ($companyInfo === false)
        ];


        $_SESSION["user_username"] = $username;

        header("Location: view.php?page=company&profile=success");
        exit();


    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }

}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["company_update"])) {

    $companyName = $_POST["companyName"] ?? "";
    $companyWebsite = $_POST["companyWebsite"] ?? "";
    $companyNumber = $_POST["companyNumber"] ?? "";
    $PositioninCompany = $_POST["PositioninCompany"] ?? "";

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];

        $errors = [];

        if (is_field_empty_in_company( $companyName,  $companyWebsite,  $companyNumber,  $PositioninCompany)) {
            $errors["empty_input"] = "Fill in all the fields!";
        }
        
        $websiteValidation = validate_company_website($companyWebsite);

        if ($websiteValidation["invalid"]) {
            $errors["invalid_URL"] = "Invalid URL address!";
        }


        if (!empty($errors)) {
            $_SESSION["errors_profile"] = $errors;

            header("Location: view.php?page=company");
            exit();
        }

        upsert_company_database($pdo, $user_id, $companyName, $companyWebsite, $companyNumber, $PositioninCompany);

        $userInfo = get_recruiter_user_info($pdo, $user_id);
        $companyInfo = get_recruiter_company_info($pdo, $user_id);

        $_SESSION["profileInfo"] = [
            "username" => $userInfo["username"] ?? "",
            "lastupdate" => $userInfo["created_at"] ?? "",
            "fullname" => $userInfo["fullname"] ?? "",
            "email" => $userInfo["email"] ?? "",
            "cellno" => $userInfo["cellno"] ?? "",

            "companyName" => $companyInfo["companyName"] ?? "",
            "companyCreatedAt" => $companyInfo["created_at"] ?? "",
            "companyWebsite" => $companyInfo["companyWebsite"] ?? "",
            "companyNumber" => $companyInfo["companyNumber"] ?? "",
            "positionInCompany" => $companyInfo["PositioninCompany"] ?? "",

            "companyProfileIncomplete" => ($companyInfo === false)
        ];

        header("Location: view.php?page=company&profile=success");
        exit();

    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["navigate"])) {

    $page = $_POST["navigate"];

    $allowedPages = ["dashboard", "company", "jobs", "applicants"];

    if (!in_array($page, $allowedPages, true)) {
        header("Location: view.php");
        exit();
    }

    if ($page === "company" || $page === "dashboard") {

        require_once '../private/database.php';
        require_once 'model.php';

        if (!isset($_SESSION["user_id"])) {
            header("Location: ../signIn/view.php");
            exit();
        }

        $user_id = (int) $_SESSION["user_id"];

        $userInfo = get_recruiter_user_info($pdo, $user_id);
        $companyInfo = get_recruiter_company_info($pdo, $user_id);

        $profileInfo = [
            "username" => $userInfo["username"] ?? "",
            "lastupdate" => $userInfo["created_at"] ?? "",
            "fullname" => $userInfo["fullname"] ?? "",
            "email" => $userInfo["email"] ?? "",
            "cellno" => $userInfo["cellno"] ?? "",

            "companyName" => $companyInfo["companyName"] ?? "",
            "companyCreatedAt" => $companyInfo["created_at"] ?? "",
            "companyWebsite" => $companyInfo["companyWebsite"] ?? "",
            "companyNumber" => $companyInfo["companyNumber"] ?? "",
            "positionInCompany" => $companyInfo["PositioninCompany"] ?? "",

            "companyProfileIncomplete" => ($companyInfo === false)
        ];

        $_SESSION["profileInfo"] = $profileInfo;
    }

    header("Location: view.php?page=" . urlencode($page));
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