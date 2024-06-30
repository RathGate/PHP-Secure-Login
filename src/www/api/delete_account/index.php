<?php

use api\delete_account\DeleteAccountService;

require_once __DIR__."/../../autoload.php";

new DeleteAccountService(["GET", "DELETE"]);