<?php

namespace database;

use security\Credentials;

class Connection
{
    public static $required_creds = ["username", "password", "host", "port"];
    public $dbname;
    public $dbh;

    function __construct(Credentials $credentials=NULL) {
        if (!isset($credentials)) {
            $credentials = new Credentials();
        }
        if (!self::IsValidCredentials($credentials)) {
            throw new \InvalidArgumentException("Les paramètres ".json_encode(self::$required_creds)
                ." sont obligatoires pour une connexion à la base de données.");
        }
        $this->dbname = $credentials->dbname ?? "";
        $this->dbh = Connection::PDO($credentials, $this->dbname);
        // Permet d'afficher les erreurs de PDO:
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    static function PDO(Credentials $credentials, ?string $dbname=NULL): \PDO
    {
        if (!isset($dbname) || $dbname == "") {
            $dsn = "mysql:".
                "host=".$credentials->host.";".
                "port=".$credentials->port.";";
        } else {
            $dsn = "mysql:".
                "host=".$credentials->host.";".
                "dbname=".$credentials->dbname.";".
                "port=".$credentials->port.";";
        }

        return new \PDO($dsn, $credentials->username, $credentials->password);
    }

    static function IsValidCredentials(Credentials $credentials): bool
    {
        foreach (self::$required_creds as $required_cred) {
            if (!property_exists($credentials, $required_cred)) {
                return false;
            }
        }
        return true;
    }
}