<?php

//echo str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');
require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');

use kahra\src\database\Object;
use kahra\src\database\User;

use kahra\src\view\View;

function getUser() {
    $users = false;

    if (array_key_exists("id", $_GET)) $users = View::parseResult(User::getById($_GET["id"]));
    elseif (array_key_exists("user_name", $_GET)) $users = View::parseResult(User::getByField("name", $_GET["user_name"]));
    else $users = View::parseResult(User::get());

    if ($users) {
        $object_arrays = User::parseRecords($users);

        echo View::formatSuccessResponse("Fetched users.", $object_arrays);
    } else {
        echo View::formatFailureResponse(-1, "No users were found.");
    }
}

getUser();

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




?>