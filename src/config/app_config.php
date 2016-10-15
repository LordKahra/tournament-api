<?php

// require_once (getenv('root_pairings') . '/src/config/app_config.php');

// require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');

// PATH
define("SITE_ROOT", str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . ((!(strpos($_SERVER['DOCUMENT_ROOT'], 'tournament-api') === false)) ? '' : '/tournament-api')));
define("SITE_HOST", "http://"
    . str_replace(
        "//", "/", $_SERVER['HTTP_HOST'] . (
            "/tournament-api"
        )
    ));

// MYSQL
define("DATABASE_HOST", getenv("DATABASE_HOST"));
define("DATABASE_USERNAME", getenv("DATABASE_USERNAME"));
define("DATABASE_PASSWORD", getenv("DATABASE_PASSWORD"));
define("DATABASE_NAME", "pairings");
// TODO: Better security.

// EMAIL
define("EMAIL_PATH", "C:/wamp/sendmail/sendmail.exe -t -i");
define("EMAIL_FROM", "lanedev1@gmail.com");
define("EMAIL_HOST", "smtp.gmail.com");
define("EMAIL_PORT", "465");

// Debugging Switch.
define("DEBUG_MODE", true);

// GLOBAL REQUIREMENTS
require_once SITE_ROOT . "/src/config/global_config.php";

?>