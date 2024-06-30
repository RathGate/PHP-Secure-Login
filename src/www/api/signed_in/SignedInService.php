<?php

namespace api\signed_in;
use api\database\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authorizer\JWT;
use libs\authorizer\Tokenizer;

class SignedInService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["GET"])
    {
        $this->optionParams = [
            "GET"=>["webservice"]
        ];
        parent::__construct($allowed_verbs);
    }

    protected function CheckParameters()
    {
        if (isset($this->paramValues->webservice)) {
            $ws = $this->database->SelectRecord(["*"], "webservices", ["name", "=", $this->paramValues->webservice])[0] ?? null;

            if (!isset($ws)) {
                Api::WriteErrorResponse(403, "Le webservice fourni n'existe pas");
            }
            $this->paramValues->webservice = $ws;
        }
    }

    public function GET(): void
    {
        // Retrieve token
        $token = Tokenizer::RetrieveAuthorizationToken();
        if (!isset($token)) {
            Api::WriteErrorResponse(401, "Aucun token d'authentification n'a été fourni");
        }

        $token_data = Tokenizer::GetSessionToken($this->database, $token);
        // Check token validity
        if (!isset($token_data) || $token_data["has_expired"]) {
            Api::WriteErrorResponse(401, "Le token fourni est expiré ou invalide");
        }
        
        // Check associated user
        if (!Authenticator::GetUserAccountByUUID($this->database, $token_data["token_data"]["user_uuid"])) {
            Api::WriteErrorResponse(401, "Aucun compte valide ne correspond au token d'authentification fourni");
        }

        // Todo: service

        $data = ["token"=> ["session_token"=> $token,"token_type"=> "Bearer"]];
        if (isset($this->paramValues->webservice)) { $data["webservice"] = $this->paramValues->webservice["name"]; }
        Api::WriteSuccessResponse($data);
    }
}