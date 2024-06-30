<?php

namespace libs;

// Contains all static methods relative to the API
class Api
{
    static function WriteResponse(bool $is_success, int $code, $data=null, string $message=null, bool $exitWhenDone=true): void {
        // Sets the HTTP response code.
        http_response_code($code);

        // Write the response and sends it.
        $response = array(
            "status"=> $is_success ? "success" : "error",
            "code"=> $code,
        );
        if (isset($data)) { $response = array_merge($response, ["data" => $data]); }
        if (isset($message)) { $response = array_merge($response, ["message" => $message]); }

        echo stripslashes(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // Exits the execution if true.
        if ($exitWhenDone) {
            exit;
        }
    }

    // Write and sends JSON formated error response.
    static function WriteErrorResponse(int $code, string $message,bool $exitWhenDone=true): void
    {
        self::WriteResponse(false, $code, null, $message, $exitWhenDone);
    }

    // Ecrit et envoie le JSON d'une requête valide.
    // Termine l'exécution du script si $exitWhenDone est true.
    static function WriteSuccessResponse($data, $code=200, bool $exitWhenDone=true): void
    {
        self::WriteResponse(true, $code, $data, null, $exitWhenDone);
    }

}