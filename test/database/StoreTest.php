<?php

namespace kahra\test\database;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/test/database/TestData.php');

use kahra\src\database\Store;

class StoreTest extends ObjectCase {
    const QUERY_INSERT = "INSERT INTO stores (id, user_id, name, vanity_url, country)
        VALUES
            (10001, 1001, 'test1001name', 'test1001url', 'US'),
            (10002, 1002, 'test1002name', 'test1002url', 'US'),
            (10003, 1003, 'test1003name', 'test1003url', 'US'),
            (10004, 1004, 'test1004name', 'test1004url', 'US'),
            (10005, 1005, 'test1005name', 'test1005url', 'US'),
            (10006, 1006, 'test1006name', 'test1006url', 'US'),
            (10007, 1007, 'test1007name', 'test1007url', 'US'),
            (10008, 1008, 'test1008name', 'test1008url', 'US');";
    const QUERY_DELETE = "DELETE FROM stores;";

    public function testGet() {
        $response = Store::get();

        // Two equals simply check for the same keys and values.
        // Three equals would also check for type and order.
        $this->assertTrue($response == static::getTestData(),
            print_r($response, true)
            );
    }

    public function testGetByIdValid() {
        $testData = static::getTestData();

        // Two equals simply check for the same keys and values.
        // Three equals would also check for type and order.
        foreach ($testData as $id => $object) {
            $response = Store::getById($id);
            $this->assertTrue(
                $response == array($id => $testData[$id]),
                print_r($response, true)
            );
        }
    }

    public function testGetByIdInvalid() {
        $response = Store::getById(static::INVALID_ID);

        $this->assertTrue($response == array(),
            print_r($response, true));
    }

    public static function getTestData() : array {
        return array(
            10001 => array ("id" => 10001, "user_id" => 1001, "name" => 'test1001name', "vanity_url" => 'test1001url', "country" => 'US', "site" => false),
            10002 => array ("id" => 10002, "user_id" => 1002, "name" => 'test1002name', "vanity_url" => 'test1002url', "country" => 'US', "site" => false),
            10003 => array ("id" => 10003, "user_id" => 1003, "name" => 'test1003name', "vanity_url" => 'test1003url', "country" => 'US', "site" => false),
            10004 => array ("id" => 10004, "user_id" => 1004, "name" => 'test1004name', "vanity_url" => 'test1004url', "country" => 'US', "site" => false),
            10005 => array ("id" => 10005, "user_id" => 1005, "name" => 'test1005name', "vanity_url" => 'test1005url', "country" => 'US', "site" => false),
            10006 => array ("id" => 10006, "user_id" => 1006, "name" => 'test1006name', "vanity_url" => 'test1006url', "country" => 'US', "site" => false),
            10007 => array ("id" => 10007, "user_id" => 1007, "name" => 'test1007name', "vanity_url" => 'test1007url', "country" => 'US', "site" => false),
            10008 => array ("id" => 10008, "user_id" => 1008, "name" => 'test1008name', "vanity_url" => 'test1008url', "country" => 'US', "site" => false)
        );
    }
}