<?php
namespace api\sign_up;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\Format;


class SignUpService extends DatabaseService
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
        // Wrong email format
        if (!Format::IsValidEmail($this->paramValues->email)) {
            Api::WriteErrorResponse(400, "L'email spécifié est incorrect.");
        }
        // Wrong password
        if (!Format::IsValidPassword($this->paramValues->password)) {
            Api::WriteErrorResponse(400, "Le mot de passe spécifié n'est pas assez fort (minuscule, majuscule, symbole, chiffre et > 8 caractères).");
        }
    }

    public function POST(): void
    {
        echo $this->serviceName;
        $temp_id = Authenticator::RegisterUserAccount($this->database, $this->paramValues->email, $this->paramValues->password);


    }
}
