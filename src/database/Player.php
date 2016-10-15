<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Player extends Object {
    const FIELD_ID          = "dci";
    const TABLE_NAME        = "players";
    const NAME_SINGULAR     = "player";
    const TAG_NAME          = "person";
    const ALIAS             = "player"; // TODO: Changed from "u"
    const FIELDS_SELECT     = "dci,first_name,last_name,middle_initial,country,email";
    const FIELDS_INSERT     = "dci,first_name,last_name,middle_initial,country";

    static function getAttributeTable() {
        return array(
            "id" => "dci",
            "first" => "first_name",
            "last" => "last_name",
            "middle" => "middle_initial",
            "country" => "country"
        );
    }

    /*function subscribe($dci, $email) {
        $query =
            "INSERT INTO " . self::getTableName() .
                " ( dci, email, first_name, last_name, country ) " .
            "VALUES " .
                '("' . $dci . '","' . $email . '","Unknown","Unknown","XX") ' .
            "ON DUPLICATE KEY UPDATE " . "dci=VALUES(dci), email=VALUES(email)";
        /*$player = array("email" => $email);
        $result = $this->update($player, "dci = '" . $dci . "'");
        if(!$result) {
            $player["dci"] = $dci;
            $player["first_name"] = "Unknown";
            $player["last_name"] = "Unknown";
            $player["country"] = "XX";
            $result = $this->insert($player);
        }

        //echo $query;
        global $mysqli;
        $result = $mysqli->query($query);
        return $result;
    }*/
}