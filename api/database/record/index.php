<?php
require_once __DIR__ . "/../../../autoload.php";

use api\database\record\DBRecordService;

new DBRecordService(["GET", "POST", "DELETE", "PUT"]);