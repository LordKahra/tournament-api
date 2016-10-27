<?php

namespace kahra\src\database;

use DOMDocument;
use kahra\src\exception\SQLInsertException;
use kahra\src\util\Debug;

abstract class Object {
    const FIELD_ID          = "id";
    const FIELD_PARENT_ID   = false;
    const TABLE_NAME        = false;
    const NAME_SINGULAR     = false;
    const TAG_NAME          = false;
    const ALIAS             = false;
    const FIELDS_SELECT     = false;
    const FIELDS_INSERT     = false;
    const FIELDS_UPSERT     = false;
    const DEFAULT_SORT      = false;

    static function getIDField() {
        return static::FIELD_ID;
    }

    static function getParentIDField() {
        return static::FIELD_PARENT_ID;
    }

    static function getTableName() {
        return static::TABLE_NAME;
    }

    static function getSingularName() {
        return static::NAME_SINGULAR;
    }

    static function getAlias() {
        return static::ALIAS;
    }

    static function getTagName() {
        return static::TAG_NAME;
    }

    static function getQueryFields() {
        return explode(",",static::FIELDS_SELECT);
    }

    static function getUpsertFields() {
        return explode(",",static::FIELDS_UPSERT);
    }

    static function getRequiredFields() {
        return explode(",",static::FIELDS_INSERT);
    }

    static function getSelectClause($includeChildren=true) {
        $clause = "";

        // Add object fields.
        foreach(static::getQueryFields() as $field) {
            $clause .= (empty($clause) ? "" : ",") . "\n`" . static::getAlias() . "`." . $field . " AS " . static::getSingularName() . "_" . $field;
        }

        // If any, add child fields.
        if ($includeChildren) foreach (static::getChildren() as $child) {
            $class = $child['class'];
            $clause .= (empty($clause) ? "" : ",") . "\n" . $class::getSelectClause();
        }

        foreach(static::getMonogamousJoins() as $join) {
            $clause .= (empty($clause) ? "" : ",") . "\n" . $join["select"];
        }
        //$childClauses = static::getJoinQueryClauses(static::getJoins());
        //$clause .= $childClauses["select"];

        return $clause;
    }

    static function getJoinClause($joins=array()) {
        $allJoins = array_merge(static::getChildren(), $joins);
        $clause = "";
        foreach ($allJoins as $child) {
            $clause .= (empty($clause) ? "" : " ") . "\n" . strtoupper($child["type"]) . " JOIN " . $child["clause"];
            $class = $child['class'];
            if ($class) $clause .= $class::getJoinClause();
        }
        return $clause;
    }

    static function getChildClauses($joins) {
        $selectText = "";
        $joinText = "";

        foreach($joins as $join) {
            $selectText .= (empty($selectText) ? "" : ",") . "\n" . $join["select"];
            $joinText   .= (empty($joinText) ? "" : " ") . "\n" . strtoupper($join["type"]) . " JOIN " . $join["clause"];
        }

        $clauses = array(
            "select"    => $selectText,
            "join"      => $joinText
        );

        return $clauses;
    }

    static function getAttributeTable() {
        return array();
    }

    static function getChildren() {
        /*
        Child_Ref_To_Parent__c => array(
            new ChildA($this->controller),
            new ChildB($this->controller)
        );
        */
        return array();
    }

    static function getMonogamousJoins() {
        /*
        return array(
            alias => array(
                "fields" => array("field_a","field_b"),
                "table" => Subobject::getTableName(),
                "alias" => Subobject::getAlias(),
                "query" =>
                    "[TYPE] JOIN " .
                        "[table_a] AS [a_alias], [table_b] AS [b_alias] " .
                    "ON [table_a.field] =  [alias] " .
                    "WHERE [alias].[reference] = [object_alias].[object_reference] as [alias]",
            )
        );

         */
        return array();
    }

    static function getSubqueries() {
        /*
        return array(
            alias => array(
                "fields" => array("field_a","field_b"),
                "table" => Subobject::getTableName(),
                "alias" => Subobject::getAlias(),
                "subquery" =>
                    "(SELECT [field_a], [field_b] " .
                    "FROM [table] [alias] " .
                    "WHERE [alias].[reference] = [object_alias].[object_reference]) as [alias]",
            )
        );

         */
        return array();
    }

    static function getJoins($includeChildren=true) {
        if ($includeChildren) {

            $joins = static::getChildren();

            foreach (static::getChildren() as $child) {
                $class = $child['class'];
                $joins = array_merge($joins, $class::getChildren());
            }

            //$joins = array_merge($joins, static::getMonogamousJoins());

            return $joins;
        }

        return array();
    }

