<?php

declare(strict_types=1);
require_once "../private/session.php";




function signup_inputs(): void
{
    $inputs = $_SESSION["signup_inputs"] ?? [];
    $errors = $_SESSION["errors_signup"] ?? [];

    $username = (isset($errors["username_taken"]))? "" : ($inputs["username"] ?? "");

    $fullname = $inputs["fullname"] ?? "";
    $email = (
        isset($errors["invalid_email"]) ||
        isset($errors["email_used"])
    ) ? "" : ($inputs["email"] ?? "");

    $cellno = $inputs["cellno"] ?? "";

    echo '<input type="text" name="username" placeholder="Username" value="' .
        htmlspecialchars($username, ENT_QUOTES, "UTF-8") . '">';

    echo '<input type="text" name="fullname" placeholder="Fullname" value="' .
        htmlspecialchars($fullname, ENT_QUOTES, "UTF-8") . '">';

    echo '<input type="password" name="pwd" placeholder="Password">';

    echo '<input type="password" name="repwd" placeholder="Confirm Password">';

    echo '<input type="email" name="email" placeholder="Email" value="' .
        htmlspecialchars($email, ENT_QUOTES, "UTF-8") . '">';

    echo '<input type="tel" name="cellno" placeholder="Cellphone Number" value="' .
        htmlspecialchars($cellno, ENT_QUOTES, "UTF-8") . '">';

}

function check_signup_errors(): void
{
    if (isset($_SESSION["errors_signup"])) {
        $errors = $_SESSION["errors_signup"];

        echo '<div class="signup-alert" role="alert">';

        foreach ($errors as $key => $value) {
            echo '<p class="error-message">'
                .  $key 
                . " : "
                . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                . '</p>';
        }

        echo '</div>';

    } else if (isset($_GET["signup"]) && $_GET["signup"] === "success") {
      echo '<div class="signup-alert" role="alert">';
      echo '<p class="success-message"> Congratiolation Regestration is a Successfull Please Login </p>';
      echo '</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lunch Orbit</title>
  <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php check_signup_errors(); ?>
  <div class="container" id="container">
  <!-- JobSeekers -->
    <div class="form-container JobSeekers-container">
      <form action="signUp.php" method="post">
        <h1>SIGN UP</h1>
        <?php signup_inputs(); ?>
        <input type="hidden" name="role" value="job_seeker">
        <button>Register</button>

        <p style="color: black;">
          Already have an account?
        <a href="">Log in</a>            
        </p>


      </form>
    </div>
  <!-- Recruiter -->
    <div class="form-container recruiter-container">
      <form action="signUp.php" method="post">
        <h1>SIGN UP</h1>
        <?php signup_inputs(); ?>

        <input type="hidden" name="role" placeholder="role" value="recruiter">
        <button>Register</button>


        <p style="color: black;">
          Already have an account?
        <a href="">Log in</a>            
        </p>

      </form>
    </div>

    <div class="overlay-container">
      <div class="overlay">

  <!-- JobSeekers -->
        <div class="overlay-panel overlay-right">
          <h1 class="title">Hi JobSeekers<br> Journy Now</h1>
          <p>If Your a Recruiter and don't have an account yet, join here and start your recruiting.</p>
          <button class="ghost" id="jobseeker">recruiter
            <i class="lni lni-arrow-right jobseeker"></i>
          </button>
        </div>

  <!-- Recruiter -->
        <div class="overlay-panel overlay-left">
          <h1 class="title">Hi Recruiter <br> Journy Now</h1>
          <p>If Your a JobSeekers and don't have an account yet, join here and have fun</p>
          <button class="ghost" id="recruiter">JobSeekers
            <i class="lni lni-arrow-left recruiter"></i>
          </button>
        </div>




      </div>
    </div>

  </div>

<script>
const jobseekerButton = document.getElementById("jobseeker");
const recruiterButton = document.getElementById("recruiter");
const container = document.getElementById("container");

function showRecruiterForm() {
  container.classList.add("right-panel-active");
}

function showJobseekerForm() {
  container.classList.remove("right-panel-active");
}

jobseekerButton.addEventListener("click", showRecruiterForm);
recruiterButton.addEventListener("click", showJobseekerForm);

const params = new URLSearchParams(window.location.search);

if (params.get("role") === "recruiter") {
  showRecruiterForm();
} else {
  showJobseekerForm();
}


</script>

</body>
</html>

<?php 
  unset($_SESSION["signup_inputs"]);
  unset($_SESSION["errors_signup"]);
?>


