<?php

namespace kahra\src\view\store;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Store;
use kahra\src\exception\AuthenticationFailureException;
use kahra\src\util\Set;
use kahra\src\view\APIResponse;


if (!isAuthenticated()) {
    echo APIResponse::getUnauthorizedResponse("You must be logged in to update a store.");
    exit();
}

// TODO: Validate ownership.

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
foreach (explode(",", Store::FIELDS_UPDATE) as $field) {
    $value = Set::get($field, $_POST);
    if ($value) $data[$field] = $value;
}

$store_id = Set::get("store_id", $_POST);



if (!$store_id || !$user_id) {
    echo APIResponse::getMissingRequestDataResponse();
    exit();
}

/*if ($address_1) $data["address_1"] = $address_1;
if ($address_2) $data["address_2"] = $address_2;
if ($city) $data["city"] = $city;
if ($state) $data["state"] = $state;
if ($zip) $data["zip"] = $zip;
if ($country) $data["country"] = $country;*/

// TODO: Handle address data.

$affectedRows = Store::update($data, "user_id = '$user_id' AND id = '$store_id'");

if ($affectedRows == -1) echo APIResponse::getFailure(-1, "Failed to update due to query error.");
else if ($affectedRows < 1) echo APIResponse::getFailure(-1, "No stores were updated.");
else echo APIResponse::getSuccess("Successfully updated " . $affectedRows . " store.");

return true;