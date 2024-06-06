<?php

namespace libs;

/**
 * Contains all static methods relative to the API
 */
class ApiLib
{

    // Write and sends JSON formated error response.
    /**
     * @param int $code HTTP response code
     * @param string $message Error message
     * @param bool $exitWhenDone If true, exits the program after sending the response.
     * @return void
     */
    static function WriteErrorResponse(int $code, string $message, bool $exitWhenDone=true) {
        // Sets the HTTP response code.
        http_response_code($code);

        // Write the response and sends it.
        $response = array("error"=>array("code"=>$code, "message"=>$message));
        echo stripslashes(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // Exits the execution if true.
        if ($exitWhenDone) {
            exit;
        }
    }

    // Ecrit et envoie le JSON d'une requête valide.
    // Termine l'exécution du script si $exitWhenDone est true.
    /**
     * @param array|string $data
     * @param bool $exitWhenDone
     * @return void
     */
    static function WriteResponse($data, bool $exitWhenDone=true) {
        // Sets the HTTP response code.
        header('Content-Type: application/json');

        // Write the response and sends it.
        $response = array("data"=>$data);
        echo stripslashes(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // Exits the execution if true.
        if ($exitWhenDone) {
            exit;
        }
    }
}