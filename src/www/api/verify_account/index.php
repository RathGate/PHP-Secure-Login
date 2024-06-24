<?php

require_once __DIR__."/../../autoload.php";
use api\verify_account\VerifyAccountService;

new VerifyAccountService(["GET", "POST"]);