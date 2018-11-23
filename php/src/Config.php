<?
declare(strict_types=1);

namespace App;

class Config
{

    private $configData;

    public function __construct()
    {
        $this->configData = [
            "jwt" => [
                "secret" => "megasecret",
            ],
            "pdo" => [
                "dataSourceName" => "mysql:host=sql;dbname=wcf",
                "username" => "root",
                "password" => "root"
            ]
        ];
    }

    public function getPDODataSourceName(): string
    {
        return $this->configData["pdo"]["dataSourceName"];
    }

    public function getPDOUsername(): string
    {
        return $this->configData["pdo"]["username"];
    }

    public function getPDOPassword(): string
    {
        return $this->configData["pdo"]["password"];
    }

    public function getJWTDuration(): int
    {
        return $this->configData["jwt"]["duration"];
    }

    public function getJWTSecret(): string
    {
        return $this->configData["jwt"]["secret"];
    }

}