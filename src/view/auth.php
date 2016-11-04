<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\Object;
use kahra\src\database\User;

use kahra\src\view\View;

function register($email, $password, $dci) {
    $result = User::register($email, $password, $dci);

    if ($result == User::STATUS_VALID) return true;

    return false;
}

