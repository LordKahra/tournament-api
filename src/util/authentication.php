<?php

// TODO: Namespacing and abstract class.
// TODO: Moving to stateless.

function isLoggedIn() {
    return isset($_SESSION["id"]) && !empty($_SESSION["id"]);
}

function getLoggedInUserId() {
    return (isset($_SESSION["id"]) && !empty($_SESSION["id"])) ? $_SESSION["id"] : false;
}

function isMagicPlayer($dci) {
    return !empty($dci) && isset($_SESSION["dci"]) && $_SESSION["dci"] == $dci;
}

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