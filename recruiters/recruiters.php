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

        try {
            require_once '../private/database.php';
            require_once 'model.php';
            require_once 'contr.php';

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
        } catch (Exception $e) {
            die("Query Faild: " . $e->getMessage());
            
        }
        
    }

    if ($page === "jobs" || $page === "dashboard") { 

        try {
            require_once '../private/database.php';
            require_once 'model.php';
            require_once 'contr.php';

            if (!isset($_SESSION["user_id"])) {
                header("Location: ../signIn/view.php");
                exit();
            }

            $user_id = (int) $_SESSION["user_id"];

            $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);

        } catch (Exception $e) {
            die("Query Faild: " . $e->getMessage());
            
        }
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



// job posting

$isJobGet = $_SERVER["REQUEST_METHOD"] === "GET"
    && (($_GET["page"] ?? "") === "jobpost");

$isJobSave = $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["job_status"]);

$isJobClose = $_SERVER["REQUEST_METHOD"] === "POST"
    && (($_POST["job_action"] ?? "") === "close");

$isJobDelete = $_SERVER["REQUEST_METHOD"] === "POST"
    && (($_POST["job_action"] ?? "") === "delete");

if ($isJobGet || $isJobSave || $isJobClose || $isJobDelete) {

    if (!isset($_SESSION["user_id"])) {
        header("Location: ../signIn/view.php");
        exit();
    }

    try {
        require_once "../private/database.php";
        require_once "model.php";
        require_once "contr.php";

        $user_id = (int) $_SESSION["user_id"];

        if ($isJobSave || ($isJobGet && (($_GET["job_action"] ?? "") === "create"))) {
            if (get_recruiter_id_by_user($pdo, $user_id) === false) {
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

                $_SESSION["errors_profile"] = [
                    "company_profile" => "Finish your company profile before posting a job."
                ];

                header("Location: view.php?page=company");
                exit();
            }
        }

        if ($isJobSave) {
            $jobId = (int) ($_POST["job_id"] ?? 0);
            $validated = validate_job_post_form($_POST);

            if (!empty($validated["errors"])) {
                $_SESSION["errors_job"] = $validated["errors"];
                $_SESSION["jobFormInput"] = $validated["form"];

                $formAction = $jobId > 0 ? "edit" : "create";
                $location = "view.php?page=jobpost&job_action=" . $formAction;

                if ($jobId > 0) {
                    $location .= "&job_id=" . $jobId;
                }

                header("Location: " . $location);
                exit();
            }

            if ($jobId > 0 && get_owned_job_post($pdo, $user_id, $jobId) === false) {
                $_SESSION["errors_job"] = [
                    "job_missing" => "That job could not be found."
                ];
                $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
                unset($_SESSION["jobFormInput"]);

                header("Location: view.php?page=jobs");
                exit();
            }

            $recruiterId = get_recruiter_id_by_user($pdo, $user_id);

            $pdo->beginTransaction();

            if ($jobId > 0) {
                update_owned_job_post($pdo, $user_id, $jobId, $validated["job"]);
            } else {
                $jobId = insert_job_post($pdo, (int) $recruiterId, $validated["job"]);
            }

            replace_job_skills($pdo, $jobId, $validated["skills"]);
            $pdo->commit();

            unset($_SESSION["jobFormInput"], $_SESSION["errors_job"]);
            $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
            $_SESSION["job_notice"] = $validated["job"]["status"] === "published"
                ? "Job published."
                : "Job saved as draft.";

            header("Location: view.php?page=jobs");
            exit();
        }

        if ($isJobClose || $isJobDelete) {
            $jobId = (int) ($_POST["job_id"] ?? 0);
            $ownedJob = $jobId > 0 ? get_owned_job_post($pdo, $user_id, $jobId) : false;

            if ($ownedJob === false) {
                $_SESSION["errors_job"] = [
                    "job_missing" => "That job could not be found."
                ];
                $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
                unset($_SESSION["jobFormInput"]);

                header("Location: view.php?page=jobs");
                exit();
            }

            if ($isJobDelete) {
                delete_owned_job_post($pdo, $user_id, $jobId);
                unset($_SESSION["currentJobPost"], $_SESSION["jobFormInput"], $_SESSION["errors_job"]);
                $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
                $_SESSION["job_notice"] = "Job deleted.";

                header("Location: view.php?page=jobs");
                exit();
            }

            update_owned_job_status($pdo, $user_id, $jobId, "closed");
            $_SESSION["currentJobPost"] = build_current_job_post($pdo, $user_id, $jobId);
            unset($_SESSION["jobFormInput"], $_SESSION["errors_job"]);
            $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
            $_SESSION["job_notice"] = "Job closed.";

            header("Location: view.php?page=jobpost&job_action=view&job_id=" . $jobId);
            exit();
        }

        $jobAction = (string) ($_GET["job_action"] ?? "");

        if ($jobAction === "create") {
            $_SESSION["currentJobPost"] = empty_job_post_form();
            unset($_SESSION["jobFormInput"], $_SESSION["errors_job"]);

            header("Location: view.php?page=jobpost&job_action=create");
            exit();
        }

        if ($jobAction === "edit" || $jobAction === "view") {
            $jobId = (int) ($_GET["job_id"] ?? 0);
            $currentJob = $jobId > 0 ? build_current_job_post($pdo, $user_id, $jobId) : false;

            if ($currentJob === false) {
                $_SESSION["errors_job"] = [
                    "job_missing" => "That job could not be found."
                ];
                $_SESSION["jobPostingData"] = get_recruiter_job_posting_data($pdo, $user_id);
                unset($_SESSION["jobFormInput"]);

                header("Location: view.php?page=jobs");
                exit();
            }

            $_SESSION["currentJobPost"] = $currentJob;
            unset($_SESSION["jobFormInput"], $_SESSION["errors_job"]);

            header("Location: view.php?page=jobpost&job_action=" . $jobAction . "&job_id=" . $jobId);
            exit();
        }

        header("Location: view.php?page=jobs");
        exit();

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Query Faild: " . $e->getMessage());
    }
}


