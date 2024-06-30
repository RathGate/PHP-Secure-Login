<?php
namespace api\sign_out;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authorizer\Tokenizer;


class SignOutService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs)
    {
        $this->optionParams = [
            "POST"=>["all"]
        ];
        parent::__construct($allowed_verbs);
    }

    public function POST(): void
    {
        // Retrieves token
        // Todo : Implement it in Service constructor ?
        $token = Tokenizer::RetrieveAuthorizationToken();

        // If no token, nothing to do
        if (!isset($token)) {
            Api::WriteSuccessResponse(null);
        }

        // Revokes current or all tokens
        if (isset($this->paramValues->all)) {
            Tokenizer::RevokeAllUserSessionTokens($this->database, null, $token);
        } else {
            Tokenizer::RevokeSessionToken($this->database, $token);
        }

        // Sends response
        Api::WriteSuccessResponse(null);
    }
}
