<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\util\Debug;

//require_once SITE_ROOT . "/src/view/View.php";

use kahra\src\database\Player;
use kahra\src\database\Pairing;
use kahra\src\database\Tournament;

?>
<!DOCTYPE html>
<head>
    <style type="text/css">
        html, body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        article section, article form {
            display: inline-block;
        }

        article section:first-of-type {
            padding-right: 64px;
            width: 100px;
            height: 64px;
            line-height: 64px;
            background-position: right;
            background-repeat: no-repeat;
        }

        section.true {
            background-image: url("<?=SITE_HOST; ?>/res/images/true.png");
        }

        section.false {
            background-image: url("<?=SITE_HOST; ?>/res/images/false.png");
        }
    </style>
</head>

<body>

    <header>Welcome to the API's debugging page. ^_^;;</header>

    <article>
        <section>Upload.</section>

        <form action="<?php echo SITE_HOST; ?>/tournament/upload" method="post" enctype="multipart/form-data">
            <input name="tournament" type="file" />
            Programs currently supported:
            Wizards Event Reporter
            <input type="submit" value="Upload" name="submit" />
        </form>
    </article>

    <article>
        <section class="<?=(isAuthenticated() ? "true" : "false");?>">Logged in: <?=(isAuthenticated() ? getLoggedInUserId() : "");?></section>

        <form action="<?php echo SITE_HOST; ?>/login" method="post" enctype="multipart/form-data">
            <p>Email <input name="email" type="email" /></p>
            <p>Password <input name="password" type="password" /></p>
            <input type="submit" value="Log In" name="submit" />
        </form>

        <section><a href="<?=SITE_HOST;?>/logout">Logout</a></section>
    </article>

    <p>Store Update</p>

    <form action="<?php echo SITE_HOST; ?>/store/update" method="post" enctype="multipart/form-data">
        <p>store_id <input name="store_id" type="number" /></p>
        <p>Name <input name="name" type="text" /></p>
        <p>Vanity URL <input name="vanity_url" type="text" /></p>
        <input type="hidden" name="extension" value=".wer" />
        <input type="submit" value="Update" name="submit" />
    </form>

    <?php

    $werDocument = new \kahra\src\file\WERDocument(file_get_contents("test_first_round.wer"));

    ?>

</body>
</html>

