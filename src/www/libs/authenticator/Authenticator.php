<?php

namespace libs\authenticator;

use database\Database;
use libs\Cryptographics;
use libs\Format;

class Authenticator
{

    static function RegisterUserAccount(Database $db, $email, $password):int {
        if (!Format::IsValidEmail($email) || !Format::IsValidPassword($password)) {
            return -1;
        }
        $test = Cryptographics::GenerateSecurePassword($password);
        $temp_user_id = $db->AddRecord("temp_users", $test);

        if ($temp_user_id < 0) {
            return -1;
        }

        try {
            $db->AddRecord("temp_user_info", array("temp_user_id"=>$temp_user_id, "email"=>$email));
        } catch (\PDOException $e) {
            $db->DeleteRecord("temp_users", array("id", "=", $temp_user_id));
            throw $e;
        }
        return $temp_user_id;
    }
    static function ConfirmUserAccount(int $temp_user_) {

    }
}