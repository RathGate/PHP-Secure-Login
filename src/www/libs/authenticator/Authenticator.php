<?php

namespace libs\authenticator;

use database\Database;
use libs\Cryptographics;
use libs\Format;

class Authenticator
{
//    static function GetUserByUUID(Database $db, $user_uuid)

    static function RegisterUserAccount(Database $db, $email, $password): string
    {
        $email = strtolower($email);
        if (!Format::IsValidEmail($email) || !Format::IsValidPassword($password)) {
            return -1;
        }
        $test = Cryptographics::GenerateSecurePassword($password);
        $temp_user_id = $db->AddRecord("temp_users", $test);

        // TODO : Exception
        if ($temp_user_id < 0) {
            return -1;
        }

        $temp_user_uuid = $db->SelectRecord(["uuid"], "temp_users", array("id", "=", $temp_user_id))[0]["uuid"] ?? null;
        echo $temp_user_uuid;

        try {
            $db->AddRecord("user_info", array("user_uuid" => $temp_user_uuid, "email" => $email));
        } catch (\PDOException $e) {
            $db->DeleteRecord("temp_users", array("uuid", "=", $temp_user_uuid));
            throw $e;
        }
        return $temp_user_uuid;
    }

//    static function GetUserByUUID

    static function VerifyUserAccount(Database $db, string $user_uuid)
    {
        $is_verified = isset($db->SelectRecord("*", "users", array("uuid", "=", $user_uuid))[0]);
        if ($is_verified) {
            echo "User already verified";
            return;
        }

        $temp_user = $db->SelectRecord(["uuid", "password", "salt", "stretch"], "temp_users", array("uuid", "=", $user_uuid))[0] ?? null;
        if (!isset($temp_user) || sizeof($temp_user) == 0) {
            echo "Wrong user UUID or inexistant user";
            return;
        }

        try {
            $db->AddRecord("users", $temp_user);
        } catch (\PDOException $e) {
            $db->DeleteRecord("users", array("uuid", "=", $temp_user["uuid"]));
            throw $e;
        }
        $db->DeleteRecord("temp_users", array("uuid", "=", $temp_user["uuid"]));
        return $temp_user["uuid"];
    }

    static function GetUserInfoByEmail(Database $db, $email) {
        return $db->SelectRecord("*", "user_info", array("email", "=", $email))[0] ?? null;
    }
    static function GetUserAccount($db, $filters=[]) {
        $db->SelectRecord("*", "temp_users");
    }

    static function IsVerifiedUserAccount($db, $user_uuid) {
        return isset($db->SelectRecord("*", "users", array("uuid", "=", $user_uuid))[0]);
    }
}