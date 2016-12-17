<?php

// require_once (getenv('root_pairings') . '/src/config/app_config.php');

/*
    APP-DEPENDENT CONFIGS
        Headers
            util\upload.php
 */

// PATH
define("SITE_ROOT", getenv("SITE_ROOT_API_TOURNAMENT"));
define("SITE_HOST", getenv("SITE_HOST_API_TOURNAMENT"));

// MYSQL
define("DATABASE_HOST", getenv("DATABASE_HOST"));
define("DATABASE_USERNAME", getenv("DATABASE_USERNAME"));
define("DATABASE_PASSWORD", getenv("DATABASE_PASSWORD"));
define("DATABASE_NAME", getenv("DATABASE_NAME_API_TOURNAMENT"));
// TODO: Better security.

// EMAIL
define("EMAIL_PATH", "C:/wamp/sendmail/sendmail.exe -t -i");
define("EMAIL_FROM", "lanedev1@gmail.com");
define("EMAIL_HOST", "smtp.gmail.com");
define("EMAIL_PORT", "465");

// UPLOAD
define("BUCKET", getenv("S3_BUCKET_NAME"));

// Debugging Switch.
define("DEBUG_MODE", true);

// GLOBAL REQUIREMENTS
require_once SITE_ROOT . "/src/config/global_config.php";

?>