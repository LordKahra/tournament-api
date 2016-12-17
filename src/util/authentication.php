<?php

// TODO: Namespacing and abstract class.
// TODO: Moving to stateless.

use kahra\src\database\User;
use kahra\src\exception\AuthenticationFailureException;

// STATES

function isLoggedIn() {
    // Every "isLoggedIn()" check will pull the database, now.
    //$token = (isset($_POST["token"]) ? $_POST["token"] : false);
    //authenticate($token);

    return isset($_SESSION["id"]) && !empty($_SESSION["id"]);
}

function isAuthenticated() {
    // If a token exists, use it.
    if (array_key_exists("token", $_POST)) authenticate($_POST["token"]);
    else setLoggedIn(false);

    // TODO: Check for time on remaining token.

    // Finally, check if they're logged in.
    return isLoggedIn();
}

// GETTERS

/**
 * @param string $errorMessage
 * @return int
 * @throws AuthenticationFailureException
 */
function getLoggedInUserId($errorMessage="Not logged in.") : int {
    if (!isset($_SESSION["id"]) || empty($_SESSION["id"]) || !$_SESSION["id"])
        throw new AuthenticationFailureException($errorMessage);

    return $_SESSION["id"];
}

function isMagicPlayer($dci) {
    return !empty($dci) && isset($_SESSION["dci"]) && $_SESSION["dci"] == $dci;
}

// SETTERS

function setLoggedIn($user=false) {
    $prefix = User::getPrefix();
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
}

function authenticate($token) {
    $result = $token ? User::getByActiveToken($token) : array();
    $prefix = User::getPrefix();

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
}

// TRIGGERS

function onLogin($message=false, $redirect=false) {
    $site = SITE_HOST . ($redirect ? "/" . urlencode($redirect) : "") . ($message ? "?message=" . urlencode($message) : "");
    header("Location: " . SITE_HOST . "/" . $site);
    exit();
}

function onFailedAuthentication($message=false, $redirect=false) {
    $site = SITE_HOST . "/login" .
        ($message ? "?message=" . urlencode($message) : "") .
        ($redirect ? ($message ? "&" : "?") . "redirect=" . urlencode($redirect) : "");
    header("Location: " . SITE_HOST . "/" . $site);
    exit();
}