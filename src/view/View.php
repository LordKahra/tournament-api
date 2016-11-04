<?php

namespace kahra\src\view;

abstract class View implements ViewStaticFunctions {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;
    const CODE_SUCCESS = 200;
    //const IS_ALIASED = false;

    static function parseResult($result) {
        if ($result) {
            $objects = array();

            //while ($tournament = mysqli_fetch_assoc($result)) {
            foreach ($result as $tournament) {
                //if (!$objects) $objects = array();
                $objects[] = $tournament;
                $title = $tournament["tournament_name"];
                $og_url = "tournament/" . $_GET["tournament_id"];
            }
            return $objects;
        } else {
            //$errors[] = array("priority" => "low", "message" => "Tournament not found.");
            return false;
        }
    }

    static function formatSuccessResponse($message, $objects=false) {
        return APIResponse::get(static::STATUS_SUCCESS, static::CODE_SUCCESS, $message, $objects);
    }

    static function formatFailureResponse($code, $message) {
        return APIResponse::get(static::STATUS_FAILURE, $code, $message, false);
    }
}

class APIResponse {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;

    const CODE_SUCCESS = 200;
    const CODE_EMPTY_SET = -1;
    const CODE_UNAUTHORIZED = -1;
    const CODE_MISSING_DATA = -1;

    // RAW ////

    static function get($status, $code, $message, $objects = false) {
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
        return static::getSuccess(static::CODE_EMPTY_SET, $message);
    }

    static function getUnauthorizedResponse($message="You are not logged in.") {
        return static::getFailure(static::CODE_UNAUTHORIZED, $message);
    }

    static function getMissingRequestDataResponse($message="You must submit all necessary data.") {
        return static::getFailure(static::CODE_MISSING_DATA, $message);
    }
}

interface ViewStaticFunctions {

}

?>