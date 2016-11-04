<?php

define("LOG_FILE", SITE_ROOT . "/log/debug.log");
define("ERROR_PAGE", SITE_HOST . "/src/view/error.php");
define("UPLOAD_DIRECTORY", SITE_ROOT . "/res/upload");
define("LOGO_URL", SITE_HOST . "/res/drawable/logo.png");

// Error Reporting.
error_reporting(DEBUG_MODE ? E_ALL : 0);

// COMPANY INFO
define("COMPANY_NAME", "Chaos Industries");
define("COMPANY_ADDRESS_FULL", "1234 Private Lane");
define("COMPANY_ADDRESS_1", "1234 Private Lane");
define("COMPANY_ADDRESS_2", false);
define("COMPANY_CITY", "Private");
define("COMPANY_STATE", "FL");
define("COMPANY_STATE_FULL", "Florida");
define("COMPANY_ZIP", "12345");
define("COMPANY_PHONE", "(123) 456-7890");

define("SITE_DESCRIPTION", "Share tournaments. Instantly.");

session_start();

// SCRIPTS
require_once SITE_ROOT . "/src/config/database_connection.php";
require_once SITE_ROOT . "/src/util/authentication.php";
require_once SITE_ROOT . "/src/util/util.php";
require_once SITE_ROOT . "/src/util/upload.php";
require_once SITE_ROOT . "/src/util/email.php";
require_once SITE_ROOT . "/src/util/str.php";
require_once SITE_ROOT . "/src/util/validation.php";

// MODEL REQUIREMENTS
require_once SITE_ROOT . "/src/exception/Exception.php";
require_once SITE_ROOT . "/src/database/Object.php";
require_once SITE_ROOT . "/src/database/Player.php";
require_once SITE_ROOT . "/src/database/Tournament.php";
require_once SITE_ROOT . "/src/database/Round.php";
require_once SITE_ROOT . "/src/database/Match.php";
require_once SITE_ROOT . "/src/database/Bye.php";
require_once SITE_ROOT . "/src/database/Seat.php";
require_once SITE_ROOT . "/src/database/Pairing.php";
require_once SITE_ROOT . "/src/database/User.php";
require_once SITE_ROOT . "/src/database/Store.php";
require_once SITE_ROOT . "/src/database/Upload.php";

// VIEW REQUIREMENTS
require_once SITE_ROOT . "/src/view/View.php";

// SYSTEM FUNCTIONS

function handle_error($user_error_message, $system_error_message) {
    $_SESSION['error_message'] = $user_error_message;
    $_SESSION['system_error_message'] = $system_error_message;
    header("Location: " . get_web_path(ERROR_PAGE));
}

function contains($haystack, $needle) {
    $position = strpos($haystack, $needle);
    $lost = ($position === false);
    return ($lost ? false : true);
}

?>