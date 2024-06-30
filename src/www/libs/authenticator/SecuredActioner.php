<?php

namespace libs\authenticator;

use database\Database;
use libs\Format;
use libs\JWT;

class SecuredActioner
{
    static int $otp_validity = 300;

    static function GetServiceID(Database $db, string $service): int
    {
        $services = $db->SelectRecord(["id"], "webservices", array("name", "=", $service));
        return $services[0]["id"] ?? -1;
    }

    static function GenerateOTP(int $byteLength = 5): string
    {
        return bin2hex(random_bytes($byteLength));
    }

    static function RegisterOTP(Database $db, string $userUUID, string $service, int $duration_s = null)
    {
        // Get service ID in database :
        $service_id = self::GetServiceID($db, $service);
        if ($service_id < 0) {
            throw new \InvalidArgumentException("Le service `" . $service . "` n'existe pas.");
        }

        // Generate OTP
        $otp = self::GenerateOTP();
        // Generate duration
        $now = new \DateTime();
        $then = clone $now;
        $then->add(new \DateInterval("PT" . ($duration_s ?? self::$otp_validity) . "S"));

        // Delete all OTP associated to user
        self::DeleteAllOTP($db, $userUUID);
        $db->AddRecord("user_otp", array("otp" => $otp, "user_uuid" => $userUUID, "webservice_id" => $service_id,
            "created_at" => Format::DateToStr($now), "expires_at" => Format::DateToStr($then)));
        return $otp;
    }

    static function DeleteAllOTP($db, $user_uuid) {
        $db->DeleteRecord("user_otp", array("user_uuid", "=", $user_uuid));
    }

    static function GetUserOTP(Database $db, string $user_uuid, string $service, $date=null)
    {
        $service_id = self::GetServiceID($db, $service);
        if ($service_id < 0) {
            throw new \InvalidArgumentException("Le service `" . $service . "` n'existe pas.");
        }

        $date = $date ?? Format::DateToStr(new \DateTime());
        // Todo : Needs to be removed when DAL advanced functions are done
        return $db->ExecuteQuery("SELECT * FROM `user_otp` WHERE `user_uuid` = ? AND `webservice_id` = ? AND ? BETWEEN `created_at` AND `expires_at` ORDER BY `created_at` DESC LIMIT 1;", [$user_uuid, $service_id, $date])[0] ?? null;
    }

    static function RegisterOTPAttempt(Database $db, int $otp_id) {
        $db->AddRecord("otp_attempts", array("otp_id"=>$otp_id));
    }

    static function ValidateOTP(Database $db, string $otp, string $user_uuid, string $service) {
        $result = array("is_validated" =>false);
        $now = Format::DateToStr(new \DateTime());

        // Retrieves OTP
        $user_otp = self::GetUserOTP($db, $user_uuid, $service, $now);
        if (!isset($user_otp)) {
            $result["err"] = "Aucun OTP valide n'a été trouvé pour cet utilisateur et ce service";
            return $result;
        }

        // If user try for OTP is not correct :
        if ($otp != $user_otp["otp"]) {
            $attempt_count = sizeof($db->SelectRecord("*", "otp_attempts", array("otp_id", "=", $user_otp["id"])));
            // If too many attempts, OTP is invalidated
            if ($attempt_count >= $user_otp["max_uses"]) {
                self::DeleteAllOTP($db, $user_uuid);
                $result["err"] = "OTP supprimé après trop de tentatives manquées";
                return $result;
            }

            // Else, registers OTP attempts
            self::RegisterOTPAttempt($db, $user_otp["id"]);
            $result["err"] = "OTP invalide";
            return $result;
        }

        // Successful OTP :
        self::DeleteAllOTP($db, $user_uuid);
        $result["is_validated"] = true;
        return $result;
    }

    static function GenerateOTPLink($user_uuid, $otp): string
    {
        $jwt = new JWT();
        return $jwt->Encode(["user"=>$user_uuid, "otp"=>$otp]);
    }
}