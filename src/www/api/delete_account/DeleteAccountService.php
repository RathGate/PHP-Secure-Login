<?php
namespace api\delete_account;
require_once __DIR__."/../../autoload.php";
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authenticator\SecuredActioner;
use libs\Format;
use libs\templator\MailTemplator;


class DeleteAccountService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs)
    {
        $this->requiredParams = [
            "GET"=>["email", "password"],
            "DELETE"=>["email", "otp"]
        ];
        $this->optionParams = [];
        parent::__construct($allowed_verbs);
    }

    public function CheckParameters(): void
    {
        $this->paramValues->user_uuid = Authenticator::GetUserByEmail($this->database, $this->paramValues->email)["uuid"] ?? null;

        // Checks if email exists
        if (!isset($this->paramValues->user_uuid)) {
            Api::WriteErrorResponse(401, "Aucun compte n'est associé à cette adresse email");
        }
        if (!Authenticator::IsVerifiedUserAccount($this->database, $this->paramValues->user_uuid)) {
            Api::WriteErrorResponse(401, "L'adresse mail doit être vérifiée pour supprimer le compte");
        }
    }

    public function GET(): void
    {
        if (!Authenticator::IsValidPassword($this->database,$this->paramValues->user_uuid, $this->paramValues->password)) {
            Api::WriteErrorResponse(401, "L'email ou le mot de passe fourni est incorrect");
        }

        // OTP
        $otp = SecuredActioner::RegisterOTP($this->database, $this->paramValues->user_uuid, $this->serviceName);

        // Mail
        $message = "Un email de confirmation contenant un code de vérification a été envoyé à l'adresse '".$this->paramValues->email."'.";
        $mail = MailTemplator::GenerateOTPVerificationEmail($this->paramValues->email, $otp);
        Api::WriteResponse(true, 201, $mail, $message);
    }

    public function DELETE(): void
    {
        $otp_validation = SecuredActioner::ValidateOTP($this->database, $this->paramValues->otp, $this->paramValues->user_uuid, $this->serviceName);
        if (!$otp_validation["is_validated"]) {
            Api::WriteErrorResponse(401, $otp_validation["err"]);
        }

        Authenticator::DeleteUser($this->database, $this->paramValues->user_uuid);
        Api::WriteResponse(true, 201, "Compte et données associées supprimés avec succès");
    }
}
