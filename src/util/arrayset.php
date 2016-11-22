<?php

namespace kahra\src\util;

use TypeError;

class ArraySet {
    /**
     * Sets a key => value pair on every record in an array.
     *
     * @param mixed $key The key to be added to the records.
     * @param mixed $value The value to be stored under the key.
     * @param array $records The records.
     *
     * @throws TypeError if the key is null.
     */
    static function setAll($key, $value, array &$records) {
        if (!$key) throw new TypeError();
        foreach ($records as &$record) {
            $record[$key] = $value;
        }
    }
}