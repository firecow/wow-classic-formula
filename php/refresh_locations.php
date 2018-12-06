<?php


declare(strict_types=1);

use App\Config;
use App\Encoding\JSON;
use App\SQL;

require 'vendor/autoload.php';
require 'error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

$json = JSON::decode(file_get_contents("../dumps/instances.json"));

foreach ($json as $list) {

}