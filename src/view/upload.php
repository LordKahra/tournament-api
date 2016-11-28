<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\view\APIResponse;

if (!array_key_exists("type", $_GET)) {
    // Someone's playing with URLs. Get em out.
    echo APIResponse::getUnauthorizedResponse("An upload type must be specified.");
    exit();
}

switch ($_GET["type"]) {
    case UPLOAD_PROFILE:
        echo APIResponse::getFailure(-1, "Not yet implemented.");
        exit();
    case UPLOAD_STORE_BANNER:
        echo APIResponse::getFailure(-1, "Not yet implemented.");
        exit();
        // TODO: Non-breaking switch statement when implemented.
    case UPLOAD_TOURNAMENT:
        $file = (array_key_exists($_GET["type"], $_FILES)) ? $_FILES[$_GET["type"]] : false;
        break;
    default:
        // Someone's playing with URLs again. Get em out.
        echo APIResponse::getUnauthorizedResponse("Invalid upload type specified.");
        exit();
}

// Determine file information.
$profile = (array_key_exists(UPLOAD_PROFILE, $_FILES)) ? $_FILES[UPLOAD_PROFILE] : false;
$tournament = (isset($_POST["tournament"]) && !empty($_POST["tournament"])) ? $_POST["tournament"] : false;
//$tournament = $_FILES[UPLOAD_TOURNAMENT];
$store = (array_key_exists(UPLOAD_STORE_BANNER, $_FILES)) ? $_FILES[UPLOAD_STORE_BANNER] : false;

// Get type specific information.
$tournamentId = ($tournament && isset($_POST["tournament_id"]) && !empty($_POST["tournament_id"])) ? $_POST["tournament_id"] : false;
$storeId = ($store && isset($_POST["store_id"]) && !empty($_POST["store_id"])) ? $_POST["store_id"] : false;

$pathinfo = ($tournament && isset($_POST["pathinfo"]) && !empty($_POST["pathinfo"])) ? $_POST["pathinfo"] : false;

var_dump($_POST);

exit();
// Send it along.

$profileResponse = ($profile ? upload($profile["tmp_name"], pathinfo($profile["name"])["extension"], UPLOAD_PROFILE) : false);
$storeResponse = ($store ? upload($store["tmp_name"], pathinfo($store["name"])["extension"], UPLOAD_STORE_BANNER) : false);
$tournamentResponse = ($tournament ? uploadTournament($tournament["tmp_name"], pathinfo($tournament["name"])["extension"], $tournamentId) : false);

$response = array();

if ($profile) $response[UPLOAD_PROFILE]         = $profileResponse;
if ($store) $response[UPLOAD_STORE_BANNER]      = $storeResponse;
if ($tournament) $response[UPLOAD_TOURNAMENT]   = $tournamentResponse;

//print_r($response);

// Redirect if successful. Show an error if not.
if (array_key_exists(UPLOAD_PROFILE, $response) && $response[UPLOAD_PROFILE]["status"]) onSuccessfulUpload(UPLOAD_PROFILE);
if (array_key_exists(UPLOAD_STORE_BANNER, $response) && $response[UPLOAD_STORE_BANNER]["status"]) onSuccessfulUpload(UPLOAD_STORE_BANNER, $response[UPLOAD_TOURNAMENT]["file_id"]);
if (array_key_exists(UPLOAD_TOURNAMENT, $response) && $response[UPLOAD_TOURNAMENT]["status"]) onSuccessfulUpload(UPLOAD_TOURNAMENT, $response[UPLOAD_TOURNAMENT]["file_id"]);