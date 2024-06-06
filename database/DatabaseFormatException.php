<?php

namespace database;

use Exception;
use Throwable;

/**
 * Error class used for format errors on Database class.
 */
class DatabaseFormatException extends Exception {

    /** Error class constructor.
     * @param string $message message to display with the error
     * @param int $code code of the error
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}