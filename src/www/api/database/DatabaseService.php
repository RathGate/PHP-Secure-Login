<?php
namespace api\database;
use api\Service;
use database\Database;
use libs\Api;
use PDOException;


// Cette classe est divisée de DBRecordService à des fins de scalabilités, pour faciliter l'implémentation de nouveaux
// endpoints.
abstract class DatabaseService extends Service
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    protected Database $database;

    public function __construct($allowed_verbs=["GET"])
    {
        try {
            $this->database = new Database();
        } catch (PDOException $e) {
            Api::WriteErrorResponse(500, "Database error : could not establish connection to database.");
        }
        parent::__construct($allowed_verbs);
    }
}