<?php

namespace kahra\src\exception;

use Exception;

class SQLException extends Exception {
    private $query;
    function __construct($message, $query=false) {
        parent::__construct($message);
        $this->query = $query ? $query : "Unknown query.";
    }

    public function getQuery() {
        return $this->query;
    }
}
class SQLInsertException extends SQLException {}
class SQLUpdateException extends SQLException {}

class InsertFailureException extends Exception {}
class UploadFailureException extends Exception {}

class InvalidInputException extends Exception {}
class InvalidEmailException extends InvalidInputException {}
class InvalidPasswordException extends InvalidInputException {}