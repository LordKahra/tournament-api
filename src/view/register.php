<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\User;

use kahra\src\exception\InvalidInputException;
use kahra\src\view\View;

$email = (isset($_POST["email"]) ? $_POST["email"] : false);
$password = (isset($_POST["password"]) ? $_POST["password"] : false);
$dci = (isset($_POST["dci"]) ? $_POST["dci"] : false);

/*
$email = (isset($_GET["email"]) ? $_GET["email"] : false);
$password = (isset($_GET["password"]) ? $_GET["password"] : false);
$dci = (isset($_GET["dci"]) ? $_GET["dci"] : false);
*/

// Is the user already logged in?
// TODO: Moving to statelessness.
/*if (isLoggedIn()) {
    echo View::formatSuccessResponse("You are already logged in.");
    exit();
}*/

// Did the user submit email, password and dci data?

if ($email && $password && $dci) {
    // The user submitted data. Validate.
    try {
        $user = User::register($email, $password, $dci);

        // Did they succeed? If so, return their information.
        //if (isLoggedIn()) {
        if ($user) {
            echo View::formatSuccessResponse("Successfully registered.", $user);
        } else {
            echo View::formatFailureResponse(-1, "Registration failure. Better errors TODO.");
                /*($status == User::STATUS_DUPLICATE_EMAIL
                    ? "That email already exists."
                    : "That dci is already associated with an account.")*/
            // TODO: Better error checking.
        }
    } catch (InvalidInputException $e) {
        echo View::formatFailureResponse(-1, $e->getMessage());
    }

    exit();
}

// The user is not logged in and didn't submit enough data.

echo View::formatFailureResponse(-1, "Registration requires email, password and dci.");


