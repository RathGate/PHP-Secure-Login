<?php
namespace api;
use api\Service;
use database\Database;
use libs\ApiLib;
use PDOException;

// Extends Service to include database connection.
abstract class DatabaseService extends Service
{
    protected Database $database;

    public function __construct($allowed_verbs=["GET"])
    {
        try {
            $this->database = new Database();
        } catch (PDOException $e) {
            ApiLib::WriteErrorResponse(500, "Database error : could not establish connection to database.");
        }
        // Parent constructor
        parent::__construct($allowed_verbs);
    }
}