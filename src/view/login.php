<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\User;

use kahra\src\view\View;

$email = (isset($_POST["email"]) ? $_POST["email"] : false);
$password = (isset($_POST["password"]) ? $_POST["password"] : false);

// Is the user already logged in?
if (isLoggedIn()) {
    echo View::formatSuccessResponse("You are already logged in.");
    exit();
}

// Did the user submit email and password data?

if ($email && $password) {
    // The user submitted data. Validate.
    $status = User::login($email, $password);

    // Did they succeed? If so, return.
    if (isLoggedIn()) {
        echo View::formatSuccessResponse("Successful login.");
    } else {
        echo View::formatFailureResponse(
            -1,
            ($status == User::STATUS_INVALID_EMAIL ? "Invalid email address." : "Your password is incorrect.")
        );
    }

    exit();
}

// The user submitted no data.

echo View::formatFailureResponse(-1, "Login requires email and password.");