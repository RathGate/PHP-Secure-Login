<?php

namespace libs\templator;

use libs\authenticator\SecuredActioner;

class MailTemplator
{
    static string $from = "admin@securitytp1.com";
    static string $website = "securitytp1.com";

    static function GenerateAccountVerificationEmail(string $user_email, $otp): array
    {
//        $link = "http://localhost/login/verify_account/?key=".SecuredActioner::GenerateOTPLink($user_email, $otp);
        $subject = "Vérification de votre adresse mail";
        $content = [
            "Vous devez vérifier votre adresse mail pour compléter votre inscription à <b>".self::$website."</b><br />".
            "Pour ce faire, vous pouvez utiliser le code suivant : <br /><br />".
            $otp
//            ."<br /><br />Alternativement, vous pouvez cliquer sur le lien suivant :<br />".
//            $link
        ];

        return [
            "from"=>self::$from,
            "to"=>$user_email,
            "subject"=>$subject,
            "content"=>$content
        ];
    }

    static function GenerateOTPVerificationEmail(string $user_email, $otp): array
    {
        $subject = "Code de vérification : ".$otp;
        $content = [
            "Une action sécurisée a été réalisée sur votre compte, nécessitant un code de vérification.<br />".
            "Votre code de vérification est <br /><br />".
            $otp
            ."<br /><br />Si vous n'êtes pas à l'origine de cette action, votre compte peut être compromis : il est conseillé de modifier votre mot de passe."
        ];

        return [
            "from"=>self::$from,
            "to"=>$user_email,
            "subject"=>$subject,
            "content"=>$content
        ];
    }
}