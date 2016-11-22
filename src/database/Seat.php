<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Seat extends Object {
    //const FIELD_ID          = "id";
    const FIELD_PARENT_ID   = "match_id";
    const TABLE_NAME        = "seats";
    const NAME_SINGULAR     = "seat";
    const ALIAS             = "seat";
    const FIELDS_SELECT     = "id,match_id,player_id,wins";
    const FIELDS_INSERT     = "match_id,player_id,wins";

    // CUSTOM QUERIES ////

    static function getByMatchId($id) {
        return static::getByField("match_id", $id);
    }

    static function getByPlayerId($player_id) {
        return static::getByField("player_id", $player_id);
    }

    static function getByTournamentId($id) {
        static::get(
            "match_id IN (
                SELECT id FROM matches WHERE round_id IN (
                    SELECT id
                    FROM rounds
                    WHERE tournament_id = '$id'
                )
            )"
        );
    }

    static function deleteByTournamentId($id) {
        static::delete(
            "match_id IN (
                SELECT id FROM matches WHERE round_id IN (
                    SELECT id
                    FROM rounds
                    WHERE tournament_id = '$id'
                )
            )"
        );
    }
}