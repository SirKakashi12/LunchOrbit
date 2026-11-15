<?php
require_once '../private/session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seekers</title>
</head>
<body>
    <h1>Hello <?php echo $_SESSION["user_username"]; ?></h1>
    <h2>ID: <?php echo $_SESSION["user_id"]; ?></h2>
</body>
</html>