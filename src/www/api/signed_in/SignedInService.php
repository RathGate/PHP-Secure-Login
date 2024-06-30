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

        $token_data = Tokenizer::GetUserSession($this->database, $token);
        // Check token validity
        if (!isset($token_data) || $token_data["has_expired"]) {
            Api::WriteErrorResponse(401, "Le token fourni est expiré ou invalide");
        }
        
        // Check associated user
        $user = Authenticator::GetUserByUUID($this->database, $token_data["token_data"]["user_uuid"]);
        if (!isset($user)) { Api::WriteErrorResponse(401, "Aucun compte valide correspondant" ); }



        if (isset($this->paramValues->webservice)) {
            $user_role_level = Authenticator::GetUserRole($this->database, $user["role_id"])["level"] ?? null;
            $ws_role_level = $this->paramValues->webservice["permission_level"] ?? null;

            echo $user_role_level;
            echo $ws_role_level;

            if ((!isset($user_role_level) && isset($ws_role_level)) ||
                (isset($user_role_level) && isset($ws_role_level) && $user_role_level > $ws_role_level)) {
                 Api::WriteErrorResponse(401, "Permissions insuffisantes" );
            }
        }

        $data = [
            "token"=> ["session_token"=> $token,"token_type"=> "Bearer"],
            "webservice"=> $this->paramValues->webservice["name"] ?? false];
        Api::WriteSuccessResponse($data);
    }
}