<?php

namespace kahra\src\view;

abstract class View implements ViewStaticFunctions {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;
    const CODE_SUCCESS = 200;
    //const IS_ALIASED = false;

    static function formatSuccessResponse($message, $objects=false) {
        return APIResponse::get(static::STATUS_SUCCESS, static::CODE_SUCCESS, $message, $objects);
    }

    static function formatFailureResponse($code, $message) {
        return APIResponse::get(static::STATUS_FAILURE, $code, $message, false);
    }

    static function handleRequest() {
        if (!array_key_exists("action", $_GET) || !$_GET["action"]) {
            echo APIResponse::getFailure(-1, "No action specified.");
            exit();
        }
        else if (!static::handleAction($_GET["action"])) {
            echo APIResponse::getFailure(-1, "Invalid action specified.");
            exit();
        }
        exit();
    }
}

interface ViewStaticFunctions {
    //static function parseResult($result);
    static function show($objects);
    static function handleAction($action) : bool;
}

?>