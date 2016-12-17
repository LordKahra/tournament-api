<?php

namespace kahra\src\view;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Store;
use kahra\src\database\User;
use kahra\src\view\APIResponse;
use kahra\src\view\View;

class StoreView extends View {
    static function show($objects=array()) {
        // TODO: Implement show() method.
    }

    static function handleAction($action) : bool {
        switch ($action) {
            case "add":
                if (!isAuthenticated()) {
                    echo APIResponse::getUnauthorizedResponse("You must be logged in to add a store.");
                    return true;
                }

                $user_id = getLoggedInUserId();
                $name = (isset($_POST["name"]) ? $_POST["name"] : false);
                $vanity_url = (isset($_POST["vanity_url"]) ? $_POST["vanity_url"] : false);
                $site = (isset($_POST["site"]) ? $_POST["site"] : false);

                if (!$name) {
                    echo APIResponse::getMissingRequestDataResponse("You must submit a name for your store.");
                    return true;
                }

                $store = Store::create(getLoggedInUserId(), $name, $vanity_url, $site);
                if ($store) {
                    echo View::formatSuccessResponse("Successfully created store.", $store);
                } else {
                    echo View::formatFailureResponse(-1, "Store creation failure. Better errors TODO.");
                }
                return true;
            case "update":
                if (!isAuthenticated()) {
                    echo APIResponse::getUnauthorizedResponse("You must be logged in to update a store.");
                    return true;
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

                return true;
            case "mine":
                if (!isAuthenticated()) {
                    echo APIResponse::getUnauthorizedResponse("You must be logged in to see your stores.");
                    return true;
                }

                $stores = Store::getByUserId(getLoggedInUserId());

                if (!$stores) echo APIResponse::getEmptyDataResponse("User " . getLoggedInUserId() . " has no stores.");
                else echo APIResponse::getSuccess("You have " . count($stores) . " stores.", $stores);
                return true;
            case "get":
                $id = (array_key_exists("store_id", $_GET) ? $_GET["store_id"] : false);
                $vanity_url = (array_key_exists("vanity_url", $_GET) ? $_GET["vanity_url"] : false);

                $stores = false;

                if ($id) {
                    $stores = Store::getById($id);
                } else if ($vanity_url) {
                    $stores = Store::getByField("vanity_url", $vanity_url);
                } else {
                    $stores = Store::get();
                }

                // TODO: Debugging.
                //$stores = Store::get();
                //echo APIResponse::getSuccess("Debugging.", $stores);
                //return true;

                if (!$stores || !is_array($stores) || count($stores) < 1) {
                    echo APIResponse::getFailure(APIResponse::CODE_EMPTY_SET, "No stores found.");
                } else {
                    echo APIResponse::getSuccess("Stores found.", $stores);
                }
                return true;
        }
        return false;
    }
}

StoreView::handleRequest();
exit();
?>