<?php

declare(strict_types=1);

namespace App;

use App\Encoding\JSON;
use App\Exceptions\DAOException;
use PDO;
use PDOStatement;
use Traversable;

class SQL
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(string $dataSourceName, string $username, string $password)
    {
        $this->pdo = new PDO($dataSourceName, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function quote(string $str): string
    {
        return $this->pdo->quote($str);
    }

    private function prepare(string $statement, array $inputParams): PDOStatement
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($statement);
        $result = $stmt->execute($inputParams);
        if ($result === false) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("PDO statement execute failed `$statement` `$encodedInputParams");
        }
        return $stmt;
    }

    public function raw(string $statement): PDOStatement
    {
        $pdo = $this->pdo;
        $statement = $pdo->query($statement);
        if ($statement === false) {
            throw new DAOException("PDO statement execute failed `$statement`");
        }
        return $statement;
    }

    public function execute(string $statement, array $inputParams): Traversable
    {
        return $this->prepare($statement, $inputParams);
    }

    public function fetchAssoc(string $statement, array $inputParams): array
    {
        $stmt = $this->prepare($statement, $inputParams);
        $dbObject = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dbObject === false) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("Could not find object for `$statement` `$encodedInputParams`");
        }
        return $dbObject;
    }

    public function fetchAll(string $statement, array $inputParams): array
    {
        $stmt = $this->prepare($statement, $inputParams);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result === false) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("Fetch all returned false `$statement` `$encodedInputParams`");
        }
        return $result;
    }

    public function fetchColumnString(string $statement, array $inputParams, int $column = 0): string
    {
        $stmt = $this->prepare($statement, $inputParams);
        $columnValue = $stmt->fetchColumn($column);
        if ($columnValue === false) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("Could not find column `$column` for `$statement` `$encodedInputParams`");
        }
        return $columnValue;
    }

    public function fetchColumnInt(string $statement, array $inputParams, int $column = 0): int
    {
        $stmt = $this->prepare($statement, $inputParams);
        $columnValue = $stmt->fetchColumn($column);
        if ($columnValue === false) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("Could not find column `$column` for `$statement` `$encodedInputParams``");
        }
        if (!is_int($columnValue)) {
            $encodedInputParams = JSON::encode($inputParams);
            throw new DAOException("Column is not an integer `$column` for `$statement` `$encodedInputParams`");
        }
        return $columnValue;
    }
}
