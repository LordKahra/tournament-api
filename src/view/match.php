<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Match;
use kahra\src\view\View;

$matches = Match::get();

//var_dump($matches);

echo "<pre>" . View::formatSuccessResponse("Something.", $matches) . "</pre>";

?>