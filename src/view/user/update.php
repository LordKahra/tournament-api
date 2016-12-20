<?php

namespace kahra\src\view\user;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\User;
use kahra\src\exception\AuthenticationFailureException;
use kahra\src\util\Set;
use kahra\src\view\APIResponse;

if (!isAuthenticated()) {
    echo APIResponse::getUnauthorizedResponse("You must be logged in to update your profile.");
    exit();
}

// Find the user.
$user_id = false;
try {
    $user_id = getLoggedInUserId();
} catch (AuthenticationFailureException $e) {
    echo APIResponse::getUnauthorizedResponse();
    exit();
}

// Create an array for the data.
$data = array();

// Fetch all allowed submitted data.
foreach (explode(",", User::FIELDS_UPDATE) as $field) {
    $value = Set::get($field, $_POST);
    if ($value) $data[$field] = $value;
}

/*echo APIResponse::get(false, -1, "Debugging.", $data);
exit();*/

if (!$data) {
    // There's no data. Exit.
    echo APIResponse::getEmptyDataResponse("You must submit data to update.");
    exit();
}

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