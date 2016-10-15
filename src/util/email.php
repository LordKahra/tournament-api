<?php

namespace kahra\src\util;

class Email {
    const EMAIL_FROM = "lanedev1@gmail.com";

    const EMAIL_ADMIN = "lordkahra+development@gmail.com";

    const SENDMAIL_KAHRACO = "/usr/sbin/sendmail";

    static function send($to, $subject, $body) {
        ini_set("sendmail_path", EMAIL_PATH);
        ini_set("sendmail_from", EMAIL_FROM);
        ini_set("SMTP", EMAIL_HOST);
        ini_set("smtp_port", EMAIL_PORT);
        return mail($to, $subject, $body, "From: " . EMAIL_FROM);
    }

    static function emailAdmin($subject, $body) {
        return self::send(self::EMAIL_ADMIN, $subject, $body);
    }
}

?>