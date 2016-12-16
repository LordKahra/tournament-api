<?php

namespace kahra\test\database;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/test/database/TestData.php');

use kahra\src\database\User;

class UserTest extends ObjectCase {
    static function getInsertQuery() {
        return "INSERT INTO users (id, dci, email, password)
        VALUES
            (1001, 1000001, 'test1001email@tourney.cloud', '" . User::generateHashedPassword('test1001password') . "'),
            (1002, 1000002, 'test1002email@tourney.cloud', '" . User::generateHashedPassword('test1002password') . "'),
            (1003, 1000003, 'test1003email@tourney.cloud', '" . User::generateHashedPassword('test1003password') . "'),
            (1004, 1000004, 'test1004email@tourney.cloud', '" . User::generateHashedPassword('test1004password') . "'),
            (1005, 1000005, 'test1005email@tourney.cloud', '" . User::generateHashedPassword('test1005password') . "'),
            (1006, 1000006, 'test1006email@tourney.cloud', '" . User::generateHashedPassword('test1006password') . "'),
            (1007, 1000007, 'test1007email@tourney.cloud', '" . User::generateHashedPassword('test1007password') . "'),
            (1008, 1000008, 'test1008email@tourney.cloud', '" . User::generateHashedPassword('test1008password') . "');";
    }
    const QUERY_DELETE = "DELETE FROM users;";


    public function testGet() {
        $response = User::get();
        $testData = static::getTestData();

        foreach ($response as $id => &$user) {
            $this->validatePassword($testData[$id], $user);
        }

        $this->assertTrue($response == $testData,
            print_r($response, true)
        );
    }

    public function testGetByIdValid() {
        $testData = static::getTestData();

        // Two equals simply check for the same keys and values.
        // Three equals would also check for type and order.
        foreach ($testData as $id => $testUser) {
            $response = User::getById($id);
            $this->validatePassword($testUser, $response[$id]);
            $this->assertTrue(
                $response == array($id => $testUser),
                print_r($response, true)
            );
        }
    }

    public function testGetByIdInvalid() {
        $response = User::getById(static::INVALID_ID);

        $this->assertTrue($response == array(),
            print_r($response, true));
    }

    private function validatePassword(&$testUser, &$responseUser) {
        $this->assertTrue(User::authenticatePassword($testUser["password"], $responseUser["password"]), "Passwords didn't match:\r\n" . $testUser["password"] . " vs " . $responseUser["password"]);
        unset($responseUser["password"]);
        unset($testUser["password"]);
    }

    static function getTestData() : array {
        return array(
            1001 => array ("id" => 1001, "dci" => 1000001, "email" => 'test1001email@tourney.cloud', "password" => 'test1001password', "is_subscribed" => 1),
            1002 => array ("id" => 1002, "dci" => 1000002, "email" => 'test1002email@tourney.cloud', "password" => 'test1002password', "is_subscribed" => 1),
            1003 => array ("id" => 1003, "dci" => 1000003, "email" => 'test1003email@tourney.cloud', "password" => 'test1003password', "is_subscribed" => 1),
            1004 => array ("id" => 1004, "dci" => 1000004, "email" => 'test1004email@tourney.cloud', "password" => 'test1004password', "is_subscribed" => 1),
            1005 => array ("id" => 1005, "dci" => 1000005, "email" => 'test1005email@tourney.cloud', "password" => 'test1005password', "is_subscribed" => 1),
            1006 => array ("id" => 1006, "dci" => 1000006, "email" => 'test1006email@tourney.cloud', "password" => 'test1006password', "is_subscribed" => 1),
            1007 => array ("id" => 1007, "dci" => 1000007, "email" => 'test1007email@tourney.cloud', "password" => 'test1007password', "is_subscribed" => 1),
            1008 => array ("id" => 1008, "dci" => 1000008, "email" => 'test1008email@tourney.cloud', "password" => 'test1008password', "is_subscribed" => 1)
        );
    }
}