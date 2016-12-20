<?php

namespace kahra\src\database;

use kahra\src\exception\InvalidEmailException;
use kahra\src\exception\InvalidInputException;
use kahra\src\exception\InvalidPasswordException;
use kahra\src\util\Validation;
use kahra\src\database\Object;
use kahra\src\database\Token;

class User extends Object {
    const FIELD_ID          = "id";
    const TABLE_NAME        = "users";
    const NAME_SINGULAR     = "user";
    const ALIAS             = "user";
    const FIELDS_SELECT     = "id,dci,email,password,is_subscribed,name";
    const FIELDS_INSERT     = "email,password,dci";
    const FIELDS_UPDATE     = "dci,email,name";
    const FIELDS_PRIVATE    = "password";

    const STATUS_VALID = 1;
    const STATUS_INVALID_EMAIL = 2;
    const STATUS_INVALID_PASSWORD = 3;
    const STATUS_DUPLICATE_EMAIL = 4;
    const STATUS_DUPLICATE_DCI = 5;
    const STATUS_INVALID_TOKEN = 5;

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

    // Parent function that checks if the user's token is valid.
    /*static function isAuthenticated() {
        // If a token exists, use it.
        if (array_key_exists("token", $_POST)) authenticate($_POST["token"]);
        else setLoggedIn(false);

        // TODO: Check for time on remaining token.

        // Finally, check if they're logged in.
        return isLoggedIn();
    }*/

    //
    /*static function authenticate($token) {
        $result = $token ? static::getByActiveToken($token) : array();
        $prefix = static::getPrefix();

        $user = false;
        foreach ($result as $row) $user = $row;

        $valid = ((
            $user
            && is_array($user)
            && array_key_exists($prefix . "id", $user)
            && $user[$prefix . "id"]
        ) ? $user : false);

        //return $valid;

        // Why store the session data? Why not simply return the user data?

        setLoggedIn($valid);

        //return isLoggedIn();
    }*/

    // Returns the generated token.
    static function login($email, $password) {
        $validEmail = Validation::validateEmail($email);
        $validPassword = Validation::validatePassword($password);

        if (!$validEmail) throw new InvalidInputException("You must submit a valid email.");
        if (!$validPassword) throw new InvalidInputException("You must submit a valid password.");

        // Query the database.

        $result = static::getByField("email", $validEmail);
        $prefix = static::getPrefix();

        $user = false;
        foreach ($result as $row) $user = $row;

        // Make sure we found a user.
        if (!$user) throw new InvalidEmailException("Invalid email.");

        // Make sure their password is correct.
        $valid = ((
            $user
            && is_array($user)
            && array_key_exists($prefix . "id", $user)
            && array_key_exists($prefix . "password", $user)
            && $user[$prefix . "id"]
            && static::authenticatePassword($validPassword, $user[$prefix . "password"])
        ) ? $user : false);

        if (!$valid) throw new InvalidPasswordException("Invalid password.");

        // They're good! Return true.

        // TODO: Going stateless.
        //static::setLoggedIn($valid);

        //if (!isLoggedIn()) throw new InvalidPasswordException("Invalid password.");

        // Generate a token.
        $token = Token::create($user[$prefix . "id"]);

        //throw new InvalidPasswordException("DEBUG");

        return array(
            "id" => $user[$prefix . "id"],
            "dci" => $user[$prefix . "dci"],
            "email" => $validEmail,
            "token" => $token
        );

        //return ((!isLoggedIn() || !$user) ? static::STATUS_INVALID_EMAIL : (!$valid ? static::STATUS_INVALID_PASSWORD : static::STATUS_VALID));
    }

    static function isActiveToken($user_id, $token) {
        return static::get(
            "id IN (
                SELECT user_id
                FROM tokens
                WHERE token = '$token' AND expiration > NOW()
                ) AND id = '$user_id'");
    }

    static function getByToken($token) {
        return static::get("id IN (SELECT user_id FROM tokens WHERE token = '$token')");
    }

    static function getByActiveToken($token) {
        return static::get("id IN (SELECT user_id FROM tokens WHERE token = '$token' AND expiration > NOW())");
    }

    static function logout() {
        setLoggedIn(false);
    }

    static function register($email, $password, $dci) {
        // TODO: Input validation.
        //$validId = false;
        $validEmail = Validation::validateEmail($email);
        $validPassword = Validation::validatePassword($password);
        $validDci = Validation::validateDci($dci);

        if (!$validEmail) throw new InvalidInputException("You must submit a valid email.");
        if (!$validPassword) throw new InvalidInputException("You must submit a valid password.");
        if (!$validDci) throw new InvalidInputException("You must submit a valid dci number.");

        // Query the database.

        static::bulkInsert(array(array(
            "email" => $validEmail,
            "password" => static::generateHashedPassword($validPassword),
            "dci" => $validDci
        )));

        global $mysqli;

        return ($mysqli->insert_id
            ? array(
                "id" => $mysqli->insert_id,
                "dci" => $validDci,
                "email" => $validEmail,
                "token" => Token::create($mysqli->insert_id)
            )
            : false);

        // Generate a token.
        //$token = Token::create($mysqli->insert_id);

        //return $user;

        /*
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
        */
    }

    /*static function setLoggedIn($user=false) {
        //session_start();
        $prefix = static::getPrefix();
        if (
            $user
            && is_array($user)
            && array_key_exists($prefix . "id", $user)
            && array_key_exists($prefix . "dci", $user)
            && array_key_exists($prefix . "email", $user)
        ) {
            $_SESSION["id"] = $user[$prefix . "id"];
            $_SESSION["dci"] = $user[$prefix . "dci"];
            $_SESSION["email"] = $user[$prefix . "email"];
        } else {
            unset($_SESSION["id"]);
            unset($_SESSION["dci"]);
            unset($_SESSION["email"]);
        }
    }*/

    static function generateHashedPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    static function authenticatePassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // CUSTOM QUERIES ////

    static function getByName($value) {
        return static::getByField("name", $value);
    }
}