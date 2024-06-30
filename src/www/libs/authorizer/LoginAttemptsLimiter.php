<?php

namespace libs\authorizer;

use database\Database;
use libs\Format;

class LoginAttemptsLimiter extends AThrottleLimiter
{
    public function HasReachedLimit(): bool
    {
        $threshold = (new \DateTime())->modify("-".$this->interval." second");
        $attempts =$this->db->ExecuteQuery("SELECT COUNT(*) as attempts FROM `user_connection_attempts` WHERE `user_uuid` = ? AND ? < `attempted_at`",
        [$this->user_uuid, Format::DateToStr($threshold)])[0]["attempts"] ?? 0;

        return $attempts >= $this->max;
    }
}