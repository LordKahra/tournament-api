<?php

$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
$GLOBALS['mysqli'] = $mysqli;

?>