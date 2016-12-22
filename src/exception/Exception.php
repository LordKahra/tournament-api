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

class EmptyInputException extends Exception {}
class MissingInputException extends Exception {}

class InvalidInputException extends Exception {}
class InvalidEmailException extends InvalidInputException {}
class InvalidPasswordException extends InvalidInputException {}

class AuthenticationFailureException extends InvalidInputException {
    function __construct($message) {
        parent::__construct($message);
        // TODO: Should we setLoggedIn(false)?
        //setLoggedIn(false);
    }
}