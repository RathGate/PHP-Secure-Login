<?php

namespace libs\authenticator;

use database\Database;

class SecuredActioner
{
    static function GenerateOTP(int $byteLength=4):string {
        return bin2hex(random_bytes($byteLength));
    }
}