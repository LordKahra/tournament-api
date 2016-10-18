<?php

namespace kahra\src\database;

class Upload extends Object {
    const TABLE_NAME        = "uploads";
    const NAME_SINGULAR     = "upload";
    //const TAG_NAME          = "event";
    const ALIAS             = "upload";
    const FIELDS_SELECT     = "id,tournament_id,timestamp";
    const FIELDS_INSERT     = "tournament_id,timestamp";
    const FIELD_PARENT_ID   = "tournament_id";

}
