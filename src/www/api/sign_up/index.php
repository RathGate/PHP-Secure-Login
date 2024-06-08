<?php

require_once __DIR__."/../../autoload.php";
use api\sign_up\SignUpService;

new SignUpService(["GET", "POST", "DELETE"]);