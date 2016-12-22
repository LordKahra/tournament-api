<?php

namespace kahra\src\database;

use kahra\src\database\Object;
use kahra\src\util\Str;

class Store extends Object {
    const FIELD_PARENT_ID   = "user_id";
    const TABLE_NAME        = "stores";
    const NAME_SINGULAR     = "store";
    const ALIAS             = "store";
    const FIELDS_SELECT     = "id,user_id,name,vanity_url,site,location_id,phone";
    const FIELDS_INSERT     = "user_id,name";
    const FIELDS_UPDATE     = "name,vanity_url,site,phone";

    /*static function getChildren() {
        return [ static::getGenericChildJoinClause(Tournament::getTableName(), Tournament::getAlias(), Tournament::getParentIDField()) => new Tournament() ];
    }*/

    /*static function getJoins($includeChildren=true) {
        $alias = self::ALIAS;
        $id_field = self::getIDField();
        $tournament_table = Tournament::TABLE_NAME;
        $tournament_alias = Tournament::ALIAS;
        $tournament_parent_id = Tournament::FIELD_PARENT_ID;
    }*/

    /*static function getChildren() {
        return array(
            array(
                "type" => "left",
                "alias" => Tournament::ALIAS,
                "select" => (Tournament::getSelectClause()),
                "clause" => self::getGenericChildJoinClause(Tournament::TABLE_NAME, Tournament::ALIAS, Tournament::FIELD_PARENT_ID),
                "class" => new Tournament()
            )
        );
    }*/

    /*static function getSubqueries() : array {
        $self_alias = static::ALIAS;
        $table = Location::TABLE_NAME;
        $alias = Location::ALIAS;
        $aliased_fields = implode(",", Str::getReferences(
            $alias,
            array("address_1","address_2","city","state","zip","country","latitude","longitude","type")));


        return array(
            array(
                "fields" => array("address_1","address_2","city","state","zip","country","latitude","longitude","type"),
                "table" => $table,
                "alias" => $alias,
                "query" =>
                "(" .
                    "SELECT $aliased_fields " .
                    "FROM $table $alias " .
                    "WHERE $alias.id = $self_alias.location_id" .
                ")"
            )
        );
    }*/

    static function getMonogamousJoins() : array {
        $location_alias = Location::ALIAS;
        $location_table = Location::TABLE_NAME;
        $location_fields = explode(",", Location::FIELDS_SELECT);
        $parent_alias = static::ALIAS;

        return array($location_alias => array(
            "fields" => $location_fields,
            "table" => $location_table,
            "alias" => $location_alias,
            "select" => Str::createSelectClause($location_alias, $location_fields),
            "query" => "LEFT JOIN $location_table $location_alias ON $parent_alias.location_id = $location_alias.id"
        ));
    }

    static function getByUserId($user_id) {
        return self::getByField("user_id", $user_id);
    }

    static function create($user_id, $name, $vanity_url=false, $site=false) {
        // TODO: validation.
        $object = array(
            "user_id" => $user_id,
            "name" => $name
        );
        if ($vanity_url) $object["vanity_url"] = $vanity_url;
        if ($site) $object["site"] = $site;

        $result = static::insert($object);
        // TODO: Location data is saved later.

        return $result;
    }
}