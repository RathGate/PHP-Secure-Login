<?php

namespace security;

use stdClass;

/**
 *
 */
class Credentials
{
    /** Name of the service (and therefore name of the file without .json extension)
     * used to create the credentials.
     * @var
     */
    private static $default_service = "database";
    public $service_name;

    /** Class constructor
     * @param string|null $service_name
     */
    function __construct(?string $service_name="")
    {
        $this::SetParameters($service_name);
    }

    /**
     * @param string|null $service_name
     * @return void
     */
    public function SetParameters(?string $service_name):void {
        // Sets the service name (uses $default_service if none)
        if (!isset($service_name) || $service_name == "") {
            $this->service_name = Credentials::$default_service;
        } else {
            $this->service_name = $service_name;
        }

        // Retrieves and decodes data from file `$service_name`.json in secure folder.
        $f = Files::GetSecureFile("credentials/".$this->service_name.".json");
        $json_data = json_decode($f);

        // Dynamically creates attributes to $service_values with retrieved data.
        foreach ($json_data as $key => $value) {
            $this->$key = $value;
        }
    }
}