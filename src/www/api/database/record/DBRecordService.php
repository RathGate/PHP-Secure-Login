<?php
namespace api\database\record;

require_once __DIR__."/../../../autoload.php";
use api\database\DatabaseService;
use database\DatabaseFormatException;
use InvalidArgumentException;
use libs\Api;
use PDOException;

class DBRecordService extends DatabaseService {

    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["GET"])
    {
        $this->requiredParams = [
            "GET"=>["table"],
            "POST"=>["table", "values"],
            "PUT"=>["table", "values"],
            "DELETE"=>["table", "where"]
        ];
        $this->optionParams = [
            "GET"=>["columns", "where"],
            "POST"=>[],
            "PUT"=>["where"],
            "DELETE"=>[]
        ];
        parent::__construct($allowed_verbs);
    }

    // Renvoie l'erreur en réponse et termine le script si un paramètre est invalide.
    public function CheckParameters(): void
    {
        if (!$this->database->connection->dbname) {
            Api::WriteErrorResponse(
                400,
                "No database selected - please check database credential files."
            );
        }
    }

    public function GET(): void
    {
        try {
            $data = $this->database->SelectRecord($this->paramValues->columns, $this->paramValues->table, $this->paramValues->where);
            Api::WriteSuccessResponse($data);
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            Api::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            Api::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }

    }
    public function POST(): void
    {
        try {
            $last_inserted_id = $this->database->AddRecord($this->paramValues->table, $this->paramValues->values);
            Api::WriteSuccessResponse(
                ["last_inserted_id"=>$last_inserted_id]
            );
        } catch (InvalidArgumentException $e) {
            Api::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            Api::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }
    public function PUT(): void
    {
        try {
            $affected_rows = $this->database->UpdateRecord($this->paramValues->table, $this->paramValues->values, $this->paramValues->where);
            Api::WriteSuccessResponse(
                ["affected_rows"=>$affected_rows]
            );
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            Api::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            Api::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }
    public function DELETE(): void
    {
        try {
            $affected_rows = $this->database->DeleteRecord($this->paramValues->table, $this->paramValues->where);
            Api::WriteSuccessResponse(
                ["affected_rows"=>$affected_rows]
            );
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            Api::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            Api::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }

}