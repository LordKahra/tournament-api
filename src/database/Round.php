<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Round extends Object {
    //const FIELD_ID          = "id";
    const FIELD_PARENT_ID   = "tournament_id";
    const TABLE_NAME        = "rounds";
    const NAME_SINGULAR     = "round";
    const ALIAS             = "round";
    const FIELDS_SELECT     = "id,tournament_id,r_index";
    const FIELDS_INSERT     = "tournament_id,r_index";

    static function getChildren() {
        // TODO: Implement.
    }

    // CUSTOM QUERIES ////

    static function getByTournamentId($id) {
        return static::getByField("tournament_id", $id);
    }

    static function deleteByTournamentId($id) {
        // Delete matches and byes first.
        Match::deleteByTournamentId($id);
        Bye::deleteByTournamentId($id);

        return static::deleteByField("tournament_id", $id);
    }
}