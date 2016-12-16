<?php

namespace kahra\test\database;

use kahra\test\database\UserTest;
use kahra\test\database\StoreTest;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');
require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/test/database/ObjectTest.php');
require_once (SITE_ROOT . '/test/database/StoreTest.php');
require_once (SITE_ROOT . '/test/database/UserTest.php');

class TestData {

    public static function resetData() {
        static::runQuery(StoreTest::QUERY_DELETE);
        static::runQuery(UserTest::QUERY_DELETE);
        static::runQuery(UserTest::getInsertQuery());
        static::runQuery(StoreTest::QUERY_INSERT);
    }

    private static function runQuery($query) {
        global $mysqli;
        $result = $mysqli->query($query);
    }



}