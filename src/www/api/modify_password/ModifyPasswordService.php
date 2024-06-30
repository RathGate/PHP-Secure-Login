<?php
namespace api\modify_password;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authenticator\SecuredActioner;
use libs\Format;
use libs\templator\MailTemplator;


class ModifyPasswordService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs)
    {
        $this->requiredParams = [
            "GET"=>["email"],
            "PUT"=>["email", "new_password", "otp"]
        ];
        parent::__construct($allowed_verbs);
    }

    public function CheckParameters(): void
    {
        // Retrieves user uuid and checks if user exists
        $this->paramValues->user_uuid = Authenticator::GetUserByEmail($this->database, $this->paramValues->email)["uuid"] ?? null;

        if (!isset($this->paramValues->user_uuid)) {
            Api::WriteErrorResponse(401, "Aucun compte n'est associé à cette adresse email");
        }
    }

    public function GET(): void
    {
        // Generates OTP
        $otp = SecuredActioner::RegisterOTP($this->database, $this->paramValues->user_uuid, $this->serviceName);

        // Sends mail
        $message = "Un email de confirmation contenant un code de vérification a été envoyé à l'adresse '".$this->paramValues->email."'.";
        $mail = MailTemplator::GenerateOTPVerificationEmail($this->paramValues->email, $otp);
        Api::WriteResponse(true, 201, $mail, $message);
    }

    public function PUT(): void
    {
        // Checks if new password format is valid
        if (!Format::IsValidPassword($this->paramValues->new_password)) {
            Api::WriteErrorResponse(400, "Le mot de passe spécifié n'est pas assez fort (minuscule, majuscule, symbole, chiffre et > 8 caractères).");
        }

        // Checks OTP validity
        $otp_validation = SecuredActioner::ValidateOTP($this->database, $this->paramValues->otp, $this->paramValues->user_uuid, $this->serviceName);
        if (!$otp_validation["is_validated"]) {
            Api::WriteErrorResponse(401, $otp_validation["err"]);
        }

        // Modifies password and sends response
        Authenticator::ModifyUserPassword($this->database, $this->paramValues->user_uuid, $this->paramValues->new_password);
        Api::WriteResponse(true, 201, "Mot de passe modifié avec succès");
    }
}
