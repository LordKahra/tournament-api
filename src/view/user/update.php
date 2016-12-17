<?php

namespace kahra\src\view\user;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\User;
use kahra\src\exception\AuthenticationFailureException;
use kahra\src\view\APIResponse;

if (!isAuthenticated()) {
    echo APIResponse::getUnauthorizedResponse("You must be logged in to update your profile.");
    exit();
}

// Find the user.
try {
    $user_id = getLoggedInUserId();
} catch (AuthenticationFailureException $e) {
    echo APIResponse::getUnauthorizedResponse();
    exit();
}

// TODO: Add more options for user updates.
// Get post data into variables.
$dci = (isset($_POST["dci"]) ? $_POST["dci"] : false);
$email = (isset($_POST["email"]) ? $_POST["email"] : false);

// Create an array for the data.
$data = array();

// Add data to the array.
if ($dci)          $data["dci"] = $dci;
if ($email)          $data["email"] = $email;

// Attempt to update the database.
$affectedRows = User::update($data, "id = '$user_id'");

if ($affectedRows > 0) {
    // Success. Fetch the new data.
    $objects = User::getById($user_id);
    if ($objects) {
        $user = false;
        foreach ($objects as $object) $user = $object;
        echo APIResponse::getSuccess("User successfully updated.", $user);
        exit;
    } else {
        // This should never happen, but let's assume weird things can happen.
        echo APIResponse::getSuccess("User updated, but retrieval failure occurred.");
        exit();
    }
} else {
    echo APIResponse::getFailure(-1, "Failed to update the user.");
    exit();
}