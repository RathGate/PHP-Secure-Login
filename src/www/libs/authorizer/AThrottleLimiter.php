<?php

namespace libs\authorizer;

use database\Database;

abstract class AThrottleLimiter
{
    public static int $default_max = 5;
    public static int $default_interval = 3600;
    public int $max;
    public int $interval;
    public string $user_uuid;
    public Database $db;

    public function __construct(Database|null $db, string $user_uuid, int $max=null, int $interval=null)
    {
        $this->db = $db ?? new Database();
        $this->max = $max ?? self::$default_max;
        $this->interval = $interval ?? self::$default_interval;
        $this->user_uuid = $user_uuid;
    }

    public abstract function HasReachedLimit(): bool;
}