<?php
namespace api\sign_out;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authorizer\Tokenizer;


class SignOut extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs)
    {
        $this->optionParams = [
            "GET"=>["all"]
        ];
        parent::__construct($allowed_verbs);
    }

    public function GET(): void
    {
        // Retrieve token
        $token = Tokenizer::RetrieveAuthorizationToken();
        echo $token;
        if (!isset($token)) {
            Api::WriteSuccessResponse(null);
        }

        if (isset($this->paramValues->all)) {
            Tokenizer::RevokeAllUserSessionTokens($this->database, null, $token);
        } else {
            Tokenizer::RevokeSessionToken($this->database, $token);
        }
        Api::WriteSuccessResponse(null);
    }
}
