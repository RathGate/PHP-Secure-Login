<?php

namespace libs\authenticator;

use api\Service;
use database\Database;

class SecuredActioner
{
    static function GetServiceID(Database $db, string $service):int {
        $services = $db->SelectRecord(["id"], "webservices", array("name", "=", $service));
        return $services[0]["id"] ?? -1;
    }

    static function GenerateOTP(int $byteLength=5):string {
        return bin2hex(random_bytes($byteLength));
    }

    static function RegisterOTP(Database $db, string $userUUID="hello", string $service)
    {
        // Get service ID in database :
        $service_id = self::GetServiceID($db, $service);
        echo $service_id." ".$service;
        if ($service_id < 0) {
            echo "Service is not existant in database";
            return null;
        }
        $otp = self::GenerateOTP();

        $db->DeleteRecord("user_otp", array("user_uuid", "=", $userUUID));
        $db->AddRecord("user_otp", array("otp"=>$otp, "user_uuid"=>$userUUID, "webservice_id"=>$service_id));

        return $otp;
    }
}