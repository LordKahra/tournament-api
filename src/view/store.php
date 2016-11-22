<?php

//require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');
require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\Store;
use kahra\src\database\User;
use kahra\src\view\APIResponse;
use kahra\src\view\View;

if (array_key_exists("action", $_GET)) {
    switch ($_GET["action"]) {
        case "add":
            if (!User::isAuthenticated()) {
                echo APIResponse::getUnauthorizedResponse("You must be logged in to add a store.");
                break;
            }

            $user_id = getLoggedInUserId();
            $name = (isset($_POST["name"]) ? $_POST["name"] : false);
            $vanity_url = (isset($_POST["vanity_url"]) ? $_POST["vanity_url"] : false);
            $site = (isset($_POST["site"]) ? $_POST["site"] : false);

            if (!$name) echo APIResponse::getMissingRequestDataResponse("You must submit a name for your store.");

            break;
        case "update":
            if (!User::isAuthenticated()) {
                echo APIResponse::getUnauthorizedResponse("You must be logged in to update a store.");
                break;
            }

            $user_id = getLoggedInUserId();
            $store_id = (isset($_POST["store_id"]) ? $_POST["store_id"] : false);
            $name = (isset($_POST["name"]) ? $_POST["name"] : false);
            $vanity_url = (isset($_POST["vanity_url"]) ? $_POST["vanity_url"] : false);
            $site = (isset($_POST["site"]) ? $_POST["site"] : false);
            $address_1 = (isset($_POST["address_1"]) ? $_POST["address_1"] : false);
            $address_2 = (isset($_POST["address_2"]) ? $_POST["address_2"] : false);
            $city = (isset($_POST["city"]) ? $_POST["city"] : false);
            $state = (isset($_POST["state"]) ? $_POST["state"] : false);
            $zip = (isset($_POST["zip"]) ? $_POST["zip"] : false);
            $country = (isset($_POST["country"]) ? $_POST["country"] : false);

            $data = array();
            if ($name) $data["name"] = $name;
            if ($vanity_url) $data["vanity_url"] = $vanity_url;
            if ($site) $data["site"] = $site;
            if ($address_1) $data["address_1"] = $address_1;
            if ($address_2) $data["address_2"] = $address_2;
            if ($city) $data["city"] = $city;
            if ($state) $data["state"] = $state;
            if ($zip) $data["zip"] = $zip;
            if ($country) $data["country"] = $country;

            $affectedRows = Store::update($data, "user_id = '$user_id' AND id = '$store_id'");

            if ($affectedRows == -1) echo APIResponse::getFailure(-1, "Failed to update due to query error.");
            else if ($affectedRows < 1) echo APIResponse::getFailure(-1, "No stores were updated.");
            else echo APIResponse::getSuccess("Successfully updated " . $affectedRows . " store.");

            break;
        case "mine":
            if (!User::isAuthenticated()) {
                echo APIResponse::getUnauthorizedResponse("You must be logged in to see your stores.");
                break;
            }

            $stores = Store::getByUserId(getLoggedInUserId());

            if (!$stores) echo APIResponse::getEmptyDataResponse("You have no stores.");
            else echo APIResponse::getSuccess("You have " . count($stores) . " stores.", $stores);
            break;
        case "get":
            $id = (isset($_GET["store_id"]) ? $_GET["store_id"] : false);
            $vanity_url = (isset($_GET["vanity_url"]) ? $_GET["vanity_url"] : false);

            $stores = false;

            if ($id) {
                $stores = Store::getById($id);
            } else if ($vanity_url) {
                $stores = Store::getByField("vanity_url", $vanity_url);
            }

            if (!$stores || !is_array($stores) || count($stores) < 1) {
                echo APIResponse::getFailure(APIResponse::CODE_EMPTY_SET, "No stores found.");
            } else {
                echo APIResponse::getSuccess("Store found.", $stores);
            }



            break;
    }
}

exit();
?>