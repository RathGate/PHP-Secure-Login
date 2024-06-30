<?php

namespace api;
require_once __DIR__."/../autoload.php";
use libs\Api;
use libs\authorizer\ThrottleLimiter;

abstract class Service {
    protected string $serviceName;
    protected array $allowedVerbs = [];
    protected array $requiredParams = [];
    protected array $optionParams = [];
    protected \stdClass $paramValues;
    protected string $method;

    public function __construct(?array $allowed_verbs=["GET"])
    {
        // Registers service name
        $this->serviceName = str_replace("Service", "", (substr(strrchr(get_class($this), '\\'), 1)));

        // Puts allowed verbs and HTTP methods to uppercase to avoid errors :
        $this->allowedVerbs = array_change_key_case($allowed_verbs ?? [], CASE_UPPER);
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);

        // Checks if the method of the current request is valid :
        if (!self::IsValidMethod()) {
            Api::WriteErrorResponse(405, "Method ".$this->method." is not allowed.");
        }

        // Retrieves, sets and checks parameters :
        $this->SetParameters();
        $this->CheckParameters();

        // Launches the execution of the service itself.
        $this->Trig();
    }

    // Main body of execution of the service. Can be overwritten to fit needs.
    // By default, will call the class method associated to the HTTP method used.
    public function Trig(): void
    {
        $fct = $this->method;
        $this->$fct();
    }

    // Checks the validity of the current HTTP method.
    public function IsValidMethod(): bool
    {
        return in_array($this->method, $this->allowedVerbs);
    }

    // Sets the requested and optionnal parameters of the request in paramValues.
    protected function SetParameters(): void {
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


        foreach ([$this->requiredParams, $this->optionParams] as $group) {
            foreach ($group[$this->method] as $param) {
                // Checks if required parameter exists
                if ($group == $this->requiredParams && !isset($rawParamValues[$param])) {
                    Api::WriteErrorResponse(400, "Paramètre obligatoire `" . $param . "` manquant.");
                    return;
                }

                // Registers parameter
                if (isset($rawParamValues[$param])) { $this->paramValues->$param = $rawParamValues[$param]; }
            }
        }
    }

    // Additional parameter check
    protected function CheckParameters() {}

    // HTTP Methods
    // By default, all of them will return a 405, each service will have to
    // declare its own functions
    public function GET(): void
    {
        Api::WriteErrorResponse(405, "Method GET is not allowed.");
    }
    public function POST(): void
    {
        Api::WriteErrorResponse(405, "Method POST is not allowed.");
    }
    public function PUT(): void
    {
        Api::WriteErrorResponse(405, "Method PUT is not allowed.");
    }
    public function DELETE(): void
    {
        Api::WriteErrorResponse(405, "Method DELETE is not allowed.");
    }
    public function PATCH(): void
    {
        Api::WriteErrorResponse(405, "Method PATCH is not allowed.");
    }
}