    static function getDefaultSort() {
        return static::DEFAULT_SORT;
    }

    ////////////////////////////////
    // QUERIES : SELECT ////////////
    ////////////////////////////////

    // innerJoin: `TARGET` on SOURCE.FIELD = TARGET.FIELD;
    static function get($where=false, $joins=array(), $order=false, $includeChildren=false) {
        //$children = $includeChildren ? static::getChildren() : array();

        // Create variables.
        //$joinClauses = static::getChildClauses($joins ? array_merge($joins, static::getJoins()) : static::getJoins());
        //$join = $joinClauses["join"];
        //$join = ($joins ? $joins . " \r\n" : "") . static::getJoinClause(); // TODO: Added joins.

        // Create the SELECT clause.
        $select = static::getSelectClause($includeChildren);// . ($joinClauses["select"] ? "," : "") . $joinClauses["select"];
        $subselect = "";
        foreach (static::getSubqueries() as $subquery) $subselect .= (empty($subselect) ? " " : ", " ) . $subquery["query"];

        // Create the JOIN clauses.
        $monojoins = "";
        foreach (static::getMonogamousJoins() as $monojoin) {
            $monojoins .= (empty($monojoins) ? " " : ", " ) . $monojoin["query"];
        }

        $join = "";
        $join = $includeChildren ? static::getJoinClause($joins) : "";
        $order      = $order ? $order : (static::getDefaultSort() ? static::getDefaultSort() : "");

        $query =
            "SELECT " .
                $select .
                ($subselect ? ", " . $subselect : "") . " " .
            "\r\nFROM " . static::getTableName() . " `" . static::getAlias() . "`" .
            ($monojoins ? " \r\n" . $monojoins : "") .
            $join .
            ($where     ? " \r\nWHERE "         . $where                : "") .
            ($order     ? " \r\nORDER BY "      . $order                : "");

        //Debug::log("Object.get", $query);
        //echo "<br/><br/><br/><br/><br/><br/>" . $query;
        global $mysqli;
        $result = $mysqli->query($query);

        if ($result) {
            $objects = mysqli_fetch_all($result, MYSQLI_ASSOC);
            /*while ($object = mysqli_fetch_all($result, MYSQLI_ASSOC)) {
                //$objects[$object[static::getSingularName() . "_" . static::FIELD_ID]] = $object;
                // TODO:
                $objects[] = $object;
            }*/
            $mappedObjects = array();
            foreach ($objects as $object) {

                $cleanObject = array();
                foreach ($object as $key => $value) {
                    // This is where the name can be stripped out.
                    $cleanKey = str_replace(static::getSingularName() . "_", "", $key);
                    $cleanObject[$cleanKey] = $value;
                }
                //$mappedObjects[$object[static::getSingularName() . "_" . static::FIELD_ID]] = $object;
                $mappedObjects[$cleanObject[static::FIELD_ID]] = $cleanObject;
            }
            return $mappedObjects;
        }
        return false;
        //return $results;
    }

    static function getById($value) {
        // Making unambiguous for joins.
        return static::getByField(static::getAlias() . "." . static::getIDField(), $value);
    }

    static function getByField($key, $value) {
        return static::get($key . " = \"" . $value . "\"");
    }

    static function getByFields($key, $values) {
        return static::get($key . " IN ('" . implode("','", $values) . "')");
    }

    ////////////////////////////////
    // QUERIES : INSERT ////////////
    ////////////////////////////////

    static function insert($fields) {
        $requiredFields = static::getRequiredFields();

        // Check for required fields.
        foreach ($requiredFields as $required) {
            if (!array_key_exists($required, $fields)) return false;
        }

        // Generate key/value strings.
        $keyString = "";
        $valueString = "";

        foreach ($fields as $key => $value) {
            $keyString      .= (empty($keyString)   ? "" : ",") . $key;
            $valueString    .= (empty($valueString) ? "" : ",") . "\"" . $value . "\"";
        }

        $query =
            "INSERT INTO " . static::getTableName() .
                "(" . $keyString . ") " .
            "VALUES " .
                "(" . $valueString . ")";

        //echo $query;
        global $mysqli;
        $results = $mysqli->query($query);
        //var_dump($fields);
        //var_dump($results);
        //print_r($mysqli);
        if (!$results) throw new SQLInsertException("Failed to insert " . static::ALIAS . " with error: " . $mysqli->error);
        return $mysqli->insert_id;
        // TODO: FULL SWAP OVER TO STATUS => true/false
    }

