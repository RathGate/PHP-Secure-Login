<?php

namespace api\signed_in;
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
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

    protected function CheckParameters(): void
    {
        // Retrieves webservice from database
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
        // Retrieves token
        // Todo : Implement it in Service constructor
        $token = Tokenizer::RetrieveAuthorizationToken();
        if (!isset($token)) {
            Api::WriteErrorResponse(401, "Aucun token d'authentification n'a été fourni");
        }

        // Retrieves user session from database linked to token
        $token_data = Tokenizer::GetUserSession($this->database, $token);
        // Checks token validity
        if (!isset($token_data) || $token_data["has_expired"]) {
            Api::WriteErrorResponse(401, "Le token fourni est expiré ou invalide");
        }
        
        // Checks associated user
        // Todo: Is this useless since `user_session` references `user`.`uuid` NOT NULL ?
        $user = Authenticator::GetUserByUUID($this->database, $token_data["token_data"]["user_uuid"]);
        if (!isset($user)) { Api::WriteErrorResponse(401, "Aucun compte valide correspondant" ); }

        // If webservice parameter is set, checks permissions to access it
        if (isset($this->paramValues->webservice)) {
            $user_role_level = Authenticator::GetUserRole($this->database, $user["role_id"])["permission_level"] ?? null;
            $ws_role_level = $this->paramValues->webservice["permission_level"] ?? null;

            if ((!isset($user_role_level) && isset($ws_role_level)) ||
                (isset($user_role_level) && isset($ws_role_level) && $user_role_level > $ws_role_level)) {
                 Api::WriteErrorResponse(401, "Permissions insuffisantes" );
            }
        }

        // Writes response
        $data = [
            "token"=> ["session_token"=> $token,"token_type"=> "Bearer"],
            "webservice"=> $this->paramValues->webservice["name"] ?? false];
        Api::WriteSuccessResponse($data);
    }
}