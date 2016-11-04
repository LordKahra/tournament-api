<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Event extends Object {
    const FIELD_PARENT_ID   = "store_id";
    const TABLE_NAME        = "events";
    const NAME_SINGULAR     = "event";
    const ALIAS             = "event";
    const FIELDS_SELECT     = "id,store_id,name,vanity_url";
    const FIELDS_INSERT     = "store_id,name";

    static function getByStoreId($store_id) {
        return self::getByField("store_id", $store_id);
    }

}