<?php

namespace kahra\src\view;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Object;
use kahra\src\database\User;

use kahra\src\view\View;

class UserView extends View {
    static function show($objects=array()) {
        // TODO: STRIP PASSWORDS
        if (!$objects) {
            echo static::formatFailureResponse(-1, "No users were found.");
        } else {
            foreach($objects as $object) {
                if (array_key_exists("password", $object)) unset($object["password"]);
            }
            echo View::formatSuccessResponse("Fetched users.", $objects);
        }
    }

    static function handleAction($action) : bool {
        switch ($action) {
            case "get":
                $users = false;
                if (array_key_exists("user_id", $_GET)) $users = User::getById($_GET["user_id"]);
                elseif (array_key_exists("user_name", $_GET)) $users = User::getByField("name", $_GET["user_name"]);
                else $users = User::get();
                echo ($users
                    ? View::formatSuccessResponse("Fetched users.", $users)
                    : static::formatFailureResponse(-1, "No users were found."));
                return true;
        }
        return false;
    }
}

function getUser() {
    $users = false;

    //if (array_key_exists("id", $_GET)) $users = UserView::parseResult(User::getById($_GET["id"]));
    if (array_key_exists("user_id", $_GET)) $users = User::getById($_GET["user_id"]);
    //elseif (array_key_exists("user_name", $_GET)) $users = UserView::parseResult(User::getByField("name", $_GET["user_name"]));
    elseif (array_key_exists("user_name", $_GET)) $users = User::getByField("name", $_GET["user_name"]);
    //else $users = UserView::parseResult(User::get());
    else $users = User::get();

    return $users;

    /*if ($users) {
        //$object_arrays = User::parseRecords($users);

        //echo View::formatSuccessResponse("Fetched users.", $object_arrays);
        echo View::formatSuccessResponse("Fetched users.", $users);
    } else {
        echo View::formatFailureResponse(-1, "No users were found.");
    }*/
}

//UserView::show(getUser());

/*$objects = false;
$errors = [];
$title = false;

$type = array_key_exists("type", $_GET) ? $_GET["type"] : false;
$class = false;

$response = array(
    "status" => 0,
    "code" => -1,
    "message" => "Fatal error with API. An administrator has been informed.",
    "objects" => array()
);

switch ($type) {
    case "user":
        $class = new User();
        break;
    case "tournament":
        $class = new Tournament();
        break;
    default:
        $response["code"] = -1;
        $response["message"] = "Invalid API action '$type' called. Aborting.";

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
}

if (array_key_exists("id", $_GET)) {
    $result = $class::getById($_GET["id"]);

    if ($result) {
        while ($object = mysqli_fetch_assoc($result)) {
            if (!$objects) $objects = array();
            $objects[] = $object;
            //$title = $object["tournament_name"];
            $title = "User Profile";
            //$og_url = "tournament/" . $_GET["tournament_id"];
        }
    } else {
        $errors[] = array("priority" => "low", "message" => "User not found.");
    }
} else {
    // No query was submitted.
    // Show all objects.
    $result = $class::get();

    if ($result) {
        while ($object = mysqli_fetch_assoc($result)) {
            if (!$objects) $objects = array();
            $objects[] = $object;
        }
    }
    //$errors[] = array("priority" => "low", "message" => "There are " . count($objects) . " tournaments in the database.");
}

// STEP 2: PRINT JSON

if ($objects) {
    $object_arrays = $class::parseRecords($objects);

    /*foreach ($object_arrays as $object) {
        $pairing_result = Pairing::getByTournament($object["id"]);
        $pairings = false;
        while ($pairing = mysqli_fetch_assoc($pairing_result)) {
            if (!$pairings) $pairings = array();
            $pairings[] = $pairing;
        }
        $object["pairings"] = $pairings ? Pairing::getMatches($pairings) : array();

        //TournamentView::printTournament($tournament);
    }

    $response["status"] = 1;
    $response["code"] = 200;
    $response["message"] = "Success.";
    $response["objects"] = $object_arrays;

    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    ?>No users found.<?php
}*/

UserView::handleRequest();
exit();


?>