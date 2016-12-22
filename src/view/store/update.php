<?php

namespace kahra\src\view\store;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Location;
use kahra\src\database\Store;
use kahra\src\exception\AuthenticationFailureException;
use kahra\src\exception\EmptyInputException;
use kahra\src\exception\MissingInputException;
use kahra\src\exception\SQLInsertException;
use kahra\src\exception\SQLUpdateException;
use kahra\src\util\Set;
use kahra\src\view\APIResponse;

// Authenticate the user.
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

// Find the store.
$store_id = Set::get("store_id", $_POST);

if (!$store_id) {
    echo APIResponse::getFailure(-1, "You must submit a store to be updated.");
    exit();
}

// Find all other data.
$storeData = getStoreData();
$locationData = getLocationData();

// Create response variables.
$storeResponse = false;
$locationResponse = false;

$storeError = false;
$locationError = false;

$hasLocation = hasLocation($store_id);

// Validate submitted data.
if (!$storeData && !$locationData) {
    echo APIResponse::getFailure(-1, "You must submit data to update.");
    exit();
}

// Upsert the location.
try {
    if ($hasLocation) {
        $locationResponse = updateLocation($store_id, $locationData);
        // If the update outright failed, set error to true.
        if ($locationResponse < 0) $locationError = true;
    } else {
        $locationResponse = insertLocation($locationData);
        $storeData["location_id"] = $locationResponse;
    }
} catch (SQLInsertException $e) {
    // There was an error inserting the location.
    $locationError = true;
} catch (MissingInputException $e) {
    // There was a missing required field.
    $locationError = true;
} catch (EmptyInputException $e) {
    // There was -no- input.
}

// Update the store.
try {
    $storeResponse = updateStore($user_id, $store_id, $storeData);
    // If the update outright failed, set error to true.
    if ($storeResponse < 0) $storeError = true;
} catch (EmptyInputException $e) {
    // Do nothing.
}

if (!$storeError && !$locationError) {
    // Woo! Success. Let's try to update the Location's latitude and longitude.
    $locations = Location::getByStoreId($store_id);

    $message = "Updated store.";

    if ($locations) {
        $location = array();
        foreach ($locations as $object) $location = $object;



        $address_1 = Set::get("address_1", $location);
        $address_2 = Set::get("address_2", $location);
        $city = Set::get("city", $location);
        $state = Set::get("state", $location);
        $zip = Set::get("zip", $location);
        $country = Set::get("country", $location);

        $geocodeData = Location::geocode($address_1, $address_2, $city, $state, $zip, $country);

        //$message = $geocodeData;
        //var_dump($geocodeData);

        if ($geocodeData) {
            // Geocoded data was found. Update.
            Location::update($geocodeData, Location::createWhereClauseByStore($store_id));

        } else {
            $message = "Updated store. Issue updating geocoded location data.";
        }

    } else {
        $message = "Location not found for geocoding.";
    }

    echo APIResponse::getSuccess($message);
    exit();
}

echo APIResponse::getFailure(-1, "Failed to update store/location information.");
exit();

// TODO: Handle address data.

function getStoreData() : array {
    $data = array();

    // Fetch all allowed submitted data.
    foreach (explode(",", Store::FIELDS_UPDATE) as $field) {
        $value = Set::get($field, $_POST);
        if ($value) $data[$field] = $value;
    }

    return $data;
}
function getLocationData() : array {
    $data = array();

    // Fetch all allowed submitted data.
    foreach (explode(",", Location::FIELDS_UPDATE) as $field) {
        $value = Set::get($field, $_POST);
        if ($value) $data[$field] = $value;
    }

    return $data;
}

/**
 * @param string $store_id
 * @return bool
 */
function hasLocation(string $store_id) {
    $locations = Location::getByStoreId($store_id);
    return (($locations) && (is_array($locations)) && (count($locations) > 0));
}

/**
 * @param string|string $user_id
 * @param string $store_id
 * @param array $data
 * @return int|bool
 * @throws EmptyInputException
 */
function updateStore(string $user_id, string $store_id, array $data) : bool {
    if (!$store_id || !$user_id || !$data) {
        throw new EmptyInputException("No store data provided.");
    }

    $affectedRows = Store::update($data, "user_id = '$user_id' AND id = '$store_id'");

    return ($affectedRows >= 0);
}

function insertLocation(array $data) : int {
    if (!$data) throw new EmptyInputException("No location data provided.");

    $locationResponse = Location::insert($data);

    // If the insert succeeded, return true.

    return $locationResponse;
}

/**
 * @param string $store_id
 * @throws EmptyInputException
 * @throws SQLInsertException
 * @return bool
 */
function updateLocation(string $store_id, array $data) : bool {
    if (!$data) throw new EmptyInputException("No location data provided.");

    $locationResponse = Location::update($data, Location::createWhereClauseByStore($store_id));

    // If the update succeeded, return true.
    return ($locationResponse > 0);
}