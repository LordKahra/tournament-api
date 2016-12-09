<?php

namespace kahra\src\view;

class APIResponse {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;

    const CODE_SUCCESS = 200;
    const CODE_EMPTY_SET = -1;
    const CODE_UNAUTHORIZED = -1;
    const CODE_MISSING_DATA = -1;

    // RAW ////

    static function get($status, $code, $message, $objects = false) {
        header('Content-Type: application/json');

        $response = array(
            "status" => $status,
            "code" => $code,
            "message" => $message
        );

        if ($objects) $response["objects"] = $objects;

        return json_encode($response, JSON_PRETTY_PRINT);
    }

    // SUCCESS OR FAILURE ////

    static function getSuccess($message, $objects=false) {
        return APIResponse::get(static::STATUS_SUCCESS, static::CODE_SUCCESS, $message, $objects);
    }

    static function getFailure($code, $message) {
        return APIResponse::get(static::STATUS_FAILURE, $code, $message, false);
    }

    // SPECIFIC ////

    static function getEmptyDataResponse($message="No records found.") {
        return static::getSuccess($message);
    }

    static function getUnauthorizedResponse($message="You are not logged in.") {
        return static::getFailure(static::CODE_UNAUTHORIZED, $message);
    }

    static function getMissingRequestDataResponse($message="You must submit all necessary data.") {
        return static::getFailure(static::CODE_MISSING_DATA, $message);
    }
}