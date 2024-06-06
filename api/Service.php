<?php

namespace api;
require_once __DIR__."/../autoload.php";
use libs\ApiLib;

/**
 *
 */
abstract class Service {
    protected $allowedVerbs = [];
    protected $requiredParams = [];
    protected $optionParams = [];
    protected $paramValues;
    protected $method;

    /** Class constructor
     * @param ?array $allowed_verbs all allowed HTTP verbs
     */
    public function __construct(?array $allowed_verbs=["GET"])
    {
        // Puts allowed verbs and HTTP methods to uppercase to avoid errors :
        $this->allowedVerbs = array_change_key_case($allowed_verbs ?? [], CASE_UPPER);
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);

        // Checks if the method of the current request is valid :
        if (!self::IsValidMethod()) {
            ApiLib::WriteErrorResponse(405, "Method ".$this->method." is not allowed.");
        }

        // Retrieves, sets and checks parameters :
        $this::SetParameters();
        // Todo: séparé de SetParameters mais peut certainement être factorisé en une fonction,
        // Todo: mais je ne pense pas savoir comment faire.
        $this->CheckParameters();

        // Launches the execution of the service itself.
        $this->Trig();
    }

    /** Main body of execution of the service. Can be overwritten to fit needs.
     * By default, will call the class method associated to the HTTP method used.
     * @return void
     */
    public function Trig() {
        $fct = $this->method;
        $this->$fct();
    }

    /** Checks the validity of the current HTTP method.
     * @return bool true if the current HTTP method is among the valid_methods.
     */
    public function IsValidMethod(): bool
    {
        return in_array($this->method, $this->allowedVerbs);
    }

    // Enregistre les paramètres dans l'object $this->params.

    /** Sets the requested and optionnal parameters of the request in paramValues.
     * @return void
     */
    public function SetParameters(): void {
        // Creates the object for the parameter values :
        $this->paramValues = new \stdClass();
        // If no required/optional parameters, assigns an empty array to the
        // value so that the program doesn't explode at the foreach.
        $this->requiredParams[$this->method] = $this->requiredParams[$this->method] ?? [];
        $this->optionParams[$this->method] = $this->optionParams[$this->method] ?? [];

        // Retrieves the parameter values depending on the HTTP method used :
        $rawParamValues = [];
        switch ($this->method) {
            case "PATCH":
            case "PUT":
                parse_str(file_get_contents('php://input'), $rawParamValues);
                break;
            case "POST":
                $rawParamValues = $_POST;
                break;
            default:
                $rawParamValues = $_GET;
        }

        // Todo : refactor this. ASAP.
        // Required parameters :
        foreach ($this->requiredParams[$this->method] as $param) {
            if (!isset($rawParamValues[$param])) {
                ApiLib::WriteErrorResponse(400, "Paramètre obligatoire `".$param."` manquant.");
            }
            try {
                $this->paramValues->$param = json_decode($rawParamValues[$param], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                ApiLib::WriteErrorResponse(400, "Syntax Error: could not parse parameter `".$param."` [expecting JSON format].");
                return;
            }
        }
        // Optional parameters :
        foreach ($this->optionParams[$this->method] as $param) {
            if (isset($rawParamValues[$param])) {
                try {
                    $this->paramValues->$param = json_decode($rawParamValues[$param], true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    ApiLib::WriteErrorResponse(400, "Syntax Error: could not parse parameter `".$param."` [expecting JSON format].");
                    return;
                }
            } else {
                $this->paramValues->$param = "";
            }
        }
    }

    /** Function to be implemented in child classes if there's need for further parameter
     * verification after they have been set.
     * @return mixed
     */
    public abstract function CheckParameters();

    /** Main body of the service for GET HTTP method.
     * @return mixed
     */
    public abstract function GET();

    /** Main body of the service for POST HTTP method.
     * @return mixed
     */
    public abstract function POST();

    /** Main body of the service for PUT HTTP method.
     * @return mixed
     */
    public abstract function PUT();

    /** Main body of the service for DELETE HTTP method.
     * @return mixed
     */
    public abstract function DELETE();

    /** Main body of the service for PATCH HTTP method.
     * @return mixed
     */
    public abstract function PATCH();
}

