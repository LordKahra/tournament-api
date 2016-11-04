<?php

namespace kahra\src\util;

class Validation {
    static function validateEmail($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }
    static function validatePassword($password) {
        return $password;
        // TODO: Safety stuff.
        //return filter_var(trim($password), FILTER_VALIDATE_EMAIL);
    }
    static function validateDci($dci) {
        return filter_var(trim($dci), FILTER_VALIDATE_INT);
    }

    // static function sanitizeOutput()
}