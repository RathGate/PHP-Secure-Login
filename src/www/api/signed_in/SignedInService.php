<?php

namespace api\signed_in;
use api\database\DatabaseService;
use libs\authorizer\JWT;

class SignedInService extends DatabaseService
{
    protected function CheckParameters()
    {
        // TODO: Implement CheckParameters() method.
    }

    public function GET(): void
    {
        $jwt = new JWT();
        $token = $jwt->Encode(array("name"=>"marianne", "kill_me"=>true));
        var_dump($jwt->Decode($token));
    }
}