<?php
namespace api\sign_in;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authorizer\Tokenizer;


class SignIn extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["POST"])
    {
        $this->requiredParams = [
            "POST"=>["email", "password"]
        ];
        $this->optionParams = [];
        parent::__construct($allowed_verbs);
    }

    public function CheckParameters(): void
    {
        $this->paramValues->user_uuid = Authenticator::GetUserInfoByEmail($this->database, $this->paramValues->email)["uuid"] ?? null;
        // Checks if email exists
        if (!isset($this->paramValues->user_uuid)) {
            Api::WriteErrorResponse(401, "Aucun compte n'a été trouvé pour l'adresse mail fournie.");
        }

        // Checks if account is verified
        if (!Authenticator::IsVerifiedUserAccount($this->database, $this->paramValues->user_uuid)) {
            Api::WriteErrorResponse(401, "L'utilisateur doit vérifier son adresse mail avant de pouvoir se connecter.");
        }
    }

    public function POST(): void
    {
        $authentication = Authenticator::ValidatePassword($this->database, $this->paramValues->user_uuid, $this->paramValues->password);
        if (!$authentication["is_validated"]) {
            Api::WriteErrorResponse(401, "L'email ou le mot de passe ne sont pas corrects");
            return;
        }

        try {
            $token = Tokenizer::GenerateSessionToken($this->database, $this->paramValues->user_uuid);
            Api::WriteSuccessResponse($token);
        } catch (\Exception $e) {
            Api::WriteErrorResponse(500, "Une erreur serveur est survenue");
        }
    }
}
