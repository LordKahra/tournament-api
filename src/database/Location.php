<?php

namespace kahra\src\database;

use kahra\src\database\Object;

class Location extends Object {
    const TABLE_NAME        = "locations";
    const ALIAS             = "locations";
    const FIELDS_SELECT = "address_1,address_2,city,state,zip,country";
}