    static function bulkInsert($objects) {
        $fields = static::getRequiredFields();
        $values = "";

        foreach ($objects as $object) {
            $value = "";
            foreach ($fields as $field) {
                $value .= (empty($value) ? "" : "," ) . "\"" . $object[$field] . "\"";
            }
            $values .= (empty($values) ? "" : ",") . "\r\n" .
                "(" . $value . ")";
        }

        $query =
            "INSERT INTO " . static::getTableName() .
                "(" . implode(",", $fields) . ") " .
            "VALUES " .
                $values;
        //echo $query;
        global $mysqli;
        $results = $mysqli->query($query);
        return $mysqli->insert_id;
    }

    ////////////////////////////////
    // QUERIES : UPDATE ////////////
    ////////////////////////////////

    static function update($fields, $where) {
        $fieldString = "";
        foreach ($fields as $key => $value) {
            $fieldString .= (empty($fieldString) ? "" : ",") . $key . "=\"" . $value . "\"";
        }
        $query =
            "UPDATE " . static::getTableName() .
            " SET " . $fieldString .
            " WHERE " . $where;

        //echo $query;
        global $mysqli;
        $results = $mysqli->query($query);
        //return $results;
        return $mysqli->insert_id;
    }

    ////////////////////////////////
    // QUERIES : DELETE ////////////
    ////////////////////////////////

    static function delete($where) {
        $query =
            "DELETE FROM " . static::getTableName() .
            " WHERE " . $where;

        global $mysqli;
        return $where ? $mysqli->query($query) : false;
    }

    ////////////////////////////////
    // QUERIES : UPSERT ////////////
    ////////////////////////////////

    static function upsert($objects) {
        $fields = static::getRequiredFields();
        $values = "";
        $onDuplicate = "";

        //var_dump($objects);

        foreach ($fields as $field) {
            $onDuplicate .= (empty($onDuplicate) ? "" : ",") .
                $field . "=VALUES(" . $field . ")";
        }

        foreach ($objects as $object) {
            $value = "";
            foreach ($fields as $field) {
                $value .= (empty($value) ? "" : "," ) . "\"" . $object[$field] . "\"";
            }
            $values .= (empty($values) ? "" : ",") . "\r\n" .
                "(" . $value . ")";
        }

        $query =
            "INSERT INTO " . static::getTableName() .
                "(" . implode(",", $fields) . ") " .
            "VALUES " .
                $values .
            " " .
            "ON DUPLICATE KEY UPDATE " . $onDuplicate;

        // Run the query.
        //echo "<br/><br/>" . $query;

        global $mysqli;
        $results = $mysqli->query($query);
        return $results;
    }

    static function parseXML($xml) {
        // Get the attribute table.
        $attributeTable = static::getAttributeTable();

        // Make a DomDocument and load in the tag.
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $elements = $dom->getElementsByTagName(static::getTagName());

        // Make a container object.
        $objects = array();

        foreach ($elements as $element) {
            //echo "<br/>iterating...<br/>";
            //echo "<br/>var_dump(element->attributes):<br/>";
            //var_dump($element->attributes);
            //echo "<br/>end var_dump";

            $object = array();
            foreach ($element->attributes as $attribute) {
                $key = $attribute->name;
                $value = $attribute->value;
                //echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp;Testing element[" . $key . "]: " . $value;
                if (array_key_exists($key, $attributeTable)) {
                    $object[$attributeTable[$key]] = $value;
                    //echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp;Array key " . $key . " exists! Saved.";
                } else {
                    // Debug info.
                    //echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp;Array key " . $key . " does not exist.";
                }
            }
            if (!empty($object)) $objects[] = $object;
        }

        return $objects;
    }

    static function getGenericChildJoinClause($child_table, $child_alias, $child_field_parent) {
        return "$child_table $child_alias ON $child_alias.$child_field_parent = " . static::getAlias() . "." . static::getIDField();
    }

    static function getGenericParentJoinClause($parent_table, $parent_alias, $parent_field_child, $parent_id_field) {
        return "$parent_table $parent_alias ON " . static::getAlias() . "." . $parent_field_child . " = $parent_alias.$parent_id_field";
    }

    // Incomplete/future functions.

