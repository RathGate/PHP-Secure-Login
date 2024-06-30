<?php

namespace libs;

use libs\security\Credentials;

class JWT
{
    public static array $required_creds = ["secret_key"];
    public string $secret_key;
    public string $alg = "HS256";
    public string $hash = "sha256";

    function __construct(Credentials $credentials=NULL) {
        if (!isset($credentials)) {
            $credentials = new Credentials("jwt");
        }

        if (!Credentials::IsValidCredentials($credentials, self::$required_creds)) {
            throw new \InvalidArgumentException("Le(s) paramètre(s) ".json_encode(self::$required_creds)
                ." sont obligatoires.");
        }
        $this->secret_key = $credentials->secret_key ?? "";
    }

    public function Encode(array $payload): string {
        $header = [
            "alg" => $this->alg,
            "type"=>"JWT"
        ];

        $header = self::base64_url_encode(json_encode($header));
        $payload = self::base64_url_encode(json_encode($payload));
        $signature = hash_hmac($this->hash, "$header.$payload", $this->secret_key, true);
        $signature = self::base64_url_encode($signature);

        return "$header.$payload.$signature";
    }

    public function Decode(string $token): array {

        $splt_token = explode(".", $token);
        if (sizeof($splt_token) != 3) {
            throw new \Exception("Invalid token format");
        }
        $signature = hash_hmac($this->hash, "$splt_token[0].$splt_token[1]", $this->secret_key, true);
        if (!hash_equals($signature, self::base64_url_decode($splt_token[2]))) {
            throw new \Exception("Invalid token signature");
        }

        return json_decode(self::base64_url_decode($splt_token[1]), true);
    }

    public static function base64_url_encode(string $text): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    public static function base64_url_decode(string $text): string
    {
        return base64_decode(str_replace(["-", "_"],["+", "/"],$text));
    }
}