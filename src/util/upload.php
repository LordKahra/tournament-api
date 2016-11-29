<?php

// FILE UPLOADS MUST BE SET TO ON IN php.ini!!!

require_once SITE_ROOT . '/src/file/WERParser.php';
require(SITE_ROOT . '/vendor/autoload.php');

use Aws\CloudFront\Exception\Exception;
use kahra\src\database\Tournament;
use kahra\src\database\Upload;
use kahra\src\exception\UploadFailureException;
use kahra\src\file\WERParser;
use kahra\src\util\Email;
use kahra\src\util\Debug;

const SIZE_LIMIT = 8388608; // 8MB

const UPLOAD_TOURNAMENT = "tournament";
const UPLOAD_PROFILE = "profile";
const UPLOAD_STORE_BANNER = "store";

const MESSAGE_SUCCESS = "success";

/**
    Uploads a tournament, creating an upload_id, and a tournament_id if necessary.
 */
function uploadTournament($fileData, $fileExtension, $tournamentId=false) {
    // Determine if the user is logged in.
    $isLoggedIn = isLoggedIn();

    // Validate the data.
    if ($error = hasUploadError($fileData, $fileExtension, UPLOAD_TOURNAMENT)) return $error;

    // Get the tournament ID, or create a new one.
    $tournamentId = getTournamentId($tournamentId);
    $uploadId = getUploadId($tournamentId);

    if (!$tournamentId || !$uploadId) {
        throw new UploadFailureException("Error encountered while generating tournament metadata.");
    }

    try {
        $s3 = \Aws\S3\S3Client::factory();
        $upload = $s3->upload(
            BUCKET_TOURNAMENT,
            $uploadId . ".wer",
            $fileData,
            'public-read'
        );
        /*$upload = $s3->upload(
            BUCKET_TOURNAMENT,
            $_FILES['userfile']['name'],
            fopen($_FILES['userfile']['tmp_name'], 'rb'),
            'public-read'
        );*/
        $uploadUrl = $upload->get('ObjectURL');

        // TODO: Skip updating.
        //WERParser::updateTournament($uploadId);

        // Success.
        return true;
    } catch (Exception $e) {
        throw $e;
    }

    // Place according to type. If the folder doesn't exist, throw an error and notify admins.
    //$fileDir = UPLOAD_DIRECTORY . "/" . UPLOAD_TOURNAMENT;
    $fileDir = TOURNAMENT_UPLOAD_DIRECTORY;
    if ($error = isMissingUploadDirectory($fileDir)) return $error;

    // Name according to tournament id, or for anonymous users, timestamp.

    $tournamentId = getTournamentId($tournamentId);
    $uploadId = getUploadId($tournamentId);

    if (!$tournamentId || !$uploadId) {
        throw new UploadFailureException("Error encountered while saving the tournament file.");
    }

    $fileName = $uploadId . "." . $fileExtension;

    // Upload the file.
    $result = uploadFile($fileData, $fileDir, $fileName, $uploadId);

    // Recheck all tournaments.
    //WERParser::updateTournaments();
    // Update the uploaded tournament.
    WERParser::updateTournament($uploadId);

    // Return the result.
    return $result;
}

/*function getUploadId($tournament_id) {
    // Get the current time.

    // Insert the upload into the database.
    $uploadId = Upload::insert(array(
        "tournament_id" => $tournament_id,
        "timestamp" => time()
    ));

    return $uploadId;
}

function getTournamentId($tournamentId=false) {
    // If there's no tournamentId, create a new tournament.
    $id = $tournamentId ? $tournamentId : Tournament::insert(array("name" => "New Tournament"));

    return $id;
}*/

function hasUploadError($fileData, $fileExtension, $type) {
    // File must not be empty.
    if(empty($fileData)) {
        return onUploadError("File was empty.");
    }

    // File must meet size limits.
    if (strlen($fileData) > SIZE_LIMIT) {
        return onUploadError("File cannot be more than 8 MB.");
    }

    // Validate the type.
    switch($type) {
        case UPLOAD_TOURNAMENT:
            if (!isWER($fileExtension)) return onUploadError("Tournaments must be .wer files.");
            break;
        case UPLOAD_PROFILE:
        case UPLOAD_STORE_BANNER:
            // File must be an image.
            if (!isImage($fileExtension)) return onUploadError("Profile pictures must be images.");
            break;
        default:
            // Throw an error if the type is invalid.
            return onUploadError("There was an error with your upload (invalid type). Please try again later.");
            break;
    }

    return false;
}

