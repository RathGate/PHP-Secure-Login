<?php

namespace database;
require_once __DIR__."/../autoload.php";

use Exception;
use InvalidArgumentException;
use PDO;
use security\Credentials;
use libs\FormatLib;

// Database manipulation class
class Database
{
    public Connection $connection;
    public static array $comparison_op = [
        "=", "<>", "!=", "<", ">", "<=", ">=", "LIKE", "IN", "BETWEEN", "IS NULL", "IS NOT NULL",
    ];

    // If $credentials is null, will generate new credentials with the class default values.
    function __construct(Credentials $credentials = NULL)
    {
        // Establishes the connexion to the database.
        $this->connection = new Connection($credentials);
    }

    // Checks if a table exist or not in the database.
    function TableExists(?string $table = NULL): bool
    {
        if (!isset($table) or $table == "") {
            throw new InvalidArgumentException("Table name cannot be empty.");
        }

        // SQL query :
        $cmd = 'SELECT table_name
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = :dbname
            AND TABLE_NAME = :table;';
        $qry = $this->connection->dbh->prepare($cmd);

        // Parameter binding and execution :
        $qry->bindValue(":dbname", $this->connection->dbname);
        $qry->bindValue(":table", $table);
        $qry->execute();

        // Returns true if table exists :
        return count($qry->fetchAll()) > 0;
    }

