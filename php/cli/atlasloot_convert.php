<?php


declare(strict_types=1);

use App\Config;
use App\SQL;

require '../vendor/autoload.php';
require '../error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());