function upload($fileData, $fileExtension, $type) {
    // User must be logged in for profile and store uploads.
    $isLoggedIn = isLoggedIn();
    if (!$isLoggedIn) {
        if ($type != UPLOAD_TOURNAMENT) onFailedAuthentication();
    }

    // Get file information.
    //$fileData = $_FILES['uploadfile']["tmp_name"];
    //$fileInfo = pathinfo($_FILES['uploadfile']["name"]);
    //$fileExtension = $fileInfo['extension'];

    if ($error = hasUploadError($fileData, $fileExtension, $type)) return $error;

    // File must not be empty.
    /*if(empty($fileData)) {
        return onUploadError("File was empty.");
    }*/

    // File must meet size limits.
    /*if (strlen($fileData) > SIZE_LIMIT) {
        return onUploadError("File cannot be more than 8 MB.");
    }*/

    // Validate the type.
    /*switch($type) {
        case UPLOAD_TOURNAMENT:
            if (!isWER($fileExtension)) return onUploadError("Tournaments must be .wer files.", "settings");
            break;
        case UPLOAD_PROFILE:
        case UPLOAD_STORE_BANNER:
            // File must be an image.
            if (!isImage($fileExtension)) return onUploadError("Profile pictures must be images.", "settings");
            break;
        default:
            // Throw an error if the type is invalid.
            return onUploadError("There was an error with your upload (invalid type). Please try again later.");
            break;
    }*/

    // Place according to type. If the folder doesn't exist, throw an error and notify admins.
    $fileDir = UPLOAD_DIRECTORY . "/" . $type;
    if ($error = isMissingUploadDirectory($fileDir)) return $error;

    // Name according to user id, or for anonymous users, timestamp.
    $fileName = ($isLoggedIn ? $_SESSION['id'] : time()) . "." . $fileExtension;

    // Upload the file.
    return uploadFile($fileData, $fileDir, $fileName, $_SESSION['id']);

    // Get the final location.
    //$fullPath = $fileDir . "/" . $fileName;

    // Attempt to upload the file.
    /*try {
        move_uploaded_file($fileData, $fullPath);
        return "success";
    } catch (exception $e){
        return onUploadError("There was a server error completing your upload. If the issue persists, please contact support.");
    }*/
}

function uploadFile($fileData, $fileDir, $fileName, $fileId) {
    // Get the final location.
    $fullPath = $fileDir . "/" . $fileName;

    // Attempt to upload the file.
    try {
        move_uploaded_file($fileData, $fullPath);
        return array(
            "status" => true,
            "message" => "Successful upload.",
            "file_id" => $fileId
        );
    } catch (exception $e){
        // TODO: Better logging.
        return array(
            "status" => false,
            "message" => onUploadError("There was a server error completing your upload. If the issue persists, please contact support.")
        );
    }
}

function isImage($fileExtension) {
    return !(
        $fileExtension != "jpg" &&
        $fileExtension != "jpeg" &&
        $fileExtension != "png" &&
        $fileExtension != "gif"
    );
}

function isWER($fileExtension) {
    return !(
        $fileExtension != "wer"
    );
}

function isMissingUploadDirectory($fileDir) {
    if(!file_exists($fileDir)){
        Email::emailAdmin("Upload Directory Missing", "The following upload directory was missing from server " . SITE_HOST . ":\r\n\r\n" . $fileDir);
        return onUploadError("There was a server error uploading your file. If the issue persists, please contact support.");
    }
    return false;
}

function getName($type, $fileExtension, $isLoggedIn) {
    return ($isLoggedIn ? $_SESSION['id'] : time()) . "." . $fileExtension;
}

function onUploadError($message) {
    /*$site = SITE_HOST . "" .
        ($message ? "?message=" . urlencode($message) : "") .
        ($redirect ? ($message ? "&" : "?") . "redirect=" . urlencode($redirect) : "");
    header("Location: " . SITE_HOST . "/" . $site);
    exit();*/
    return $message;
}

function onSuccessfulUpload($type, $id=false) {
    $header = "";
    switch($type) {
        case UPLOAD_PROFILE:
            $header = "user";
            break;
        case UPLOAD_TOURNAMENT:
            $header = "tournament" . ($id ? "/" . $id : "");
            break;
        case UPLOAD_STORE_BANNER:
            $header = "store" . ($id ? "/" . $id : "");
            break;
    }
    // TODO: Header testing.
    $header .= "?updated=1";
    header("Location: " . SITE_HOST . "/" . $header);
    exit();
}

?>