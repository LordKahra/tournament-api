<?php

require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/pairings') . '/scripts/app_config.php');
//require_once (getenv('root_pairings') . '/scripts/app_config.php');

// Determine file information.
$profile = (array_key_exists(UPLOAD_PROFILE, $_FILES)) ? $_FILES[UPLOAD_PROFILE] : false;
$tournament = (array_key_exists(UPLOAD_TOURNAMENT, $_FILES)) ? $_FILES[UPLOAD_TOURNAMENT] : false;
$store = (array_key_exists(UPLOAD_STORE_BANNER, $_FILES)) ? $_FILES[UPLOAD_STORE_BANNER] : false;

// Get type specific information.
$tournamentId = ($tournament && isset($_POST["tournament_id"]) && !empty($_POST["tournament_id"])) ? $_POST["tournament_id"] : false;
$storeId = ($store && isset($_POST["store_id"]) && !empty($_POST["store_id"])) ? $_POST["store_id"] : false;

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