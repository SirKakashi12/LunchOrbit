<?php

require_once '../private/session.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../signIn/view.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["logout"])) {

    $_SESSION = [];

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

    session_destroy();

    header("Location: ../signIn/view.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["personal_update"])) {

    $username = trim((string) ($_POST["username"] ?? ""));
    $fullname = trim((string) ($_POST["fullname"] ?? ""));
    $email = trim((string) ($_POST["email"] ?? ""));
    $cellno = trim((string) ($_POST["cellno"] ?? ""));

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];
        $errors = [];

        if (get_jobseeker_user_info($pdo, $user_id) === false) {
            header("Location: ../signIn/view.php");
            exit();
        }

        if (is_field_empty_in_personal($username, $fullname, $email, $cellno)) {
            $errors["empty_input"] = "Fill in all the fields!";
        }

        if (is_username_taken_for_other_user($pdo, $user_id, $username)) {
            $errors["username_taken"] = "Username already taken!";
        }

        if (is_email_invalid($email)) {
            $errors["invalid_email"] = "Invalid email address!";
        }

        if (is_email_registered_for_other_user($pdo, $user_id, $email)) {
            $errors["email_used"] = "Email already taken!";
        }

        if (!empty($errors)) {
            $_SESSION["errors_profile"] = $errors;
            $_SESSION["jobseekerFormInput"] = [
                "username" => $username,
                "fullname" => $fullname,
                "email" => $email,
                "cellno" => $cellno,
            ];

            header("Location: view.php?page=profile");
            exit();
        }

        update_user_database($pdo, $user_id, $username, $fullname, $email, $cellno);
        $_SESSION["user_username"] = $username;
        unset($_SESSION["jobseekerFormInput"]);

        header("Location: view.php?page=profile&profile=success");
        exit();
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["jobseeker_update"])) {

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];

        if (get_jobseeker_user_info($pdo, $user_id) === false) {
            header("Location: ../signIn/view.php");
            exit();
        }

        $validated = validate_jobseeker_profile($_POST);

        if (!empty($validated["errors"])) {
            $_SESSION["errors_profile"] = $validated["errors"];
            $_SESSION["jobseekerFormInput"] = $validated["form"];

            header("Location: view.php?page=profile");
            exit();
        }

        $pdo->beginTransaction();
        upsert_jobseeker_profile($pdo, $user_id, $validated["profile"]);
        $jobseekersId = get_jobseeker_id_by_user($pdo, $user_id);
        replace_jobseeker_skills($pdo, (int) $jobseekersId, $validated["skills"]);
        $pdo->commit();

        unset($_SESSION["jobseekerFormInput"]);

        header("Location: view.php?page=profile&profile=success");
        exit();
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["apply_job"])) {

    $jobId = (int) ($_POST["job_id"] ?? 0);
    $coverLetter = trim((string) ($_POST["cover_letter"] ?? ""));

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];
        $jobseekersId = require_jobseeker_profile($pdo, $user_id);

        if ($jobseekersId === false) {
            $_SESSION["errors_jobseeker"] = [
                "profile" => "Save your job seeker profile before applying.",
            ];

            header("Location: view.php?page=profile");
            exit();
        }

        if (strlen($coverLetter) > 5000) {
            $_SESSION["errors_jobseeker"] = [
                "cover_letter" => "Cover letter must be 5000 characters or fewer.",
            ];

            header("Location: view.php?page=job&job_id=" . $jobId);
            exit();
        }

        $job = get_open_job($pdo, $jobId, $jobseekersId);

        if ($job === false) {
            $_SESSION["errors_jobseeker"] = [
                "job_missing" => "That job is not open for applications.",
            ];

            header("Location: view.php?page=jobs");
            exit();
        }

        if (jobseeker_has_application($pdo, $jobId, $jobseekersId)) {
            $_SESSION["errors_jobseeker"] = [
                "already_applied" => "You already applied to this job.",
            ];

            header("Location: view.php?page=job&job_id=" . $jobId);
            exit();
        }

        insert_application(
            $pdo,
            $jobId,
            $jobseekersId,
            $coverLetter === "" ? null : $coverLetter
        );

        $_SESSION["jobseeker_notice"] = "Application sent.";

        header("Location: view.php?page=applications");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === "23000") {
            $_SESSION["errors_jobseeker"] = [
                "already_applied" => "You already applied to this job.",
            ];

            header("Location: view.php?page=applications");
            exit();
        }

        die("Query Faild: " . $e->getMessage());
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["save_job"])) {

    $jobId = (int) ($_POST["job_id"] ?? 0);
    $returnPage = (string) ($_POST["return_page"] ?? "jobs");

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];
        $jobseekersId = require_jobseeker_profile($pdo, $user_id);

        if ($jobseekersId === false) {
            $_SESSION["errors_jobseeker"] = [
                "profile" => "Save your job seeker profile before saving a job.",
            ];

            header("Location: view.php?page=profile");
            exit();
        }

        $job = get_open_job($pdo, $jobId, $jobseekersId);

        if ($job === false) {
            $_SESSION["errors_jobseeker"] = [
                "job_missing" => "That job is no longer open.",
            ];

            header("Location: view.php?page=jobs");
            exit();
        }

        if (!jobseeker_has_saved_job($pdo, $jobId, $jobseekersId)) {
            insert_saved_job($pdo, $jobId, $jobseekersId);
        }

        $_SESSION["jobseeker_notice"] = "Job saved.";

        if ($returnPage === "job") {
            header("Location: view.php?page=job&job_id=" . $jobId);
            exit();
        }

        if ($returnPage === "jobs") {
            header("Location: view.php?page=jobs");
            exit();
        }

        header("Location: view.php?page=saved");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === "23000") {
            $_SESSION["jobseeker_notice"] = "Job saved.";
            header("Location: view.php?page=saved");
            exit();
        }

        die("Query Faild: " . $e->getMessage());
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["unsave_job"])) {

    $jobId = (int) ($_POST["job_id"] ?? 0);
    $returnPage = (string) ($_POST["return_page"] ?? "saved");

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];
        $jobseekersId = require_jobseeker_profile($pdo, $user_id);

        if ($jobseekersId === false) {
            header("Location: view.php?page=saved");
            exit();
        }

        delete_saved_job($pdo, $jobId, $jobseekersId);
        $_SESSION["jobseeker_notice"] = "Job removed from saved jobs.";

        if ($returnPage === "job") {
            header("Location: view.php?page=job&job_id=" . $jobId);
            exit();
        }

        if ($returnPage === "jobs") {
            header("Location: view.php?page=jobs");
            exit();
        }

        header("Location: view.php?page=saved");
        exit();
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["withdraw_application"])) {

    $applicationId = (int) ($_POST["application_id"] ?? 0);

    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        $user_id = (int) $_SESSION["user_id"];
        $jobseekersId = require_jobseeker_profile($pdo, $user_id);

        if ($jobseekersId === false) {
            header("Location: view.php?page=applications");
            exit();
        }

        $withdrawn = withdraw_jobseeker_application($pdo, $jobseekersId, $applicationId);

        if ($withdrawn) {
            $_SESSION["jobseeker_notice"] = "Application withdrawn.";
        } else {
            $_SESSION["errors_jobseeker"] = [
                "withdraw" => "That application can no longer be withdrawn.",
            ];
        }

        header("Location: view.php?page=applications");
        exit();
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["navigate"])) {

    $page = (string) $_POST["navigate"];
    $allowedPages = ["dashboard", "jobs", "applications", "saved", "profile"];

    if (!in_array($page, $allowedPages, true)) {
        header("Location: view.php?page=dashboard");
        exit();
    }

    header("Location: view.php?page=" . urlencode($page));
    exit();
}

header("Location: view.php?page=dashboard");
exit();
