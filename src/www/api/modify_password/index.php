<?php

require_once __DIR__."/../../autoload.php";
use api\modify_password\ModifyPasswordService;

new ModifyPasswordService(["GET", "PUT"]);