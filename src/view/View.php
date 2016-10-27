<?php

namespace kahra\src\view;

abstract class View implements ViewStaticFunctions {
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 0;
    const CODE_SUCCESS = 200;
    const IS_ALIASED = false;

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

    static function formatResponse($status, $code, $message, $objects) {
        $response = array(
            "status" => $status,
            "code" => $code,
            "message" => $message,
            "objects" => $objects
        );
        return json_encode($response, JSON_PRETTY_PRINT);
    }

    static function formatSuccessResponse($message, $objects) {
        return self::formatResponse(static::STATUS_SUCCESS, static::CODE_SUCCESS, $message, $objects);
    }

    static function formatFailureResponse($code, $message) {
        return self::formatResponse(static::STATUS_FAILURE, $code, $message, 0);
    }
}

interface ViewStaticFunctions {

}

?>