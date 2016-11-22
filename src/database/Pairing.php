<?php

namespace kahra\src\database;

use kahra\src\database\Object;
use kahra\src\database\Player;
use kahra\src\database\User;
use kahra\src\database\Tournament;

use kahra\src\util\Debug;

//$player = Player::getAlias();

/**
 * Class Pairing
 * @package kahra\src\database
 *
 * @deprecated
 */
class Pairing extends Object {
    const TABLE_NAME        = "pairings";
    const NAME_SINGULAR     = "pairing";
    const TAG_NAME          = "seat";
    const ALIAS             = "pairing";
    const FIELDS_SELECT     = "id,tournament_id,player_id,points,round,table_id,is_bye";
    const FIELDS_INSERT     = "tournament_id,player_id,points,round,table_id,is_bye";
    const DEFAULT_SORT      = "table_id, last_name";
    const FIELD_PARENT_ID   = "tournament_id";
    const FIELD_MANY_PARENT_ID   = "player_id";

    static function getAttributeTable() {
        return array(
            "player" => "player_id"
        );
    }

    static function getJoins($includeChildren=true) {
        return array(
            array(
                "type" => "left",
                "alias" => Player::ALIAS,
                "select" => (Player::getSelectClause()),
                "clause" => "players " . Player::ALIAS . " ON " . self::getAlias() . ".player_id = " . Player::ALIAS . ".dci",
                "class" => "Player"
            ),
            array(
                "type" => "left",
                "alias" => Tournament::ALIAS,
                "select" => (Tournament::getSelectClause()),
                "clause" => "tournaments " . Tournament::ALIAS . " ON " . self::getAlias() . ".tournament_id = " . Tournament::ALIAS . ".id",
                "class" => "Tournament"
            ),
            array(
                "type" => "left",
                "alias" => User::ALIAS,
                "select" => (User::getSelectClause()),
                "clause" => "users " . User::ALIAS . " ON " . Player::ALIAS . ".dci = " . User::ALIAS . ".dci",
                "class" => "User"
            )
        );
    }

    // CUSTOM QUERIES /////

    // p.id, p.player_id, p.round, p.table_id, p.points,
    // t.name as tournament_name,
    // u.first_name, u.last_name
    static function getByTournament($tournament, $round=false) {
        // Prevariables:
        $where = self::getAlias() . ".tournament_id = $tournament AND " . self::getAlias() . ".round = " .
            ($round ? $round :
            "(
        SELECT MAX(p3.round)
                FROM pairings p3
                WHERE p3.tournament_id = $tournament
            )");
        $joins = array(
            array(
                "type" => "left",
                "alias" => "sum",
                "select" => "sum.amount",
                "clause" => "(
                    SELECT p2.id, p2.player_id, SUM(p2.points) AS amount
                    FROM pairings p2
                    WHERE tournament_id = $tournament
                    GROUP BY p2.player_id
                ) AS sum ON " . self::getAlias() . ".player_id = sum.player_id",
                "class" => false
            )
        );
        $joins = array_merge($joins);
        $order = false;

        return self::get($where, $joins);
    }

    static function getChildren() {
        return array(
            array(
                "type" => "left",
                "alias" => Player::ALIAS,
                "select" => (Player::getSelectClause()),
                "clause" => self::getGenericParentJoinClause(Player::TABLE_NAME, Player::ALIAS, static::FIELD_MANY_PARENT_ID, Player::FIELD_ID),
                "class" => new Player()
            )
        );
    }

    public static function getMatches($pairings) {
        $matches = array();
        foreach ($pairings as $pairing) {
            $match_id = $pairing["pairing_table_id"];
            if (!array_key_exists($match_id, $matches)) $matches[$match_id] = array();
            $matches[$match_id][] = $pairing;
        }
        return $matches;
    }

    public static function parseObjects($records, $class) {
        $objects = parent::parseObjects($records, $class);
        $matches = array();
        foreach ($objects["pairings"] as $pairing) {
            $match_id = $pairing["pairing_table_id"];
            if (!array_key_exists($match_id, $matches)) $matches[$match_id] = array();
            $matches[$match_id][] = $pairing;
        }
        $objects["matches"] = $matches;
    }
}