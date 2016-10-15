<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Round extends Object {
    //const FIELD_ID          = "id";
    const FIELD_PARENT_ID   = "tournament_id";
    const TABLE_NAME        = "rounds";
    const NAME_SINGULAR     = "round";
    const ALIAS             = "round";
    const FIELDS_SELECT     = "id,tournament_id,index";
    const FIELDS_INSERT     = "tournament_id,index";

    static function getChildren() {
        // TODO: Implement.
    }

    // CUSTOM QUERIES ////

    static function getByTournamentId($id) {
        return static::getByField("tournament_id", $id);
    }
}