<?php
declare(strict_types=1);
require_once "../private/session.php";

function signin_inputs(): void
{
    $inputs = $_SESSION["signin_inputs"] ?? [];

    $usernameoremail = $inputs["usernameoremail"] ?? "";

    echo '<input
        type="text"
        name="usernameoremail"
        placeholder="Username or Email"
        value="' . htmlspecialchars($usernameoremail, ENT_QUOTES, "UTF-8") . '"
        autocomplete="username"
    >';

    echo '<input
        type="password"
        name="pwd"
        placeholder="Password"
        autocomplete="current-password"
    >';
}


function check_signin_errors(): void
{
    if (isset($_SESSION["errors_signin"])) {
        $errors = $_SESSION["errors_signin"];

        echo '<div class="signin-alert" role="alert">';

        foreach ($errors as $key => $value) {
            echo '<p class="error-message">'
                .  $key 
                . " : "
                . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                . '</p>';
        }

        echo '</div>';

    } 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lunch Orbit - Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php check_signin_errors(); ?>

    <main class="signin-container">

        <!-- Sign In Form -->
        <section class="signin-form-container">
            <form action="signIn.php" method="POST">
                <h1>SIGN IN</h1>

                <?php signin_inputs(); ?>

                <button type="submit">Login</button>

                <p class="signin-account-prompt">
                    Don't have an account?
                    <a href="../signUp/view.php">Sign up</a>
                </p>
            </form>
        </section>

        <!-- Decorative Background -->
        <aside class="signin-overlay-container">
            <div class="signin-overlay">
                <div class="signin-overlay-panel">
                    <h1 class="signin-title">
                        Hi Wellcome to Lunch Orbit<br>Seek them out or Find
                    </h1>

                    <p>
                        Good luck to your Journey
                    </p>
                </div>
            </div>
        </aside>

    </main>

</body>
</html>

<?php 
  unset($_SESSION["signin_inputs"]);
  unset($_SESSION["errors_signin"]);
?>

