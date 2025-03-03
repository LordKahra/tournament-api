<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Tournament;
use kahra\src\database\Upload;
use kahra\src\database\User;
use kahra\src\exception\SQLInsertException;
use kahra\src\file\WERParser;
use kahra\src\util\Set;
use kahra\src\util\Validation;

use kahra\src\view\APIResponse;

$tournament = (array_key_exists("upload_data", $_POST)) ? $_POST["upload_data"] : false;

// Get type specific information.
$tournament_id = Set::get("tournament_id", $_POST);
$extension = Set::get("extension", $_POST);

if (!Validation::validateWERDocument($tournament, $extension)) {
    echo APIResponse::getFailure(-1, "Upload is not a valid WERDocument. $extension");
    exit();
}

// Get the tournament metadata, or create it.
try {
    $tournament_id = getTournamentId($tournament_id);
    $uploadId = getUploadId($tournament_id);
} catch (SQLInsertException $e) {
    echo APIResponse::getFailure(-1, $e->getMessage() . " Query: " . $e->getQuery());
    exit();
}

// TODO: HERE

// TODO: Validate tournament_id and upload_id.

/*$fileDir = TOURNAMENT_UPLOAD_DIRECTORY;
if (!Validation::validateUploadDirectory($fileDir)) {
    echo APIResponse::getFailure(-1, "Critical backend error. An administrator has been notified.");
    exit();
}*/

//$fullPath = TOURNAMENT_UPLOAD_DIRECTORY . "/" . $uploadId . "." . $extension;

// TODO: NOT HERE

try {
    $s3 = \Aws\S3\S3Client::factory();
    // TODO: HERE
    $upload = $s3->upload(
        BUCKET,
        TOURNAMENT_DIRECTORY . $uploadId . ".wer",
        $tournament,
        'public-read'
    );

    $uploadUrl = $upload->get('ObjectURL');

    // Success.
    // Update the uploaded tournament.
    WERParser::updateTournament($uploadId, $tournament_id, $tournament);

    echo APIResponse::getSuccess("Upload successful.", false, $tournament_id);
    exit();
} catch (SQLInsertException $e) {
    echo APIResponse::getFailure(-1,
        "There was an error parsing the uploaded tournament: " . $e->getMessage() . " Query: " . $e->getQuery());
    exit();
} catch (Guzzle\Service\Exception\ValidationException $e) {
    echo APIResponse::getFailure(-1,
        "There was an error with the server configuration. If the issue persists, please contact support. " . $e->getMessage());
    exit();
} catch (Aws\Common\Exception\InstanceProfileCredentialsException $e) {
    echo APIResponse::getFailure(-1,
        "There was an error with the server configuration. If the issue persists, please contact support. " . $e->getMessage());
    exit();
} catch (Aws\CloudFront\Exception\Exception $e) {
    echo APIResponse::getFailure(-1,
        "There was a server error completing your upload. If the issue persists, please contact support.");
    exit();
} catch (Exception $e) {
    echo APIResponse::getFailure(-1,
        "There was a server error completing your upload. If the issue persists, please contact support. " . get_class($e));
    exit();
}

/*if (file_put_contents($fullPath, $tournament) === false) {
    // Failure.
    echo APIResponse::getFailure(-1, "There was a server error completing your upload. If the issue persists, please contact support.");
    exit();
}

// Success.
// Update the uploaded tournament.
WERParser::updateTournament($uploadId);


echo APIResponse::getSuccess("Upload successful.", $tournament_id);
exit();*/

/**
 * @param $tournament_id
 * @return int
 * @throws SQLInsertException
 */
function getUploadId($tournament_id) : int {
    // Get the current time.

    // Insert the upload into the database.
    $id = Upload::insert(array(
        "tournament_id" => $tournament_id,
        "timestamp" => time()
    ));

    return $id;
}

/**
 * @param bool $tournament_id
 * @return int
 * @throws SQLInsertException
 */
function getTournamentId($tournament_id=false) : int {
    // If the tournament id is valid, return.
    if ($tournament_id) return $tournament_id;

    // TODO: Authenticate upload privileges.

    // If there's no tournament_id, create a new tournament.
    $data = array("name" => "New Tournament");
    if (isAuthenticated()) $data["user_id"] = getLoggedInUserId();

    return Tournament::insert($data);
}