    // Selects all records from a table.
    // Todo : Limit and offset !
    // Todo : Factoring where and param binds ?
    function SelectRecord($columns, ?string $table=NULL, $where=NULL) : array
    {
        if (!$this->TableExists($table)) {
            $db = $this->connection->dbname;
            throw new InvalidArgumentException("Table `$table` doesn't exist in database `$db`.");
        }

        // Column name formatting :
        if ($columns && !is_array($columns) && $columns != "*") {
            throw new Exception("Invalid column parameter");
        }
        if (!$columns || $columns == "*" || in_array("*", $columns)) {
            $columns = "*";
        } else {
            $columns = FormatLib::FormatImplode($columns);
        }

        // WhereClause clause formatting :
        if ($where) {
            $clause = Database::WhereClause($where);
            $opt = " WHERE ".$clause["strReq"];
        } else {
            $opt = "";
        }

        // SQL query
        $cmd = "SELECT $columns FROM $table$opt;";
        $qry = $this->connection->dbh->prepare($cmd);

        // WhereClause parameter binding and execution :
        if (isset($clause["values"]) and count($clause["values"])>0) {
            for ($i = 1; $i <= count($clause["values"]); $i++) {
                $qry->bindValue($i, $clause["values"][$i-1]);
            }
        }
        $qry->execute();
        // TODO : remove
        return $qry->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Adds a single record to a table.
     * Todo : multiple additions at the same time ?
     * @param string $table table name
     * @param array|null $record associative array for values to be inserted.
     * @throws InvalidArgumentException raised if table doesn't exist.
     * @return string|false last inserted ID if exists, false otherwise.
     */
    function AddRecord(string $table, array $record=NULL): false|string
    {
        if (!$this->TableExists($table)) {
            $db = $this->connection->dbname;
            throw new InvalidArgumentException("Table `$table` doesn't exist in database `$db`.");
        }

        // Column name formatting :
        $cols = FormatLib::FormatImplode(array_keys($record));
        // Values formatting :
        $vals = FormatLib::FormatImplode(range(0, count($record)-1), ":%s");

        // SQL Query :
        $cmd = "INSERT INTO `$table` ($cols) VALUES ($vals);";
        $qry = $this->connection->dbh->prepare($cmd);

        // Parameter binding and execution :
        $i = 0;
        foreach ($record as $val) {
            $qry->bindValue(":$i", $val);
            $i++;
        }
        $qry->execute();

        return $this->connection->dbh->lastInsertId();
    }

    /** Deletes records from a table.
     // Todo : Factoring where and param binds ?
     * @param string $table table name
     * @param array|null|string $where where clause conditions
     * @throws DatabaseFormatException
     * @throws InvalidArgumentException
     * @return int number of rows affected
     */
    function DeleteRecord(string $table, $where): int
    {
        // Checks for the existence of the table
        if (!$this->TableExists($table)) {
            $db = $this->connection->dbname;
            throw new InvalidArgumentException("Table `$table` doesn't exist in database `$db`.");
        }
        // Checks for the existence of the where clause :
        if ($where == NULL) {
            throw new DatabaseFormatException("Invalid DELETE query format : `where` clause must be set");
        }

        // WhereClause clause formatting :
        $clause = Database::WhereClause($where);
        // SQL query :
        $cmd = "DELETE FROM $table WHERE ".$clause["strReq"].";";
        $qry = $this->connection->dbh->prepare($cmd);

        // WhereClause parameter binding and execution :
        if (isset($clause["values"]) and count($clause["values"])>0) {
            for ($i = 1; $i <= count($clause["values"]); $i++) {
                $qry->bindValue($i, $clause["values"][$i-1]);
            }
        }
        $qry->execute();

        return $qry->rowCount();
    }

    // Updates records from a table
    // Todo : Factoring where and param binds ?
    function UpdateRecord(string $table, ?array $record, $where): int
    {
        if (!$this->TableExists($table)) {
            $db = $this->connection->dbname;
            throw new InvalidArgumentException("Table `$table` doesn't exist in database `$db`.");
        }

        // Columns and values formatting :
        $vals = FormatLib::ArrayToInsertFormat($record);

        // WhereClause clause formatting :
        $clause = Database::WhereClause($where);

        // SQL query :
        $cmd = "UPDATE `$table` SET $vals WHERE ".$clause["strReq"].";";
        $qry = $this->connection->dbh->prepare($cmd);

        // Value parameter binding :
        $i = 1;
        foreach ($record as $val) {
            $qry->bindValue($i, $val);
            $i++;
        }
        // WhereClause parameter binding and execution :
        if (isset($clause["values"]) and count($clause["values"])>0) {
            foreach ($clause["values"] as $val) {
                echo $val;
                $qry->bindValue($i, $val);
                $i++;
            }
        }
        $qry->execute();
        return $qry->rowCount();
    }

     // String builder for where clause with comparison operators.
     // Todo : Is an absolute unit of a function.
     // Todo : Maybe possible to convert to a query builder ?
     // Todo : Implement "NOT" operator.
    static function WhereComparison($val1, $operator, $val2, string $val3=NULL): array
    {
        // Structure which will carry the string result and its associated values
            // strReq : "age" BETWEEN ? AND ?
            // values : [12, 15]
        $result = [
            "strReq" => "",
            "values" => []
        ];

        $operator = strtoupper($operator);

        // Value check of the operator :
        if (!in_array($operator, Database::$comparison_op)) {
            throw new DatabaseFormatException("Invalid operator `$operator`: supported comparison operators are "
                .FormatLib::SurroundImplode(Database::$comparison_op, "[", "]", ", "));
        }
        // Type check of the $val1 :
        if (!is_string($val1)) {
            //TODO
            throw new DatabaseFormatException("Invalid parameter format: comparison parameter 1 must be a string");
        }

        // Switch on the operator :
        switch ($operator) {
            case "BETWEEN":
                // Parameters value check :
                if (!isset($val2) || !isset($val3) || !FormatLib::isValidTypeOnly($val2) || !FormatLib::isValidTypeOnly($val3)) {
                    throw new DatabaseFormatException("Invalid parameter format in `$operator` comparison: parameter 3 and 4 must 
                    both be set and of the following types: `string` or `integer/double` (here `".gettype($val2)."` and `".gettype($val3)."`).");
                }

                // String construction and values
                $result["strReq"] = "$val1 BETWEEN ? AND ?";
                $result["values"] = [$val2, $val3];
                break;
            case "IN":
                // Parameters value check :
                if (!$val2 || !is_array($val2) || !FormatLib::isValidTypeOnly($val2, true)) {
                    $comp = $val2 ? "" : "with no value";
                    throw new DatabaseFormatException("Invalid parameter format in `$operator` comparison: parameter 3 must
                    have a value and be of type `array` (here `".gettype($val2)."` $comp).");
                }

                // String construction and values
                $result["strReq"] = "$val1 IN (".join(", ", array_fill(0, count($val2), "?")).")";
                $result["values"] = $val2;
                break;
            case "IS NULL":
            case "IS NOT NULL" :
                // String construction and values
                $result["strReq"] = "$val1 $operator";
                $result["values"] = [];
                break;
            default:
                // Parameters value check :
                if (!FormatLib::isValidTypeOnly($val2)) {
                    throw new DatabaseFormatException("Invalid parameter format in `$operator` comparison: parameter 3 must
                    have a value of the following types: `string` or `integer/double` (here `".gettype($val2)."`).");
                }
                // String construction and values
                $result["strReq"] = "$val1 $operator ?";
                $result["values"] = [$val2];
        }
        return $result;
    }


    // Global String builder for where clause
    // Todo : Is also an absolute unit of a function.
    // Todo : Maybe possible to convert to a query builder ?
    // Todo : Implement "NOT" operator.
    // Todo : Separate the conditionArr like in WhereComparison?
    static function WhereClause($conditionArr, bool $is_nested=false): array
    {
        // Structure which will carry the string result and its associated values
        // strReq : "age" BETWEEN ? AND ?
        // values : [12, 15]
        $result = [
            "strReq" => "",
            "values" => []
        ];

        // Type check of the conditionArr :
        if (!is_array($conditionArr)) {
            throw new DatabaseFormatException("Invalid format for WHERE clause : must be of type `array` (here `".gettype($conditionArr)."`).");
        }
        // Value check of the conditionArr (minimum 2 elements, otherwise there can't be aggregation/comparison).
        if (count($conditionArr) < 2) {
            throw new DatabaseFormatException("Invalid format for WHERE clause : array must contain at least 2 elements (column and operator, more if 
             aggregation or comparison) (here `".count($conditionArr)."`).");
        }

        // Please don't ban me from PHP, I had no better idea.
        $val1 = $conditionArr[0] ?? NULL;
        $operator = $conditionArr[1] ?? NULL;
        if ($operator) {
            $operator = strtoupper($operator);
        }
        $val2 = $conditionArr[2] ?? NULL;
        $val3 = $conditionArr[3] ?? NULL;

        // If operator is an aggregation operator :
        if ($operator == "AND" || $operator == "OR") {
            if (!$val2) {
                throw new DatabaseFormatException("Invalid parameter format in `$operator` aggregation: parameter 3 must be set and not empty.");
            }
            // Recursive on left and right terms :
            $leftTerm = Database::WhereClause($val1, true);
            $rightTerm = Database::WhereClause($val2, true);

            // Builds the result string :
            $temp = $leftTerm["strReq"]." $operator ".$rightTerm["strReq"];
            $result["strReq"] = $is_nested ? "($temp)" : $temp;
            // Builds the result value array :
            $result["values"] = array_merge($result["values"], $leftTerm["values"]);
            $result["values"] = array_merge($result["values"], $rightTerm["values"]);

            return $result;
        }
        // Else, probably a comparison operator :
        return Database::WhereComparison($val1, $operator, $val2, $val3);
    }

}

