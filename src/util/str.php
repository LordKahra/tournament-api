<?php

namespace kahra\src\util;

class Str {
    static function getAliases ($alias, $fields=array()) {
        $aliasedFields = array();
        foreach ($fields as $field) {
            $aliasedFields[] = $alias . "_" . $field;
        }
        return $aliasedFields;
    }

    static function getReferences ($alias, $fields=array()) {
        $referencedFields = array();
        foreach ($fields as $field) {
            $referencedFields[] = $alias . "." . $field;
        }
        return $referencedFields;
    }

    static function createSelectClause($alias, $fields=array()) {
        $clause = "";
        foreach ($fields as $field) {
            $clause .= (empty($clause) ? "" : ", ") . "$alias.$field AS $alias" . "_$field";
        }
        return $clause;
    }
}