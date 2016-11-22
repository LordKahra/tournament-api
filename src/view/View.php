<?php

namespace kahra\src\view;

abstract class View implements ViewStaticFunctions {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;
    const CODE_SUCCESS = 200;
    //const IS_ALIASED = false;

    /*static function parseResult($result) {
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
    }*/

    static function formatSuccessResponse($message, $objects=false) {
        return APIResponse::get(static::STATUS_SUCCESS, static::CODE_SUCCESS, $message, $objects);
    }

    static function formatFailureResponse($code, $message) {
        return APIResponse::get(static::STATUS_FAILURE, $code, $message, false);
    }
}

interface ViewStaticFunctions {
    //static function parseResult($result);
    static function show($objects);
}

?>