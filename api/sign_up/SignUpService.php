<?php
namespace api\sign_up;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;

class SignUpService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["POST"])
    {
        $this->requiredParams = [
            "GET"=>["hello"],
            "POST"=>["email", "password"]
        ];
        $this->optionParams = [];
        parent::__construct($allowed_verbs);
    }
    public function CheckParameters()
    {
        // TODO: Implement CheckParameters() method.
    }

    public function POST(): void
    {
        echo "hi from post !";
    }
    public function GET(): void
    {
        echo "hi from get!";
    }
}