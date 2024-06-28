<?php

namespace api\verify_account;
use api\database\DatabaseService;
use libs\Api;
use libs\authenticator\Authenticator;
use libs\authenticator\SecuredActioner;
use libs\authorizer\JWT;
use libs\Format;

class VerifyAccountService extends DatabaseService
{
    // Surcharge Service.__construct() pour ajouter le traitement spécifique de la requête.
    public function __construct($allowed_verbs=["POST"])
    {
        $this->requiredParams = [
            "GET"=>["email"],
            "POST"=>["email", "otp"]
        ];
        $this->optionParams = [];

        parent::__construct($allowed_verbs);
    }

    protected function CheckParameters(): void
    {
//        if (isset($this->paramValues->key) && $this->paramValues->key != "") {
//            $jwt = new JWT();
//            try {
//                $payload = $jwt->Decode($this->paramValues->key);
//                $this->paramValues->user_uuid = $payload["user"];
//                $this->paramValues->otp = $payload["otp"];
//            } catch (\Exception $e) {
//                Api::WriteErrorResponse(404, "Lien invalide ou expiré");
//            }
//        }

        if (isset($this->paramValues->email) && $this->paramValues->email != "") {
            $this->paramValues->user_uuid = Authenticator::GetUserInfoByEmail($this->database, $this->paramValues->email)["user_uuid"] ?? null;
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
        $otp = SecuredActioner::RegisterOTP($this->database, $this->paramValues->user_uuid, "SignUp");

        $message = "Un email de confirmation a été envoyé à l'adresse '".$this->paramValues->email."'.";
        $data = array("warning"=>"// Ceci n'apparaît que dans le mail de confirmation. //");
        $data["otp"] = $otp;
//        $data["link"] = "http://localhost/login/api/verify_account/?key=".SecuredActioner::GenerateOTPLink($this->paramValues->user_uuid, $data["otp"]);
        Api::WriteResponse(true, 201, $data, $message, true);

//        if (isset($this->paramValues->email)) {
//            $message = "Un email de confirmation a été envoyé à l'adresse '".$this->paramValues->email."'.";
//            $data = array("warning"=>"// Ceci n'apparaît que dans le mail de confirmation. //");
//            $data["otp"] = SecuredActioner::RegisterOTP($this->database, $this->paramValues->user_uuid, "SignUp");
//            $data["link"] = "http://localhost/login/api/verify_account/?key=".SecuredActioner::GenerateOTPLink($this->paramValues->user_uuid, $data["otp"]);
//            Api::WriteResponse(true, 201, $data, $message, true);
//        }
//
//        Api::WriteErrorResponse(400, "Lien invalide");
    }

    public function POST():void {
        $otp_validation = SecuredActioner::ValidateOTP($this->database, $this->paramValues->otp, $this->paramValues->user_uuid, "SignUp");
        if (!$otp_validation["is_validated"]) {
            Api::WriteErrorResponse(401, !$otp_validation["err"]);
        }
        Authenticator::VerifyUserAccount($this->database, $this->paramValues->user_uuid);
    }
}