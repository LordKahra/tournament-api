<?php

//require_once str_replace("//", "/", $_SERVER['DOCUMENT_ROOT'] . (((strpos($_SERVER['DOCUMENT_ROOT'], 'wamp') === false)) ? '' : '/tournament-api') . '/src/config/app_config.php');
require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '\src\config\app_config.php');

use kahra\src\util\Debug;

//require_once SITE_ROOT . "/src/view/View.php";

use kahra\src\database\Player;
use kahra\src\database\Pairing;
use kahra\src\database\Tournament;

?>

    <header>Share tournament pairings. Instantly.</header>

    <form action="<?php echo SITE_HOST; ?>/tournament/upload" method="post" enctype="multipart/form-data">
        <input name="tournament" type="file" />
        Programs currently supported:
        Wizards Event Reporter
        <input type="submit" value="Upload" name="submit" />
    </form>

    <form action="<?php echo SITE_HOST; ?>/login" method="post" enctype="multipart/form-data">
        <p>Email <input name="email" type="email" /></p>
        <p>Password <input name="password" type="password" /></p>
        <input type="submit" value="Log In" name="submit" />
    </form>

    <p><a href="<?=SITE_HOST;?>/logout">Logout</a></p>


    <p>Store Update</p>

    <form action="<?php echo SITE_HOST; ?>/store/update" method="post" enctype="multipart/form-data">
        <p>store_id <input name="store_id" type="number" /></p>
        <p>Name <input name="name" type="text" /></p>
        <p>Vanity URL <input name="vanity_url" type="text" /></p>
        <input type="submit" value="Update" name="submit" />
    </form>



