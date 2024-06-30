<?php

namespace libs;

class Cryptographics
{
    static function GenerateSalt(int $length=64) :string {
        // Generate the salt using a secure pseudo-random number generator.
        $salt = random_bytes(64);
        // Convert the salt to a base64-encoded string for storage.
        // Return the encoded salt.
        return base64_encode($salt);
    }

    static function GenerateSecurePassword(string $password, string $salt=null, int $stretch=1000, int $saltLength=64): array {
        $result = [];
        $result["stretch"] = max($stretch, 0);
        $result["salt"] = $salt ?? self::GenerateSalt($saltLength);

        $result["password"] = $password . $result["salt"];
        for ($i = 0; $i < $result["stretch"]; $i++) {
            $result["password"] = hash('sha512', $result["password"]);
        }
        return $result;
    }

    static function MatchSecurePassword(string $password, string $securePwd, string $salt="", int $stretch=1000):bool {
        return self::GenerateSecurePassword($password, $salt, $stretch)["password"] == $securePwd;
    }
}