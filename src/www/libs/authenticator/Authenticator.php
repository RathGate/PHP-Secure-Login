<?php

namespace libs\authenticator;

use database\Database;
use libs\Cryptographics;
use libs\Format;

class Authenticator
{
    static function RegisterUserAccount(Database $db, $email, $password): string
    {
        // Sanitize email
        $email = strtolower($email);

        // Check formats for email & password
        if (!Format::IsValidEmail($email) || !Format::IsValidPassword($password)) {
            return -1;
        }

        // Generate password
        $account_info = Cryptographics::GenerateSecurePassword($password);

        try {
            // Add email to `users` table and retrieve created uuid
            $user_id = $db->AddRecord("users", ["email" => $email]);
            $user_uuid = $db->SelectRecord(["uuid"], "users", array("id", "=", $user_id))[0]["uuid"] ?? null;

            // Add password info to `user_accounts_temp`
            $account_info["user_uuid"] = $user_uuid;
            $db->AddRecord("user_accounts_tmp", $account_info);
        } catch (\Exception $e) {
            $db->DeleteRecord("users", array("id", "=", $user_id));
            throw $e;
        }

        return $account_info["user_uuid"];
    }

    static function VerifyUserAccount(Database $db, string $user_uuid)
    {
        $is_verified = isset($db->SelectRecord("*", "user_accounts", array("user_uuid", "=", $user_uuid))[0]);
        if ($is_verified) {
            echo "User already verified";
            return;
        }

        $temp_user = $db->SelectRecord(["user_uuid", "password", "salt", "stretch"], "user_accounts_tmp", array("user_uuid", "=", $user_uuid))[0] ?? null;
        if (!isset($temp_user) || sizeof($temp_user) == 0) {
            echo "Wrong user UUID or inexistant user";
            return;
        }

        try {
            $db->AddRecord("user_accounts", $temp_user);
        } catch (\PDOException $e) {
            $db->DeleteRecord("user_accounts", array("user_uuid", "=", $temp_user["user_uuid"]));
            throw $e;
        }
        $db->DeleteRecord("user_accounts_tmp", array("user_uuid", "=", $temp_user["user_uuid"]));
        return $temp_user["uuid"];
    }

    static function GetUserInfoByEmail(Database $db, $email) {
        return $db->SelectRecord("*", "users", array("email", "=", $email))[0] ?? null;
    }

    static function IsVerifiedUserAccount($db, $user_uuid) {
        return isset($db->SelectRecord("*", "user_accounts", array("user_uuid", "=", $user_uuid))[0]);
    }
    static function GetUserAccountByUUID(Database $db, $user_uuid) {
        return $db->SelectRecord("*", "user_accounts", array("user_uuid", "=", $user_uuid))[0] ?? null;
    }

    static function ValidatePassword(Database $db, $user_uuid, $password):array {
        $result = array("is_validated" =>false);
        $user_account = self::GetUserAccountByUUID($db, $user_uuid);
        if (!isset($user_account)) {
            $result["err"] = "Account does not exist";
            return $result;
        }

        if (!Cryptographics::MatchSecurePassword($password, $user_account["password"], $user_account["salt"], $user_account["stretch"])) {
            $db->AddRecord("user_connection_attempts", array("user_uuid"=>$user_uuid));


            $result["err"] = "Password do not match";
            return $result;
        }
        $result["is_validated"] = true;
        return $result;
    }
}