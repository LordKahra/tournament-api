<?php

// To include:
// require_once (DIR . "util.php");
// use util\Utils

namespace kahra\src\util;

use TypeError;
//ini_set('xdebug.var_display_max_data', 2048);

class Debug {
    static function log($tag, $text) {
        self::fileLog($tag, $text);
        //self::consoleLog($text);
    }

    static function fileLog($tag, $text) {
        file_put_contents(LOG_FILE, "\r\n\r\n" . Time::getFormattedTime() . ": " . substr($tag,0,12) . " " . $text, FILE_APPEND);
    }

    static function consoleLog($text) {
        echo "<script>";
        echo "console.log(\"";

        str_replace("\"", "'", $text);
        str_replace("\r", "", $text);
        str_replace("\n", "", $text);

        echo($text);

        echo "\");";
        echo "</script>";
    }

}

class Time {
    static function getFormattedTime() {
        return date("[m/d/y H:i:s]");
    }
}

class Set {
    /**
     * Sets a key => value pair on every record in an array.
     *
     * @param mixed $key The key to be added to the records.
     * @param mixed $value The value to be stored under the key.
     * @param array $records The records.
     *
     * @throws TypeError if the key is null.
     */
    static function setAll(string $key, $value, array &$records) {
        if (!$key) throw new TypeError();
        foreach ($records as &$record) {
            $record[$key] = $value;
        }
    }

    /**
     * @param string $needle
     * @param array $haystack
     * @return string
     */
    static function get(string $needle, array $haystack) : string {
        return ((
            array_key_exists($needle, $haystack)
            && !empty($haystack[$needle])
            && $haystack[$needle]
        ) ? $haystack[$needle] : "");
    }
    // TODO: More functions. getInt, getBool, etc.
}

/* GLOBAL FUNCTIONS */

function readable($line) {
    $line = trim($line);
    $line = str_replace("<", "\r\n&lt;", str_replace(">", "&rt;", $line));
    return $line;
}

// Some incomplete utils.

/*public function getSegments() {
    $segments = array();

    foreach(explode("/", $this->getPathInfo()) as $value) {
        if (trim($value)) {
            $segments[] = $value;
        }
    }

    return $segments;
}*/

/*public function getPathInfo() {

    if (array_key_exists('PATH_INFO', $_SERVER)) {
        //echo 'SERVER[PATH_INFO]:' . $_SERVER['PATH_INFO'];
        //$pathInfo = $_SERVER['PATH_INFO'];
        $pathInfo = str_replace(SITE_ROOT . "src/", "", $_SERVER['PATH_INFO']);
        return (trim($pathInfo));
    }


    //echo 'SERVER[PHP_SELF]:' . $_SERVER['PHP_SELF'];

    $pathInfo = str_replace(SITE_ROOT, "", $_SERVER['PHP_SELF']);

    //echo "pathInfo: " . $pathInfo;

    return trim($pathInfo);

    $pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);

    $asd = substr($_SERVER['REQUEST_URI'], 0, $pos - 2);

    $asd = substr($asd, strlen($_SERVER['SCRIPT_NAME']) + 1);

    return $asd;
}*/

?>