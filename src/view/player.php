<?php

use kahra\src\database\Player;
use kahra\src\view\APIResponse;

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

$players = Player::get();

echo APIResponse::getSuccess("NOT YET IMPLEMENTED.", $players);