<?php
namespace api\sort;
require_once __DIR__."/../../autoload.php";

use api\Service;
use libs\ApiLib;
use libs\SortLib;

class SortService extends Service {

    public function __construct($allowed_verbs=["GET"])
    {
        $this->requiredParams = [
            "GET"=>["arr"]
        ];
        parent::__construct($allowed_verbs);
    }

    protected function SetParameters(): void
    {
        parent::SetParameters();
        // Retrieves the endpoint name (.../{endpoint}/index.php)
        preg_match("/^.*\/(?P<folder_name>.+)\/.+\.php$/", $_SERVER["PHP_SELF"], $matches);
        // Looks for the existence of a function {endpoint} in SortLib, else 'false'
        $this->paramValues->sortFunc = $matches["folder_name"] ?? false;
    }

    /** Additional parameter check for custom parameters set in SortService::SetParameters
     * @return void
     */
    protected function CheckParameters(): void
    {
        // La méthode associée à l'endpoint n'existe pas
        if (!method_exists(SortLib::class, $this->paramValues->sortFunc)) {
            ApiLib::WriteErrorResponse(500, "Aucune fonction de tri associée à `".$this->paramValues->sortFunc."`.");
        }
        // Le type du paramètre est invalide
        if (!is_array($this->paramValues->arr)) {
            ApiLib::WriteErrorResponse(400, "Le paramètre `arr` doit être de type array.");
        }
    }

    public function GET(): void
    {
        // Trie l'array avec la fonction associée à l'endpoint
        $sortedArr = SortLib::{$this->paramValues->sortFunc}($this->paramValues->arr);
        // Ecrit le json de la réponse et l'envoie
        ApiLib::WriteResponse(array("sort_function"=>$this->paramValues->sortFunc, "sorted_arr"=>$sortedArr));
    }

    public function POST(): void
    {}
    public function PUT(): void
    {}
    public function DELETE(): void
    {}
    public function PATCH(): void
    {}

}