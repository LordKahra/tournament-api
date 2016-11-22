<?php

namespace kahra\src\util;

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
}