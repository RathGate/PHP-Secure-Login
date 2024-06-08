<?php

namespace database;

use Exception;
use Throwable;

// Error class used for format errors on Database class.
class DatabaseFormatException extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}