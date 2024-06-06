<?php
namespace api\database\record;

require_once __DIR__."/../../../autoload.php";
use api\database\DatabaseService;
use database\DatabaseFormatException;
use InvalidArgumentException;
use libs\ApiLib;
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
    public function CheckParameters()
    {
        if (!$this->database->connection->dbname) {
            ApiLib::WriteErrorResponse(
                400,
                "No database selected - please check database credential files."
            );
        }
    }

    public function GET()
    {
        try {
            $data = $this->database->SelectRecord($this->paramValues->columns, $this->paramValues->table, $this->paramValues->where);
            ApiLib::WriteResponse($data);
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            ApiLib::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            ApiLib::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }

    }
    public function POST(){
        try {
            $last_inserted_id = $this->database->AddRecord($this->paramValues->table, $this->paramValues->values);
            ApiLib::WriteResponse(
                ["last_inserted_id"=>$last_inserted_id]
            );
        } catch (InvalidArgumentException $e) {
            ApiLib::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            ApiLib::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }
    public function PUT(){
        try {
            $affected_rows = $this->database->UpdateRecord($this->paramValues->table, $this->paramValues->values, $this->paramValues->where);
            ApiLib::WriteResponse(
                ["affected_rows"=>$affected_rows]
            );
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            ApiLib::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            ApiLib::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }
    public function DELETE(){
        try {
            $affected_rows = $this->database->DeleteRecord($this->paramValues->table, $this->paramValues->where);
            ApiLib::WriteResponse(
                ["affected_rows"=>$affected_rows]
            );
        } catch (InvalidArgumentException|DatabaseFormatException $e) {
            ApiLib::WriteErrorResponse(
                400,
                $e->getMessage()
            );
        } catch (PDOException $e) {
            ApiLib::WriteErrorResponse(
                400,
                "PDO Exception - ".$e->getMessage()
            );
        }
    }

    /**
     * @return void
     */
    public function PATCH()
    {
        // TODO: Implement PATCH() method.
    }
}