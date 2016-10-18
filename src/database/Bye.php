<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Bye extends Object {
    //const FIELD_ID          = "id";
    const FIELD_PARENT_ID   = "round_id";
    const TABLE_NAME        = "byes";
    const NAME_SINGULAR     = "bye";
    const ALIAS             = "bye";
    const FIELDS_SELECT     = "id,round_id,player_id";
    const FIELDS_INSERT     = "round_id,player_id";

    // CUSTOM QUERIES ////

    static function getByRoundId($id) {
        return static::getByField("round_id", $id);
    }
    
    static function getByPlayerId($id) {
        return static::getByField("player_id", $id);
    }
}