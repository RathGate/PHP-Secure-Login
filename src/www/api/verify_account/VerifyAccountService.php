<?php

namespace api\verify_account;
use api\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authenticator\SecuredActioner;
use libs\templator\MailTemplator;

class VerifyAccountService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["POST"])
    {
        $this->requiredParams = [
            "GET"=>["email", "password"],
            "POST"=>["email", "otp"]
        ];

        parent::__construct($allowed_verbs);
    }

    protected function CheckParameters(): void
    {
        if (isset($this->paramValues->email) && $this->paramValues->email != "") {
            $this->paramValues->user_uuid = Authenticator::GetUserByEmail($this->database, $this->paramValues->email)["uuid"] ?? null;
            // Checks if email exists
            if (!isset($this->paramValues->user_uuid)) {
                Api::WriteErrorResponse(401, "Aucun compte n'a été trouvé pour l'adresse mail fournie.");
            }

            // Checks if account has already been verified
            if (Authenticator::IsVerifiedUserAccount($this->database, $this->paramValues->user_uuid)) {
                Api::WriteErrorResponse(403, "Le compte associé à l'adresse fournie a déjà été vérifié.");
            }
        }
    }

    public function GET(): void
    {
        // Checks user password
        if (!Authenticator::IsValidPassword($this->database,$this->paramValues->user_uuid, $this->paramValues->password, "user_accounts_tmp")) {
            Api::WriteErrorResponse(401, "L'email ou le mot de passe fourni est incorrect");
        }

        // Generates OTP for user and service
        $otp = SecuredActioner::RegisterOTP($this->database,  $this->paramValues->user_uuid, "SignUp");

        // Writes response
        $message = "Un email de confirmation a été envoyé à l'adresse '".$this->paramValues->email."'.";
        $mail = (MailTemplator::GenerateAccountVerificationEmail($this->paramValues->email, $otp));
        Api::WriteResponse(true, 201, $mail, $message, true);
    }

    public function POST():void {
        // Retrieves and checks OTP
        $otp_validation = SecuredActioner::ValidateOTP($this->database, $this->paramValues->otp, $this->paramValues->user_uuid, "SignUp");
        if (!$otp_validation["is_validated"]) {
            Api::WriteErrorResponse(401, $otp_validation["err"]);
        }

        // Verifies user account
        Authenticator::VerifyUserAccount($this->database, $this->paramValues->user_uuid);
        // Writes response
        Api::WriteResponse(true, 201, null, "Le compte a été vérifié avec succès");
    }
}