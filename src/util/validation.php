<?php

namespace kahra\src\util;

use DOMDocument;

abstract class Validation {
    static function validateEmail($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    static function validatePassword($password) {
        return $password;
        // TODO: Safety stuff.
        // Could probably use FILTER_VALIDATE_EMAIL, since that's basically what's allowed
        // anyways.
        // Also, don't trim. If they put a space in their password, we want to throw.

        //return filter_var(trim($password), FILTER_VALIDATE_EMAIL);
    }

    static function validateDci($dci) {
        return filter_var(trim($dci), FILTER_VALIDATE_INT);
    }

    // static function sanitizeOutput()

    static function validateWERDocument($data, $extension) {
        //$document = new DOMDocument();
        return (static::isWERExtension($extension) && (new DOMDocument())->loadXML($data));
    }

    private static function isWERExtension($fileExtension) {
        return !(
            $fileExtension != "wer"
        );
    }

    private static function isImageExtension($fileExtension) {
        return !(
            $fileExtension != "jpg" &&
            $fileExtension != "jpeg" &&
            $fileExtension != "png" &&
            $fileExtension != "gif"
        );
    }

    static function validateUploadDirectory($fileDir) {
        if(!file_exists($fileDir)){
            Email::emailAdmin("Upload Directory Missing", "The following upload directory was missing from server " . SITE_HOST . ":\r\n\r\n" . $fileDir);
            return false;
            //return onUploadError("There was a server error uploading your file. If the issue persists, please contact support.");
        }
        return true;
    }
}