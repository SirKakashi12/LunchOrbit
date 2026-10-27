<?php
require_once '../private/session.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $role = $_POST["role"];


    // Job Seeker and Recruiter have this in signup shit
    $username = $_POST["username"]?? "";
    $fullname = $_POST["fullname"]?? "";
    $pwd = $_POST["pwd"]?? "";
    $repwd = $_POST["repwd"]?? "";
    $email = $_POST["email"]?? "";
    $cellno = $_POST["cellno"]?? "";
    $role = $_POST["role"]?? "";



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

        if (is_field_empty($username , $fullname , $pwd , $repwd , $email , $cellno, $role)){
            $errors["emty_input"] = "Fill in all the fields!";
        }
        if(is_email_invalid($email)){
            $errors["invalid_email"] = "Invaled email used!";
        }
        if(is_username_taken($pdo, $username)){
            $errors["username_taken"] = "username already taken!";
        }
        if(is_email_regesterd($pdo, $email)){
            $errors["email_used"] = "Email already taken!";
        }
        if(is_password_and_repassword_not_the_same($pwd,  $repwd)){
            $errors["password_incorrrect"] = "Password is not the same!";
        }



        if ($errors) {
            $_SESSION["errors_signup"] = $errors;

            $signupInputs = [
                "username" => $username,
                "fullname" => $fullname,
                "email" => $email,
                "cellno" => $cellno
            ];

            $_SESSION["signup_inputs"] = $signupInputs;

            $redirect = ($role === "recruiter")
                ? "./view.php?role=recruiter"
                : "./view.php?role=job_seeker";

            header("Location: " . $redirect);
            die();
        }

        create_user( $pdo, $username, $fullname, $pwd, $email, $cellno, $role);

        $redirect = ($role === "recruiter")
                ? "./view.php?role=recruiter&signup=success"
                : "./view.php?role=job_seeker&signup=success";
        header("Location: " . $redirect);
        die();

        
    } catch (Exception $e) {
        die("Query Faild: " . $e->getMessage());
    }

} else {
    header("Location: ./view.php");
    exit();
}