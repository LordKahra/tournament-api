<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Tournament;
use kahra\src\database\Upload;
use kahra\src\database\User;
use kahra\src\file\WERParser;
use kahra\src\util\Validation;

use kahra\src\view\APIResponse;

$tournament = (array_key_exists("upload_data", $_POST)) ? $_POST["upload_data"] : false;

// Get type specific information.
$tournament_id = ($tournament && isset($_POST["tournament_id"]) && !empty($_POST["tournament_id"])) ? $_POST["tournament_id"] : false;
$extension = ($tournament && !empty($_POST["extension"])) ? $_POST["extension"] : false;

if (!Validation::validateWERDocument($tournament, $extension)) {
    echo APIResponse::getFailure(-1, "Upload is not a valid WERDocument.");
    exit();
}

// TODO: HERE

// Get the tournament metadata, or create it.
$tournament_id = getTournamentId($tournament_id);

echo APIResponse::getFailure(-1, "Debugging.");
exit();

$uploadId = getUploadId($tournament_id);



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
    $upload = $s3->upload(
        BUCKET,
        TOURNAMENT_DIRECTORY . $uploadId . ".wer",
        $tournament,
        'public-read'
    );

    $uploadUrl = $upload->get('ObjectURL');


    // Success.
    // Update the uploaded tournament.
    //TODO: WERParser::updateTournament($uploadId);

    echo APIResponse::getSuccess("Upload successful.", $tournament_id);
    exit();
} catch (\Aws\CloudFront\Exception\Exception $e) {
    echo APIResponse::getFailure(-1, "There was a server error completing your upload. If the issue persists, please contact support.");
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

function getUploadId($tournament_id) : int {
    // Get the current time.

    // Insert the upload into the database.
    $id = Upload::insert(array(
        "tournament_id" => $tournament_id,
        "timestamp" => time()
    ));

    return $id;
}

function getTournamentId($tournament_id=false) : int {
    // If the tournament id is valid, return.
    if ($tournament_id) return $tournament_id;

    // TODO: Authenticate upload privileges.

    // If there's no tournament_id, create a new tournament.
    $data = array("name" => "New Tournament");
    if (User::isAuthenticated()) $data["user_id"] = getLoggedInUserId();

    return Tournament::insert($data);
}