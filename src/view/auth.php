<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\User;

use kahra\src\view\View;

$token = (isset($_POST["token"]) ? $_POST["token"] : false);

// Is the user already logged in?
/*if (isLoggedIn()) {
    echo View::formatSuccessResponse("You are already logged in.");
    exit();
}*/

// Did the user submit token data?

if ($token) {
    // The user submitted data. Validate.
    $status = User::authenticate($token);

    // Did they succeed? If so, return.
    echo (
        isLoggedIn()
            ? View::formatSuccessResponse("Successful login.")
            : View::formatFailureResponse(-1, "Invalid token."));
    exit();
} else {
    // The user didn't submit a token.
    // TODO: Should users who don't submit a token be logged out?
    echo (
        isLoggedIn()
            ? View::formatSuccessResponse("You are already logged in.")
            : View::formatFailureResponse(-1, "Authentication requires user token."));
    exit();
}