<?php

namespace kahra\src\database;

class Token extends Object {
    const FIELD_ID          = "id";
    const TABLE_NAME        = "tokens";
    const NAME_SINGULAR     = "token";
    const ALIAS             = "token";
    const FIELDS_SELECT     = "id,user_id,token,expiration";
    const FIELDS_INSERT     = "user_id,token,expiration";

    static function create($user_id) {
        $token = static::generate($user_id);

        try {
            $result = static::insert(array(
                "user_id" => "\"$user_id\"",
                "token" => "\"$token\"",
                "expiration" => "DATE_ADD(NOW(), INTERVAL 1 DAY)"
            ), true);
        } catch (\Exception $e) {
            $token = $e->getMessage();
        }

        return $token;
    }

    static function generate($user_id) {
        $generatedToken = false;
        do {
            $randomNumber = rand(1, 9999);
            $generatedToken = "token_$user_id" . $randomNumber; // TODO LOL
            if (static::getByField("token", $generatedToken)) $generatedToken = false;

        } while (!$generatedToken);

        /*
        // Check if the key exists.
        $exists = static::getByField("token", $generatedToken);
        while ($exists) {
            $generatedToken = static::generate($user_id . "_");
        }*/

        return $generatedToken;
    }
}

