<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\database\User;

use kahra\src\view\View;

User::logout();

echo View::formatSuccessResponse("You have successfully logged out.");