    public static function parseObjects($records, $class) {
        $objects = array();
        foreach ($records as $record) {
            $object = static::parseObject($record, $class);
            // Add all missing objects.
            static::addMissingObjects($objects, $object, $class);
            // If the id doesn't already exist, add it.
            /*$id = $record[$class::getAlias() . "_" . $class::getIDField()];
            if (!array_key_exists($id, $objects)) {
                $object = static::createObject($record, $class);
                if ($object) $objects[$id] = $object;
            }*/


        }
        return $objects;
    }

    public static function addMissingObjects($objects, $object, $class) {
        if (!array_key_exists($objects, $object[$class::getIDField()])) {
            $objects[$object[$class::getIDField()]] = $object;
        } else {
            foreach($class::getChildren() as $child) {
                foreach($object[$child["class"]::getTableName()] as $child_object) {
                    static::addMissingObjects($objects[$object[$class::getIDField()]], $child_object, $child["class"]);
                }
            }
        }
    }

    public static function getDescendants($class) {
        $descendants = array();
        foreach(static::getChildren() as $child) {
            $descendants[] = $child;
            foreach ($child["class"]::getChildren() as $grandchild) {
                $descendants[] = $grandchild;
            }
        }
    }

    public static function parseObject($record, $class) {
        $id = $record[$class::getAlias() . "_" . $class::getIDField()];
        if ($id) {
            $object = $class::createObject();
            foreach($class::getChildren() as $child) {
                // Get the class.
                $child_class = $child["class"];

                // Check for child existence.
                $child_id = $child[$child_class::getAlias() . "_" . $child_class::getIDField()];

                $child_array = Object::parseObject($record, $child_class);
                if ($child_array) $object[$child_class::getTableName()][$child_id] = $child_array;
            }
            return $object;
        }
        return false;
    }

    public static function parseSingleRecord($record) {
        $object = static::createObject($record);
        foreach(static::getChildren() as $child) {
            $child_class = $child["class"];
            $child_table_name = $child_class::getTableName();
            $child_id_field = $child_class::getAlias() . "_" . $child_class::getIDField();
            $object[$child_table_name] = array();
            if (array_key_exists($child_id_field, $record)) {
                $object[$child_table_name][$record[$child_id_field]] = $child_class::parseSingleRecord($record);
            }
        }
        //var_dump($object);
        return $object;
    }

    public static function mergeRecord(&$objects, $object) {
        $record_id = $object[static::getIDField()];
        if (!array_key_exists($record_id, $objects)) {
            $objects[$record_id] = $object;
        } else {
            foreach(static::getChildren() as $child) {
                $child_table_name = $child["class"]::getTableName();
                foreach($object[$child_table_name] as $child_object) {
                    $child["class"]::mergeRecord($objects[$record_id][$child_table_name], $child_object);
                }
            }
        }
    }

    private static function mergeObject($objects, $object) {
        $id = $object[static::getIDField()];

        if (array_key_exists($id, $objects)) {
            foreach (static::getChildren() as $child_type) {
                $child_class = $child_type["class"];
                $child_table_name = $child_class::getTableName();
                $child_array = $object[$child_table_name];

                foreach ($child_array as $child) {
                    $objects[$id][$child_table_name] = $child_class::mergeObject($objects[$id][$child_table_name], $child);
                }
            }
        } else {
            $objects[$id] = $object;
        }
        return $objects;
    }

    public static function parseRecords($records) {
        $objects = array();
        foreach ($records as $record) {
            $object = static::parseSingleRecord($record);
            $objects = static::mergeObject($objects, $object);
            //var_dump($object);
        }
        return $objects;
    }

    public static function createObject($record) {
        $object = array();
        // Get all of the object's fields.
        foreach(static::getQueryFields() as $field) {
            $object[$field] = $record[static::getAlias() . "_" . $field];
        }
        // Get all of the object's subquery fields.
        foreach(static::getSubqueries() as $subquery) {
            foreach($subquery["fields"] as $field) {
                $aliased_field = $subquery["alias"] . "_" . $field;
                $object[$aliased_field] = $record[$aliased_field];
            }
        }
        // Get all of the object's monogamous join fields.
        foreach(static::getMonogamousJoins() as $monojoin) {
            foreach($monojoin["fields"] as $field) {
                $aliased_field = $monojoin["alias"] . "_" . $field;
                $object[$aliased_field] = $record[$aliased_field];
            }
        }
        // Create containers for all of the object's children.
        foreach(static::getChildren() as $child) {
            $object[$child["class"]::getTableName()] = array();
        }
        return $object;
    }
}