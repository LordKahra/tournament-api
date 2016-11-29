<?php

require('../vendor/autoload.php');

$s3 = \Aws\S3\S3Client::factory();
$bucket = getenv("S3_BUCKET_NAME");

?>
<!DOCTYPE html>
<html>
<body>
<?php
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && isset($_FILES['userfile'])
    && $_FILES['userfile']['error'] == UPLOAD_ERR_OK
    && is_uploaded_file($_FILES['userfile']['tmp_name'])
) {
    try {

        $upload = $s3->upload(
            $bucket,
            $_FILES['userfile']['name'],
            fopen($_FILES['userfile']['tmp_name'], 'rb'),
            'public-read'
        );
        ?><p>Upload <a href="<?=htmlspecialchars($upload->get('ObjectURL'))?>">successful</a> :)</p><?php
    } catch(Exception $e) {
        ?><p>Upload error :( <?=$e->getMessage();?></p><?php
        var_dump($e);
    }
}

?>

<form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <input name="userfile" type="file"><input type="submit" value="Upload">
</form>
</body>
</html>