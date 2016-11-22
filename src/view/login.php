<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\User;

use kahra\src\exception\InvalidEmailException;
use kahra\src\exception\InvalidPasswordException;
use kahra\src\view\View;

// TODO: "Remember me" boolean value.

$email = (isset($_POST["email"]) ? $_POST["email"] : false);
$password = (isset($_POST["password"]) ? $_POST["password"] : false);
$persistent = (isset($_POST["persistent"]) ? $_POST["persistent"] : true);

// Is the user already logged in?
if (isLoggedIn()) {
    echo View::formatSuccessResponse("You are already logged in.");
    exit();
}

// Did the user submit email and password data?

if ($email && $password) {
    // The user submitted data. Validate.
    try {
        // An error is thrown if the user failed to log in. Proceed.
        $user = User::login($email, $password);
        if (!$user) throw new InvalidPasswordException("This should never happen, but login failed. Someone on the dev team has been notified.");
// TODO: FAILS RIGHT HERE FUCK
        //echo View::formatFailureResponse(-1, "fuck");
        //exit();
        echo View::formatSuccessResponse("Successful login.", $user);
        exit();
    } catch (InvalidEmailException $e) {
        echo View::formatFailureResponse(-1, $e->getMessage());
        exit();
    } catch (InvalidPasswordException $e) {
        echo View::formatFailureResponse(-1, $e->getMessage());
        exit();
    }
}

// The user submitted no data.

echo View::formatFailureResponse(-1, "Login requires email and password.");
exit();