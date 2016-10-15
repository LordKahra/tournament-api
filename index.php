<?php

require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');
//require_once (getenv('root_pairings') . '/scripts/app_config.php');

use kahra\src\util\Debug;

//require_once SITE_ROOT . "/src/view/View.php";

use kahra\src\database\Player;
use kahra\src\database\Pairing;
use kahra\src\database\Tournament;

?>

    <header>Share tournament pairings. Instantly.</header>

    <form action="<?php echo SITE_HOST; ?>/upload" method="post" enctype="multipart/form-data">
        <input name="tournament" type="file" />
        Programs currently supported:
        Wizards Event Reporter
        <input type="submit" value="Upload" name="submit" />
    </form>
