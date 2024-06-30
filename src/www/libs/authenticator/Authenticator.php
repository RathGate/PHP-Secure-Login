<?php

namespace libs\authenticator;

use database\Database;
use libs\authorizer\AThrottleLimiter;
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
            // If something goes wrong, deletes half-created user
            $db->DeleteRecord("users", array("id", "=", $user_id));
            throw $e;
        }

        return $account_info["user_uuid"];
    }

    static function VerifyUserAccount(Database $db, string $user_uuid)
    {
        $is_verified = isset($db->SelectRecord("*", "user_accounts", array("user_uuid", "=", $user_uuid))[0]);
        if ($is_verified) {
            throw new \InvalidArgumentException("L'utilisateur est déjà vérifié");
        }

        $temp_user = $db->SelectRecord(["user_uuid", "password", "salt", "stretch"], "user_accounts_tmp", array("user_uuid", "=", $user_uuid))[0] ?? null;
        if (!isset($temp_user) || sizeof($temp_user) == 0) {
            throw new \InvalidArgumentException("L'utilisateur temporaire n'existe pas");
        }

        try {
            $db->AddRecord("user_accounts", $temp_user);
        } catch (\PDOException $e) {
            $db->DeleteRecord("user_accounts", array("user_uuid", "=", $temp_user["user_uuid"]));
            throw $e;
        }
        $db->DeleteRecord("user_accounts_tmp", array("user_uuid", "=", $temp_user["user_uuid"]));
        return $temp_user["user_uuid"];
    }

    static function GetUserByEmail(Database $db, $email) {
        return $db->SelectRecord("*", "users", array("email", "=", $email))[0] ?? null;
    }

    static function GetUserByUUID(Database $db, $user_uuid) {
        return $db->SelectRecord("*", "users", array("uuid", "=", $user_uuid))[0] ?? null;
    }

    static function GetUserRole(Database $db, $role_id) {
        if (!isset($role_id)) { return null; }
        return $db->SelectRecord("*", "roles", ["id", "=", $role_id])[0] ?? null;
    }

    static function IsVerifiedUserAccount($db, $user_uuid) {
        return isset($db->SelectRecord("*", "user_accounts", array("user_uuid", "=", $user_uuid))[0]);
    }

    static function GetUserAccountByUUID(Database $db, $user_uuid, $table="user_accounts") {
        return $db->SelectRecord("*", $table, array("user_uuid", "=", $user_uuid))[0] ?? null;
    }

    static function IsValidPassword(Database $db, $user_uuid, $password, $table="user_accounts"):bool {
        $user_account = self::GetUserAccountByUUID($db, $user_uuid,$table);
        if (!isset($user_account)) { return false; }
        return Cryptographics::MatchSecurePassword($password, $user_account["password"], $user_account["salt"], $user_account["stretch"]);
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

    static function ModifyUserPassword(Database $db, $user_uuid, $password, $table="user_accounts"): bool
    {
        $pwd_info = Cryptographics::GenerateSecurePassword($password);
        return $db->UpdateRecord($table, $pwd_info, ["user_uuid", "=", $user_uuid]) > 1;
    }

    static function DeleteUser(Database $db, $user_uuid): int
    {
        return $db->DeleteRecord("users", ["uuid","=",$user_uuid]);
    }
}