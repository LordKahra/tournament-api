<?php

namespace kahra\src\database;

use kahra\src\exception\InvalidInputException;
use kahra\src\util\Validation;
use kahra\src\database\Object;

class User extends Object {
    const FIELD_ID          = "id";
    const TABLE_NAME        = "users";
    const NAME_SINGULAR     = "user";
    const ALIAS             = "user";
    const FIELDS_SELECT     = "id,dci,email,password,is_subscribed";
    const FIELDS_INSERT     = "email,password,dci";

    const STATUS_VALID = 1;
    const STATUS_INVALID_EMAIL = 2;
    const STATUS_INVALID_PASSWORD = 3;
    const STATUS_DUPLICATE_EMAIL = 4;
    const STATUS_DUPLICATE_DCI = 5;

    /*static function getJoins($includeChildren=true) {
        $alias = static::ALIAS;
        $store_table = Store::TABLE_NAME;
        $store_alias = Store::ALIAS;

        if ($includeChildren) {

            $joins = static::getChildren();

            foreach (static::getChildren() as $child) {
                $class = $child['class'];
                $joins = array_merge($joins, $class::getJoins());
            }

            return $joins;
        }

        return array();
    }*/

    static function getChildren() {
        return array(
            array(
                "type" => "left",
                "alias" => Store::getAlias(),
                "select" => (Store::getSelectClause()),
                "clause" => static::getGenericChildJoinClause(Store::TABLE_NAME, Store::ALIAS, Store::FIELD_PARENT_ID),
                "class" => new Store()
            )
        );
    }

    // Returns status.
    static function login($email, $password) {
        $result = static::getByField("email", $email);
        $prefix = static::getPrefix();

        $user = false;
        foreach ($result as $row) $user = $row;

        $valid = (
            (is_array($user) && array_key_exists($prefix . "password", $user) && static::validatePassword($password, $user[$prefix . "password"]))
            ? $user
            : false
        );

        static::setLoggedIn($valid);

        return (!$user ? static::STATUS_INVALID_EMAIL : (!$valid ? static::STATUS_INVALID_PASSWORD : static::STATUS_VALID));
    }

    static function logout() {
        static::setLoggedIn(false);
    }

    static function register($email, $password, $dci) {
        // TODO: Input validation.
        $validEmail = Validation::validateEmail($email);
        $validPassword = Validation::validatePassword($password);
        $validDci = Validation::validateDci($dci);

        if (!$validEmail) throw new InvalidInputException("You must submit a valid email.");
        if (!$validPassword) throw new InvalidInputException("You must submit a valid password.");
        if (!$validDci) throw new InvalidInputException("You must submit a valid dci number.");

        static::bulkInsert(array(array(
            "email" => $validEmail,
            "password" => static::generateHashedPassword($validPassword),
            "dci" => $validDci
        )));

        global $mysqli;

        $user = ($mysqli->insert_id
            ? array(
                "id" => $mysqli->insert_id,
                "dci" => $validDci,
                "email" => $validEmail
            )
            : false);

        static::setLoggedIn($user);

        if (!$user) {
            // On failure, return information.
            $error = $mysqli->error;
            //echo $error;
            // TODO: Better error handling.
            return -1;
        } else {
            return static::STATUS_VALID;
        }
    }

    static function setLoggedIn($user=false) {
        //session_start();
        $prefix = static::getPrefix();
        if ($user) {
            $_SESSION["id"] = $user[$prefix . "id"];
            $_SESSION["dci"] = $user[$prefix . "dci"];
            $_SESSION["email"] = $user[$prefix . "email"];
        } else {
            unset($_SESSION["id"]);
            unset($_SESSION["dci"]);
            unset($_SESSION["email"]);
        }
    }

    static function generateHashedPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    static function validatePassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // CUSTOM QUERIES ////

    static function getByName($value) {
        return static::getByField("name", $value);
    }
}