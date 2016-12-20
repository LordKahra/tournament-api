<?php

namespace kahra\src\database;

use kahra\src\database\Object;

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