<?php

namespace kahra\src\util;

use TypeError;

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
     * @return bool
     */
    static function get(string $needle, array $haystack) : bool {
        return ((
            array_key_exists($needle, $haystack)
            && !empty($haystack[$needle])
            && $haystack[$needle]
        ) ? $haystack[$needle] : false);
    }
}