<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {

    $usernameoremail = $_POST["usernameoremail"]?? "";
    $pwd = $_POST["pwd"]?? "";



    // // Recruiter signup shit
    // $companyName = $_POST["companyName"];
    // $companyWebsite = $_POST["companyWebsite"];
    // $companyNumber = $_POST["companyNumber"];
    // $PositioninCompany = $_POST["PositioninCompany"];

    // // Job Seeker signup shit
    // $education_level = $_POST["education_level"];
    // $education_status = $_POST["education_status"];
    // $school_name = $_POST["school_name"];
    // $degree_course = $_POST["degree_course"];
    // $graduation_year = $_POST["graduation_year"];
    // $work_experience_level = $_POST["work_experience_level"];
    // $city = $_POST["city"];
    // $province = $_POST["province"];
    // $profile_summary = $_POST["profile_summary"];
    // $preferred_work_setup = $_POST["preferred_work_setup"];
    // $preferred_employment_type = $_POST["preferred_employment_type"];


    try {
        require_once '../private/database.php';
        require_once 'model.php';
        require_once 'contr.php';

        require_once '../private/session.php';

        //Error Handlers
        $errors =[];
        $result = null;
        
        if (is_field_empty($usernameoremail, $pwd)) {
            $errors["empty_input"] = "Fill in all the fields!";
        } else {
            $result = get_user($pdo, $usernameoremail);

            if (!$result) {
                $errors["incorrect_credentials"] =
                    "Invalid username/email or password!";
            } elseif (is_password_wrong($pwd, $result["password"])) {
                $errors["incorrect_credentials"] =
                    "Invalid username/email or password!";
            }
        }

        if ($errors) {
            $_SESSION["errors_signin"] = $errors;

            $signupInputs = [
                "usernameoremail" => $usernameoremail,
                "pwd" => $pwd,

            ];

            $_SESSION["signin_inputs"] = $signupInputs;

            header("Location: ./view.php");
            exit();
        }


        session_regenerate_id(true);

        $_SESSION["user_id"] = $result["user_id"];
        $_SESSION["user_username"] = $result["username"];
        $_SESSION["last_regeneration"] = time();

        //check what role of the user
        if($result["role"] === "job_seeker"){
            header("Location: ../jobSeekers/view.php");
            exit(); 
        }

        if($result["role"] === "recruiter"){
            header("Location: ../recruiters/view.php?page=dashboard");
            exit(); 
        }


   
            
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }

} else {
    header("Location: ./view.php");
    exit();
}