<?php

// FILE UPLOADS MUST BE SET TO ON IN php.ini!!!

require_once SITE_ROOT . '/src/file/WERParser.php';

use kahra\src\database\Tournament;
use kahra\src\file\WERParser;
const SIZE_LIMIT = 8388608; // 8MB

const UPLOAD_TOURNAMENT = "tournament";
const UPLOAD_PROFILE = "profile";
const UPLOAD_STORE_BANNER = "store";

const MESSAGE_SUCCESS = "success";

function uploadTournament($fileData, $fileExtension, $tournamentId=false) {
    // Determine if the user is logged in.
    $isLoggedIn = isLoggedIn();

    // Validate the data.
    if ($error = hasUploadError($fileData, $fileExtension, UPLOAD_TOURNAMENT)) return $error;

    // Place according to type. If the folder doesn't exist, throw an error and notify admins.
    $fileDir = UPLOAD_DIRECTORY . "/" . UPLOAD_TOURNAMENT;
    if ($error = isMissingUploadDirectory($fileDir)) return $error;

    // Name according to tournament id, or for anonymous users, timestamp.
    $fileId = getTournamentId($isLoggedIn, $tournamentId);

    if (!$fileId) {
        $error = "There was a server error creating the tournament. If the issue persists, please contact support.";
        return $error;
    }

    $fileName = $fileId . "." . $fileExtension;

    // Upload the file.
    $result = uploadFile($fileData, $fileDir, $fileName, $fileId);

    // Recheck all tournaments.
    WERParser::updateTournaments();

    // Return the result.
    return $result;
}

function getTournamentId($isLoggedIn, $tournamentId=false) {
    $fileID = "";

    // If there's no tournamentId, create a new tournament.
    $id = $tournamentId ? $tournamentId : Tournament::insert(array("name" => "New Tournament"));
    // TODO: Throw here if error.

    if ($isLoggedIn) {
        $fileID = $id;
    } else {
        // TODO: Better validation against same-time uploaders.
        $fileID = $id;
    }

    return $fileID;
}

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
    header("Location: " . $site);
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
    $header .= "?updated=1";
    //echo $header;
    header("Location: " . $header);
    exit();
}

?>