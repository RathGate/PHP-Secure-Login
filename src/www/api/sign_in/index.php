<?php

require_once __DIR__."/../../autoload.php";
use api\sign_in\SignInService;

new SignInService(["GET", "POST"]);