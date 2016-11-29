<?php

namespace kahra\src\database;

use kahra\src\exception\SQLInsertException;
use kahra\src\file\WERDocument;
use kahra\src\util\Debug;
use kahra\src\util\Str;
use kahra\src\util\ArraySet;

use kahra\src\database\User;
use kahra\src\database\Store;
use kahra\src\database\Round;

class Tournament extends Object {
    const TABLE_NAME        = "tournaments";
    const NAME_SINGULAR     = "tournament";
    const TAG_NAME          = "event";
    const ALIAS             = "tournament";
    const FIELDS_SELECT     = "id,store_id,name,last_updated,type_id";
    const FIELDS_INSERT     = "name";
    const FIELD_PARENT_ID   = "store_id";

    static function getAttributeTable() {
        return array(
            "title" => "name"
        );
    }

    static function getSubqueries() {
        $user_table = User::getTableName();
        $user_alias = User::getAlias();
        $user_fields = array("id");
        $aliased_fields = implode(",", Str::getReferences($user_alias, $user_fields));

        /*
        $store_table = Store::getTableName();
        $store_alias = Store::getAlias();
        $store_fields = array("id", "name");
        $aliased_store_fields = implode(", " . $store_alias . ".", $store_fields);
        $aliased_store_fields = empty($aliased_store_fields) ? "" : $store_alias . "." . $aliased_store_fields;
        */

        return array(
            "user" => array(
                "fields" => $user_fields,
                "table" => $user_table,
                "alias" => $user_alias,
                "query" => "(\r\n" .
                    "SELECT " . $aliased_fields . " " .
                    "FROM " . $user_table . " " . $user_alias . " " .
                    "WHERE " . $user_alias . ".id = " .
                        "(SELECT user_id FROM stores store WHERE store.id = " . self::getAlias() . ".store_id)" .
                ") as " . $user_alias . "_id"
            )
        );
    }

    static function getMonogamousJoins() {
        $store_table = Store::getTableName();
        $store_alias = Store::getAlias();
        $store_fields = array("id", "name");
        $store_select_clause = Str::createSelectClause($store_alias, $store_fields);

        return array(
            $store_alias => array(
                "fields" => $store_fields,
                "table" => $store_table,
                "alias" => $store_alias,
                "type" => "LEFT",
                "select" => $store_select_clause,
                "query" =>
                    "LEFT JOIN " .
                        $store_table . " AS " . $store_alias . " " .
                    "ON " .
                        $store_alias . ".id = " . self::getAlias() . ".store_id ",
            )
        );
    }

    // CUSTOM QUERIES

    static function getByUserId($user_id) {
        return static::get("user_id = " . $user_id);
    }

    /**
     * Reset all pairing data for a specific tournament.
     *
     * @param int $id The id of the tournament to reset.
     */
    static function resetData($id) {
        // Delete the rounds.
        Debug::log("Tournament.deleteData()", "Entered.");
        Round::deleteByTournamentId($id);
        Debug::log("Tournament.deleteData()", "Done.");
    }

    /**
     * Generates the data for a tournament.
     *
     * @param WERDocument $document A WERDocument upload containing all tournament data.
     * @param int $tournament_id The tournament id.
     *
     * @throws SQLInsertException if there is an issue inserting any of the data.
     *
     * @return void
     */
    static function generateData(WERDocument $document, $tournament_id) {
        // Get the relevant data.
        $rounds = $document->rounds;
        $matches = $document->matches;
        $byes = $document->byes;
        $seats = $document->seats;
        $roundPrefix = Round::getPrefix();
        $matchPrefix = Match::getPrefix();

        // If there are no rounds, return.
        if (count($rounds) < 1) return;

        try {

            //echo "<h1>TOURN ID WITHIN GENERATEDATA: $tournament_id</h1>";

            //var_dump($rounds);

            // Set the tournament id for the rounds.
            ArraySet::setAll("tournament_id", $tournament_id, $rounds);

            //var_dump($rounds);

            // Insert the rounds.
            Round::bulkInsert($rounds);

            // Fetch the rounds.
            $roundRecords = Round::getByTournamentId($tournament_id);

            if (!$roundRecords) {
                // There MUST be at least one round to reach this point.
                // If there are no records, something went wrong.
                // Clean up and throw.
                Tournament::resetData($tournament_id);
            }

            // Map the round ids.
            $roundIdMap = array();
            foreach ($roundRecords as $round_id => $round) {
                $roundIdMap[$round[$roundPrefix . "r_index"]] = $round_id;
            }

            // Add the correct round ids to the matches and byes, and unset the round index.
            $matchData = array();
            $byeData = array();
            foreach ($matches as $roundIndex => $matchSet) {
                foreach ($matchSet as $match) {
                    $match["round_id"] = $roundIdMap[$roundIndex];
                    unset($match["round_index"]);
                    $matchData[] = $match;
                    // TODO: I don't think the unset is actually necessary.
                }
            }

            foreach ($byes as $roundIndex => $byeSet) {
                foreach ($byeSet as $bye) {
                    $bye["round_id"] = $roundIdMap[$roundIndex];
                    unset($bye["round_index"]);
                    $byeData[] = $bye;
                }
            }

            // Insert the matches and byes.
            Match::bulkInsert($matchData);
            Bye::bulkInsert($byeData);

            // Fetch the matches.
            $matchRecords = Match::getByTournamentId($tournament_id);

            //var_dump($matchRecords);

            // Map the round ids.
            $matchIdMap = array();
            foreach ($matchRecords as $match_id => $match)
                $matchIdMap[$match[$matchPrefix . "table_id"]] = $match_id;

            // Add the correct match ids to the seats, and add the seats to a data set.
            $seatData = array();
            foreach ($seats as $table => $seatSet) {
                foreach ($seatSet as $seat) {
                    $seat["match_id"] = $matchIdMap[$table];
                    $seatData[] = $seat;
                }
            }

            // Insert the seats.
            Seat::bulkInsert($seatData);

            // Done!
        } catch (SQLInsertException $e) {
            Tournament::resetData($tournament_id);
            throw $e;
        }
    }

    /*static function getChildren() {
        return array(
            array(
                "type" => "left",
                "alias" => Round::ALIAS,
                "select" => (Round::getSelectClause()),
                "clause" => static::getGenericChildJoinClause(Round::TABLE_NAME, Round::ALIAS, Round::FIELD_PARENT_ID),
                "class" => new Round()
            )
        );
    }

    /*static function getChildren() {
        return array(
            array(
                "type" => "left",
                "alias" => Pairing::ALIAS,
                "select" => (Pairing::getSelectClause()),
                "clause" => self::getGenericChildJoinClause(Pairing::TABLE_NAME, Pairing::ALIAS, Pairing::FIELD_PARENT_ID),
                "class" => new Pairing()
            )
        );
    }*/

    /*function deletePairings($tournamentID) {

    }*/

}