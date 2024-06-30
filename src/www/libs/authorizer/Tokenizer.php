<?php

namespace libs\authorizer;

use Cassandra\Date;
use database\Database;
use libs\Format;
use mysql_xdevapi\Exception;
use security\Credentials;

class Tokenizer
{
    // Time interval with DateInterval format `PxYxMxDTxHxMxS`
    // https://www.php.net/manual/en/dateinterval.construct.php
    static string $AccessTokenDuration = "PT15M";
    static string $RefreshTokenDuration = "P30D";
    static string $SessionTokenDuration = "P1D";

    static function GenerateJWTToken($payload, string|null $duration): string
    {
        if (!isset($payload) || $payload == null) {
            throw new Exception("Payload cannot be empty");
        }
        $time = ["iat" => (new \DateTime())->getTimestamp()];

        if (isset($duration)) {
            $interval = new \DateInterval($duration);
            $time["exp"] = ((new \DateTime())->add($interval))->getTimestamp();

            if ($time["iat"] > $time["exp"]) {
                throw new \Exception("Token duration cannot be null or negative");
            }
        }

        return (new JWT())->Encode(array_merge($payload, $time));
    }

    static function GenerateAccessToken($user_uuid): string|array
    {
        $token = self::GenerateJWTToken(["user_uuid"=>$user_uuid], self::$AccessTokenDuration);
        return [
            "access_token"=> $token,
            "token_type"=> "Bearer",
            "expires_in"=>date_create('@0')->add(new \DateInterval(self::$AccessTokenDuration))->getTimestamp(),
        ];
    }

    static function GenerateRefreshToken($user_uuid, $generate_access=true): string|array
    {
        $token = self::GenerateJWTToken(["user_uuid"=>$user_uuid], self::$RefreshTokenDuration);
        if ($generate_access) {
            $result = self::GenerateAccessToken($user_uuid);
            $result["refrsh_token"] = $token;
            return $result;
        }

        return [
            "refresh_token"=> $token,
            "token_type"=> "Bearer",
            "expires_in"=>date_create('@0')->add(new \DateInterval(self::$RefreshTokenDuration))->getTimestamp(),
        ];
    }

    static function DecifferToken(string $token) {
        $payload = (new JWT())->Decode($token);
        $has_expired = false;

        try {
            $iat = isset($payload['iat']) ? new \DateTime("@".$payload['iat']) : null;
            $exp = isset($payload['exp']) ? new \DateTime("@".$payload['exp']) : null;

            // Malformated token
            if (isset($iat) && isset($exp) && $exp <= $iat) {
                throw new \Exception();
            }

            if (isset($exp) && new \DateTime() > $exp) {
                $has_expired = true;
            }

        } catch (Exception $e) {
            throw new \Exception("Invalid token format");
        }

        return [
            "payload"=>$payload,
            "has_expired"=>$has_expired
        ];
    }

    static function RetrieveAuthorizationToken(): string|null {
        $authorization = getallheaders()["Autorization"] ?? null;

        if (!isset($authorization)) {
            return null;
        }
        $matches = array();

        if (preg_match('/Bearer (.+)/', $authorization, $matches) && isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    static function GenerateSessionToken(Database $db, string $user_uuid) {
        $token = bin2hex(random_bytes(32));

        $iat = Format::DateToStr(new \DateTime());
        $exp = Format::DateToStr(((new \DateTime())->add(new \DateInterval(self::$SessionTokenDuration))));
        $val = date_create('@0')->add(new \DateInterval(self::$SessionTokenDuration))->getTimestamp();

        $last_inserted_id = $db->AddRecord("session_tokens", array("user_uuid"=>$user_uuid, "token"=>$token, "created_at"=>$iat,
            "expires_at"=>$exp, "validity"=>$val));
        $db->DeleteRecord("session_tokens", [["user_uuid", "=", $user_uuid], "AND", ["id", "!=", $last_inserted_id]]);

        return [
            "session_token"=> $token,
            "token_type"=> "Bearer",
            "expires_in"=> $val
        ];
    }

    static function RevokeSessionToken(Database $db, string $token) {
        $db->DeleteRecord("session_tokens", ["token", "=", $token]);
    }

    static function RevokeAllUserSessionTokens(Database $db, string $user_uuid=null, string $token=null) {
        if (!isset($user_uuid) && !isset($token)) { return; }
        if (!isset($user_uuid)) {
            $token_data = self::GetSessionToken($db, $token);
            if (!isset($token_data)) { return; }
            $user_uuid = $token_data["token_data"]["user_uuid"];
        }

        $db->DeleteRecord("session_tokens", ["user_uuid", "=", $user_uuid]);
    }

    static function GetSessionToken(Database $db, string $token): ?array
    {
        $token_data = $db->SelectRecord("*", "session_tokens", ["token", "=", $token])[0] ?? null;
        $has_expired = false;

        if (!isset($token_data)) { return null; }

        $iat = new \DateTime($token_data["created_at"]);
        $exp = new \DateTime($token_data["expires_at"]);
        $now = new \DateTime();
        if ($now < $iat || $now > $exp) {
            $has_expired = true;
            $db->DeleteRecord("session_tokens", ["token", "=", $token]);
        }

        return [
            "token_data" => $token_data,
            "has_expired" => $has_expired
        ];
    }
}