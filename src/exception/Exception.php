<?php

namespace kahra\src\exception;

use Exception;

class SQLException extends Exception {}
class SQLInsertException extends SQLException {}
class SQLUpdateException extends SQLException {}

class InsertFailureException extends Exception {}
class UploadFailureException extends Exception {}

class InvalidInputException extends Exception {}
class InvalidEmailException extends InvalidInputException {}
class InvalidPasswordException extends InvalidInputException {}