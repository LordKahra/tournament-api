<?php

namespace kahra\src\database;

use kahra\src\util\Str;

use kahra\src\database\User;
use kahra\src\database\Store;
use kahra\src\database\Round;

class Tournament extends Object {
    const TABLE_NAME        = "tournaments";
    const NAME_SINGULAR     = "tournament";
    const TAG_NAME          = "event";
    const ALIAS             = "tournament";
    const FIELDS_SELECT     = "id,store_id,name,last_updated";
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
        return self::get("user_id = " . $user_id);
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