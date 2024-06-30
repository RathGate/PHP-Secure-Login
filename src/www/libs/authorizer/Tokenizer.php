<?php

namespace libs\authorizer;

use database\Database;
use http\Exception\InvalidArgumentException;
use libs\Format;
use libs\JWT;
use mysql_xdevapi\Exception;

class Tokenizer
{
    // Time interval with DateInterval format `PxYxMxDTxHxMxS`
    // https://www.php.net/manual/en/dateinterval.construct.php
    static string $AccessTokenDuration = "PT15M";
    static string $RefreshTokenDuration = "P30D";
    static string $SessionTokenDuration = "P1D";

    // NOTE : Session system is currently by session token
    static function GenerateJWTToken($payload, string|null $duration): string
    {
        if (!isset($payload) || $payload == null) {
            throw new InvalidArgumentException("Payload cannot be empty");
        }
        $time = ["iat" => (new \DateTime())->getTimestamp()];

        // Calculates OTP expiration date based on duration
        if (isset($duration)) {
            $interval = new \DateInterval($duration);
            $time["exp"] = ((new \DateTime())->add($interval))->getTimestamp();

            if ($time["iat"] > $time["exp"]) {
                throw new \InvalidArgumentException("Token duration cannot be null or negative");
            }
        }
        return (new JWT())->Encode(array_merge($payload, $time));
    }

    // NOTE : Session system is currently by session token
    static function GenerateAccessToken($user_uuid): string|array
    {
        $token = self::GenerateJWTToken(["user_uuid"=>$user_uuid], self::$AccessTokenDuration);
        return [
            "access_token"=> $token,
            "token_type"=> "Bearer",
            "expires_in"=>date_create('@0')->add(new \DateInterval(self::$AccessTokenDuration))->getTimestamp(),
        ];
    }

    // NOTE : Session system is currently by session token
    // Todo : Write token in database
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

    static function DecifferJWTToken(string $token) {
        $payload = (new JWT())->Decode($token);
        $has_expired = false;

        try {
            $iat = isset($payload['iat']) ? new \DateTime("@".$payload['iat']) : null;
            $exp = isset($payload['exp']) ? new \DateTime("@".$payload['exp']) : null;

            // Malformated token
            if (isset($iat) && isset($exp) && $exp <= $iat) { throw new \Exception(); }

            // Checks if token has expired
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
        // Retrieves token from headers
        $authorization = getallheaders()["Autorization"] ?? null;
        if (!isset($authorization)) {
            return null;
        }

        // Checks token format
        $matches = array();
        if (preg_match('/Bearer (.+)/', $authorization, $matches) && isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    static function GenerateSessionToken(Database $db, string $user_uuid, int $byte_length=32) {
        // Generates token and token attributes
        $token = bin2hex(random_bytes($byte_length));
        $iat = Format::DateToStr(new \DateTime());
        $exp = Format::DateToStr(((new \DateTime())->add(new \DateInterval(self::$SessionTokenDuration))));
        $val = date_create('@0')->add(new \DateInterval(self::$SessionTokenDuration))->getTimestamp();

        // Adds token to database and deletes all previous failed attempts
        $token_id = $db->AddRecord("user_sessions", array("user_uuid"=>$user_uuid, "token"=>$token, "created_at"=>$iat,
            "expires_at"=>$exp, "validity"=>$val));
        $db->DeleteRecord("user_connection_attempts", ["user_uuid", "=", $user_uuid]);

        // Deletes all other session tokens
        // Todo : Find a way to link token to IP to permit multiple sessions
        // Todo: without risking illimited session tokens? Doesn't work in local though
        return [
            "session_token"=> $token,
            "token_type"=> "Bearer",
            "expires_in"=> $val
        ];
    }

    static function RevokeSessionToken(Database $db, string $token) {
        $db->DeleteRecord("user_sessions", ["token", "=", $token]);
    }

    static function RevokeAllUserSessionTokens(Database $db, string $user_uuid=null, string $token=null) {
        if (!isset($user_uuid) && !isset($token)) { return; }

        // If user_uuid is not yet retrieved, can use current token to do so
        // Todo : superfluous request to database !
        if (!isset($user_uuid)) {
            $token_data = self::GetUserSession($db, $token);
            if (!isset($token_data)) { return; }
            $user_uuid = $token_data["token_data"]["user_uuid"];
        }

        // Deletes all session tokens link to user_uuid
        $db->DeleteRecord("user_sessions", ["user_uuid", "=", $user_uuid]);
    }

    // Retrieves user session from database using token
    static function GetUserSession(Database $db, string $token): ?array
    {
        $token_data = $db->SelectRecord("*", "user_sessions", ["token", "=", $token])[0] ?? null;
        $has_expired = false;

        if (!isset($token_data)) { return null; }

        // Checks token validity
        $iat = new \DateTime($token_data["created_at"]);
        $exp = new \DateTime($token_data["expires_at"]);
        $now = new \DateTime();
        if ($now < $iat || $now > $exp) {
            $has_expired = true;
            $db->DeleteRecord("user_sessions", ["token", "=", $token]);
        }

        return [
            "token_data" => $token_data,
            "has_expired" => $has_expired
        ];
    }
}