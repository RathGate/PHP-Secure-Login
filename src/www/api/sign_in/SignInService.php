<?php
namespace api\sign_in;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authorizer\LoginAttemptsLimiter;
use libs\authorizer\Tokenizer;


class SignInService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["POST"])
    {
        $this->requiredParams = [
            "GET"=>["email"],
            "POST"=>["email", "password"]
        ];
        parent::__construct($allowed_verbs);
    }

    public function CheckParameters(): void
    {
        $this->paramValues->user_uuid = Authenticator::GetUserByEmail($this->database, $this->paramValues->email)["uuid"] ?? null;

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
        // Initialize login attempt limiter
        // Todo: Implement a rate limiter directly on Service or DatabaseService ?
        $limiter = new LoginAttemptsLimiter($this->database, $this->paramValues->user_uuid);

        // Brute force check
        if($limiter->HasReachedLimit()) {
            Api::WriteErrorResponse(401, "Limite de tentatives de connexion atteinte ; veuillez réessayer plus tard");
        };

        // Checks if password is correct
        $authentication = Authenticator::ValidatePassword($this->database, $this->paramValues->user_uuid, $this->paramValues->password);
        if (!$authentication["is_validated"]) {
            // Todo: Implement rate limiter inside ValidatePassword ?
            if($limiter->HasReachedLimit()) {
                Api::WriteErrorResponse(401, "Limite de tentatives de connexion atteinte ; veuillez réessayer plus tard");
            };
            Api::WriteErrorResponse(401, "L'email ou le mot de passe ne sont pas corrects");
            return;
        }

        // Generates session token and sends response
        $token = Tokenizer::GenerateSessionToken($this->database, $this->paramValues->user_uuid);
        Api::WriteSuccessResponse($token);
    }
}
